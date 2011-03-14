#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pines/system/includes/pines.js" > "../../pines/system/includes/pines.min.js"
java -jar compiler.jar --js="../../pines/system/includes/pines.js" >> "../../pines/system/includes/pines.min.js"

echo Done.
