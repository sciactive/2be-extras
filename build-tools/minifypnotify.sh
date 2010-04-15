#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pines/pines/components/com_pnotify/includes/jquery.pnotify.js" > "../../pines/pines/components/com_pnotify/includes/jquery.pnotify.min.js"
java -jar compiler.jar --js="../../pines/pines/components/com_pnotify/includes/jquery.pnotify.js" >> "../../pines/pines/components/com_pnotify/includes/jquery.pnotify.min.js"

echo Done.