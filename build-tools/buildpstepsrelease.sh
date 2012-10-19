#!/bin/sh

echo Setting up directory structure.
mkdir "jquery.psteps"
echo Importing files.
cp "../../psteps/jquery.psteps.js" "jquery.psteps/"

echo Compressing JavaScript with Google Closure Compiler.
head -n 11 jquery.psteps/jquery.psteps.js > jquery.psteps/jquery.psteps.min.js
java -jar compiler.jar --js=jquery.psteps/jquery.psteps.js >> jquery.psteps/jquery.psteps.min.js

echo Zipping the whole directory.
cd jquery.psteps/
zip -r psteps.zip .
cd ..
mv jquery.psteps/psteps.zip .

echo Cleaning up.
rm -r jquery.psteps/

echo Done.
