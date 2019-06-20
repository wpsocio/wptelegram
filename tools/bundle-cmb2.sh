#!/bin/bash

# change cwd
cd "$(dirname "$0")"

# set root directory
ROOT_DIR="$(dirname "$(pwd)")"

# args
DEST_PATH=${1-"$ROOT_DIR/build/includes"}
CMB2_DIR=${2-'cmb2'}

# main file of cmb2
FILE="$DEST_PATH/$CMB2_DIR/init.php"

API_URL="https://api.wordpress.org/plugins/info/1.0/cmb2.json"


HTTP_RESPONSE=$(curl --silent --write-out "HTTPSTATUS:%{http_code}" -X POST $API_URL)
HTTP_BODY=$(echo $HTTP_RESPONSE | sed -e 's/HTTPSTATUS\:.*//g')
# extract the status
HTTP_STATUS=$(echo $HTTP_RESPONSE | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')

# if the request fails
if [ ! $HTTP_STATUS -eq 200  ]; then
  echo "Error! Could not connect to api.wordpress.org - [HTTP status: ${HTTP_STATUS}]"
  exit 1
fi

# whether to download or not
DL=false

# get the remote version from API response
REMOTE_VERSION="$(grep -Po '"version":"\d+?\.\d+?\.\d+?"' <<< $HTTP_BODY | grep -Po '\d+?\.\d+?\.\d+?')"

TEMP_PATH="${ROOT_DIR}/temp/cmb2-${REMOTE_VERSION}.zip"

# if file exists
if [ -f $FILE ]; then

    # get the local version from init.php
    LOCAL_VERSION="$(grep -Piom1 '\*\s*?Version\:\s*?(\d+?\.\d+?\.\d+?)' "$FILE" | grep -Po '\d+?\.\d+?\.\d+?')"

    echo "Local version: ${LOCAL_VERSION}"
    echo "Remote version: ${REMOTE_VERSION}"

    if [ "$REMOTE_VERSION" != "$LOCAL_VERSION" ]; then
        DL=true
        printf "The library needs to be updated...\n\n"

        echo "Deleting the existing files..."
        # remove the existing directory, if any
        rm -rf "${DEST_PATH}/${CMB2_DIR}"
    fi
else
    echo "No Local version found"
    DL=true
fi

# if we need to download the latest version
if [ "$DL" = true ]; then

    # if the file is not in the temp folder
    if [ ! -f $TEMP_PATH ]; then

        # remove temporary files
        rm -f "${ROOT_DIR}"/temp/cmb2-*.zip

        # get the download URL from the API response
        URL="$(grep -Po '(?<="download_link":")[^"]+?(?=")' <<< $HTTP_BODY | sed -e 's/\\//g' )"

        # make sure the temporary path exists for wget to work
        mkdir -p "$(dirname "$TEMP_PATH")"

        echo "Downloading the zip file to /$(realpath --relative-to="$ROOT_DIR" "$TEMP_PATH")"
        # download the zip file to temporary path
        wget -q $URL -O "$TEMP_PATH"
    fi

    echo "Extracting the zip file to /$(realpath --relative-to="$ROOT_DIR" "$DEST_PATH")"
    # unzip contents to destination path
    unzip -q "$TEMP_PATH" -d "$DEST_PATH"

    echo "Done!"
else
    echo "Everything looks alright"
fi