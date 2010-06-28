#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
java -jar compiler.jar --js="../../pines/components/com_jstree/includes/jquery.jstree.js" > "../../pines/components/com_jstree/includes/jquery.jstree.min.js"

echo Done.