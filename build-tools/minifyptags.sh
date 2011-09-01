#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 10 "../../ptags/jquery.ptags.js" > "../../ptags/jquery.ptags.min.js"
java -jar compiler.jar --js="../../ptags/jquery.ptags.js" >> "../../ptags/jquery.ptags.min.js"

echo Done.