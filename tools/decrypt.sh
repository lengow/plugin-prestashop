#!/bin/sh

set -e

# Required ENV variable: ENCRYPTED
# Usage: ENCRYPTED=my_super_long_ascii_pass_phrase123 ./decrypt_file.sh encrypted.enc secretfile.json

if [ -z "$ENCRYPTED" ]; then
  echo "ERROR: Required ENCRYPTED environment variable NOT passed in."
  exit 1
fi
OUTPUT_FILE=vars.sh
INPUT_FILE=vars.enc
decrypt () {
    gpg --batch --pinentry-mode loopback --passphrase "${ENCRYPTED}" \
        -o "${OUTPUT_FILE}" -d "${INPUT_FILE}" >/dev/null 2>/dev/null
}

if [ -f "${OUTPUT_FILE}" ]
then 
    rm -f "${OUTPUT_FILE}"
fi 

decrypt "${INPUT_FILE}" "${OUTPUT_FILE}"
echo "Decrypted ${INPUT_FILE} to file ${OUTPUT_FILE}"


