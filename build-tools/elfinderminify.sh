#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
java -jar compiler.jar --js="../../pines/components/com_elfinder/includes/js/elfinder.full.js" > "../../pines/components/com_elfinder/includes/js/elfinder.min.js"

echo Done.
