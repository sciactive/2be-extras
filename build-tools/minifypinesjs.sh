#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pines/system/includes/pines.js" > "../../pines/system/includes/pines.min.js"
java -jar compiler.jar --js="../../pines/system/includes/pines.js" >> "../../pines/system/includes/pines.min.js"

echo Compressing json2.js with Google Closure Compiler.
java -jar compiler.jar --js="../../pines/system/includes/json2.js" > "../../pines/system/includes/json2.min.js"

echo Done.
