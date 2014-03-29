#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../2be/system/includes/core.js" > "../../2be/system/includes/core.min.js"
java -jar compiler.jar --js="../../2be/system/includes/core.js" >> "../../pines/system/includes/core.min.js"

echo Done.
