#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pnotify/jquery.pnotify.js" > "../../pnotify/jquery.pnotify.min.js"
java -jar compiler.jar --js="../../pnotify/jquery.pnotify.js" >> "../../pnotify/jquery.pnotify.min.js"

echo Done.