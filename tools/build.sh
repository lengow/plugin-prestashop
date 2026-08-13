#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'
umask 022

readonly PACKAGE_DIRECTORY='lengow'
readonly ARCHIVE_PREFIX='lengow.prestashop'
readonly SCRIPT_DIRECTORY="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
readonly REPOSITORY_ROOT="$(cd -- "${SCRIPT_DIRECTORY}/.." && pwd -P)"

WORKSPACE=''
MODULE_KEY=''

usage()
{
    cat <<'EOF'
Usage:
  tools/build.sh <version> [--output <directory>]

Build a production PrestaShop module archive.
The default output directory is <repository>/dist. Relative output paths are
resolved from the repository root.
EOF
}

die()
{
    printf 'Error: %s\n' "$*" >&2
    exit 1
}

cleanup()
{
    local status=$?

    if [[ -n "${WORKSPACE}" && -d "${WORKSPACE}" ]]; then
        rm -rf -- "${WORKSPACE}"
    fi

    return "${status}"
}

require_command()
{
    command -v "$1" >/dev/null 2>&1 || die "Missing required command: $1"
}

validate_version()
{
    local version="$1"

    [[ "${version}" =~ ^[0-9]+([.][0-9]+){0,2}([-+][0-9A-Za-z][0-9A-Za-z.-]*)?$ ]] \
        || die "Invalid version: ${version}"
}

resolve_output_directory()
{
    local output_directory="$1"

    if [[ "${output_directory}" != /* ]]; then
        output_directory="${REPOSITORY_ROOT}/${output_directory}"
    fi

    mkdir -p -- "${output_directory}"
    (
        cd -- "${output_directory}"
        pwd -P
    )
}

load_module_key()
{
    local variables_file="${WORKSPACE}/vars.sh"

    [[ -n "${ENCRYPTED:-}" ]] || die 'Required environment variable ENCRYPTED is not set'

    gpg --batch --pinentry-mode loopback --passphrase "${ENCRYPTED}" \
        --output "${variables_file}" --decrypt "${SCRIPT_DIRECTORY}/vars.enc" >/dev/null 2>&1 \
        || die 'Unable to decrypt tools/vars.enc'

    # vars.enc retains legacy shell expressions that may read unset positional parameters.
    set +u
    # shellcheck source=/dev/null
    source "${variables_file}"
    set -u
    rm -f -- "${variables_file}"

    [[ -n "${MODULE_KEY:-}" ]] || die 'MODULE_KEY is missing from tools/vars.enc'
}

copy_runtime_files()
{
    local staging_root="$1"
    local entry
    local -a directories=(
        classes
        config
        controllers
        mails
        src
        translations
        upgrade
        vendor
        views
        webservice
    )
    local -a files=(
        .htaccess
        en.php
        es.php
        fr.php
        index.php
        it.php
        lengow.php
        license.txt
        loader.php
        logo.gif
        logo.png
    )

    mkdir -p -- "${staging_root}/logs" "${staging_root}/export"

    for entry in "${directories[@]}"; do
        [[ -d "${REPOSITORY_ROOT}/${entry}" ]] \
            && rsync -a -- "${REPOSITORY_ROOT}/${entry}" "${staging_root}/"
    done

    for entry in "${files[@]}"; do
        [[ -f "${REPOSITORY_ROOT}/${entry}" ]] \
            && rsync -a -- "${REPOSITORY_ROOT}/${entry}" "${staging_root}/"
    done

    for entry in logs/.htaccess logs/index.php export/index.php; do
        [[ -f "${REPOSITORY_ROOT}/${entry}" ]] \
            && rsync -a -- "${REPOSITORY_ROOT}/${entry}" "${staging_root}/${entry}"
    done
    rm -f -- "${staging_root}/config/marketplaces.json"
}

generate_release_files()
{
    local staging_root="$1"

    mkdir -p -- "${staging_root}/tools"
    cp -- "${SCRIPT_DIRECTORY}/translate.php" "${SCRIPT_DIRECTORY}/checkmd5.php" "${staging_root}/tools/"
    php "${staging_root}/tools/translate.php"
    php "${staging_root}/tools/checkmd5.php"
    rm -rf -- "${staging_root}/tools" "${staging_root}/translations/yml"
}

inject_module_key()
{
    local module_file="$1"

    MODULE_KEY="${MODULE_KEY}" php -r '
        $moduleFile = $argv[1];
        $contents = file_get_contents($moduleFile);
        if ($contents === false) {
            exit(1);
        }

        $updated = str_replace("__LENGOW_PRESTASHOP_PRODUCT_KEY__", getenv("MODULE_KEY"), $contents, $count);
        if ($count !== 1 || file_put_contents($moduleFile, $updated) === false) {
            exit(1);
        }
    ' "${module_file}" || die 'Unable to inject the production module key'
}

require_archive_entry()
{
    local archive_contents="$1"
    local entry="$2"

    grep -Fxq -- "${entry}" "${archive_contents}" || die "Archive is missing ${entry}"
}

validate_archive()
{
    local archive_path="$1"
    local archive_contents="${WORKSPACE}/archive-contents.txt"
    local entry

    unzip -Z1 "${archive_path}" > "${archive_contents}"

    require_archive_entry "${archive_contents}" "${PACKAGE_DIRECTORY}/lengow.php"
    require_archive_entry "${archive_contents}" "${PACKAGE_DIRECTORY}/config/checkmd5.csv"
    require_archive_entry "${archive_contents}" "${PACKAGE_DIRECTORY}/translations/en.csv"

    while IFS= read -r entry; do
        case "${entry}" in
            */.DS_Store|*/._*|*/.Spotlight-V100/*|*/.Trashes/*|*/Thumbs.db|*/Desktop.ini|*/ehthumbs.db|*/__MACOSX/*)
                die "Archive contains operating-system metadata: ${entry}"
                ;;
            */.git/*|*/.github/*|*/.agent/*|*/.agent-kit/*|*/.agents/*|*/.claude/*|*/.codex/*|*/.cursor/*|*/.gemini/*|*/.GEMINI/*|*/.opencode/*|*/AGENTS.md|*/CLAUDE.md|*/COPILOT.md|*/GEMINI.md)
                die "Archive contains local development files: ${entry}"
                ;;
            "${PACKAGE_DIRECTORY}/config/marketplaces.json")
                die 'Archive contains local marketplace configuration'
                ;;
            "${PACKAGE_DIRECTORY}/translations/yml/"*)
                die "Archive contains translation source: ${entry}"
                ;;
            "${PACKAGE_DIRECTORY}/logs/"|"${PACKAGE_DIRECTORY}/logs/.htaccess"|"${PACKAGE_DIRECTORY}/logs/index.php")
                ;;
            "${PACKAGE_DIRECTORY}/logs/"*)
                die "Archive contains runtime log data: ${entry}"
                ;;
            "${PACKAGE_DIRECTORY}/export/"|"${PACKAGE_DIRECTORY}/export/index.php")
                ;;
            "${PACKAGE_DIRECTORY}/export/"*)
                die "Archive contains export data: ${entry}"
                ;;
            "${PACKAGE_DIRECTORY}/")
                ;;
            "${PACKAGE_DIRECTORY}/.htaccess"|"${PACKAGE_DIRECTORY}/en.php"|"${PACKAGE_DIRECTORY}/es.php"|"${PACKAGE_DIRECTORY}/fr.php"|"${PACKAGE_DIRECTORY}/index.php"|"${PACKAGE_DIRECTORY}/it.php"|"${PACKAGE_DIRECTORY}/lengow.php"|"${PACKAGE_DIRECTORY}/license.txt"|"${PACKAGE_DIRECTORY}/loader.php"|"${PACKAGE_DIRECTORY}/logo.gif"|"${PACKAGE_DIRECTORY}/logo.png")
                ;;
            "${PACKAGE_DIRECTORY}/classes/"*|"${PACKAGE_DIRECTORY}/config/"*|"${PACKAGE_DIRECTORY}/controllers/"*|"${PACKAGE_DIRECTORY}/mails/"*|"${PACKAGE_DIRECTORY}/src/"*|"${PACKAGE_DIRECTORY}/translations/"*|"${PACKAGE_DIRECTORY}/upgrade/"*|"${PACKAGE_DIRECTORY}/vendor/"*|"${PACKAGE_DIRECTORY}/views/"*|"${PACKAGE_DIRECTORY}/webservice/"*)
                ;;
            *)
                die "Archive contains an unexpected entry: ${entry}"
                ;;
        esac
    done < "${archive_contents}"
}

