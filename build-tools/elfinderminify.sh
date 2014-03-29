#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
java -jar compiler.jar --js="../../2be/components/com_elfinder/includes/js/elfinder.full.js" > "../../2be/components/com_elfinder/includes/js/elfinder.min.js"

echo Done.
