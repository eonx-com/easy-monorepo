#!/bin/bash

# Find all folders inside 'packages' directory that contain 'tests' folder
folders=$(find packages -type d -name 'tests' -exec dirname {} \;)

for folder in $folders; do
    folder_name=$(basename "$folder")

    vendor/bin/phpunit "packages/$folder_name/tests"
done