main()
{
    local version
    local output_directory="${REPOSITORY_ROOT}/dist"
    local archive_name
    local archive_path
    local temporary_archive
    local staging_root

    if [[ "${1:-}" == '--help' || "${1:-}" == '-h' ]]; then
        usage
        return 0
    fi

    [[ $# -ge 1 ]] || {
        usage >&2
        die 'Version parameter is required'
    }

    version="$1"
    shift
    validate_version "${version}"

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --output)
                [[ $# -ge 2 ]] || die 'Missing directory after --output'
                output_directory="$2"
                shift 2
                ;;
            --output=*)
                output_directory="${1#--output=}"
                [[ -n "${output_directory}" ]] || die 'Missing directory after --output='
                shift
                ;;
            *)
                die "Unknown option: $1"
                ;;
        esac
    done

    require_command gpg
    require_command php
    require_command rsync
    require_command unzip
    require_command zip
    require_command grep
    php -r 'exit(function_exists("yaml_parse_file") ? 0 : 1);' \
        || die 'The PHP YAML extension is required'

    output_directory="$(resolve_output_directory "${output_directory}")"
    archive_name="${ARCHIVE_PREFIX}.${version}.zip"
    archive_path="${output_directory}/${archive_name}"
    WORKSPACE="$(mktemp -d "${TMPDIR:-/tmp}/lengow-prestashop.XXXXXX")"
    trap cleanup EXIT
    staging_root="${WORKSPACE}/${PACKAGE_DIRECTORY}"
    temporary_archive="${WORKSPACE}/${archive_name}"

    load_module_key
    copy_runtime_files "${staging_root}"
    generate_release_files "${staging_root}"
    inject_module_key "${staging_root}/lengow.php"

    (
        cd -- "${WORKSPACE}"
        zip -qr "${temporary_archive}" "${PACKAGE_DIRECTORY}"
    )
    validate_archive "${temporary_archive}"
    mv -f -- "${temporary_archive}" "${archive_path}"

    printf 'Archive created: %s\n' "${archive_path}"
}

main "$@"
