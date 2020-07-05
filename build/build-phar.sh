#!/usr/bin/env bash

set -e

cd build

./app-builder.php

cd build-tmp

composer install
box compile

cd ..
rm -rf build-tmp

cd ..
