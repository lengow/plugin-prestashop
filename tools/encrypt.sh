#!/bin/sh

set -e

# Required ENV variable: ENCRYPTED
# Usage: ENCRYPTED=my_super_long_ascii_pass_phrase123 ./encrypt_file.sh secretfile.json encrypted.enc 

if [ -z "$ENCRYPTED" ]; then
  echo "ERROR: Required ENCRYPTED environment variable NOT passed in."
  exit 1
fi

OUTPUT_FILE=vars.enc
INPUT_FILE=vars.sh
encr () {
  gpg --symmetric --cipher-algo AES256 --passphrase-repeat 0 --batch --pinentry-mode loopback \
    --passphrase "${ENCRYPTED}" -o "${OUTPUT_FILE}" \
    "${INPUT_FILE}" >/dev/null 2>/dev/null
}

if [ -f "${OUTPUT_FILE}" ]
then 
    rm -f "${OUTPUT_FILE}"
fi 

encr "${INPUT_FILE}" "${OUTPUT_FILE}"
echo "Encrypted ${INPUT_FILE} to file ${OUTPUT_FILE}"

echo "Remove ${INPUT_FILE}"
rm -f "${INPUT_FILE}"

