#!/usr/bin/env bash

set -e

BOX_DIR="/tmp/box"

mkdir -p ${BOX_DIR}

# Install humbug/box
composer --working-dir=${BOX_DIR} require humbug/box "^3.8" --no-interaction --no-progress --no-suggest

cd build

./app-builder.php

cd build-tmp

composer install --no-dev
${BOX_DIR}/vendor/bin/box compile

cd ..

rm -rf build-tmp
rm -rf ${BOX_DIR}

cd ..
