#!/bin/sh

echo Setting up directory structure.
mkdir "jquery.ptags"
echo Importing files.
cp "../../ptags/jquery.ptags.default.css" "jquery.ptags/"
cp "../../ptags/jquery.ptags.js" "jquery.ptags/"

echo Compressing JavaScript with Google Closure Compiler.
head -n 10 jquery.ptags/jquery.ptags.js > jquery.ptags/jquery.ptags.min.js
java -jar compiler.jar --js=jquery.ptags/jquery.ptags.js >> jquery.ptags/jquery.ptags.min.js

echo Zipping the whole directory.
cd jquery.ptags/
zip -r ptags.zip .
cd ..
mv jquery.ptags/ptags.zip .

echo Cleaning up.
rm -r jquery.ptags/

echo Done.