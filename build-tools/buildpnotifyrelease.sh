#!/bin/sh

echo Setting up directory structure.
mkdir "jquery.pnotify"
mkdir "jquery.pnotify/use for pines style icons"
echo Importing files.
cp "../../pnotify/jquery.pnotify.default.icons.css" "jquery.pnotify/use for pines style icons/"
cp "../../pnotify/jquery.pnotify.default.css" "jquery.pnotify/"
cp "../../pnotify/jquery.pnotify.js" "jquery.pnotify/"

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 jquery.pnotify/jquery.pnotify.js > jquery.pnotify/jquery.pnotify.min.js
java -jar compiler.jar --js=jquery.pnotify/jquery.pnotify.js >> jquery.pnotify/jquery.pnotify.min.js

echo Zipping the whole directory.
cd jquery.pnotify/
zip -r jquery.pnotify.zip .
cd ..
mv jquery.pnotify/jquery.pnotify.zip .

echo Cleaning up.
rm -r jquery.pnotify/

echo Done.