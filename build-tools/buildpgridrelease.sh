#!/bin/sh

echo Setting up directory structure.
mkdir "jquery.pgrid"
mkdir "jquery.pgrid/use for pines style icons"
echo Importing files.
cp "../../pgrid/jquery.pgrid.default.icons.css" "jquery.pgrid/use for pines style icons/"
cp "../../pgrid/jquery.pgrid.touch.icons.css" "jquery.pgrid/use for pines style icons/"
cp "../../pgrid/jquery.pgrid.default.css" "jquery.pgrid/"
cp "../../pgrid/jquery.pgrid.touch.css" "jquery.pgrid/"
cp "../../pgrid/jquery.pgrid.js" "jquery.pgrid/"

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 jquery.pgrid/jquery.pgrid.js > jquery.pgrid/jquery.pgrid.min.js
java -jar compiler.jar --js=jquery.pgrid/jquery.pgrid.js >> jquery.pgrid/jquery.pgrid.min.js

echo Zipping the whole directory.
cd jquery.pgrid/
zip -r pgrid.zip .
cd ..
mv jquery.pgrid/pgrid.zip .

echo Cleaning up.
rm -r jquery.pgrid/

echo Done.