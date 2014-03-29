#! /bin/bash

echo "Removing previous docs..."
rm -R html/*

echo "Copying source files..."
cp -R sources-html/* html/

echo "Building HTML docs..."
xmlto -o html --skip-validation -m html/config.xsl xhtml 2be-Development.xml
