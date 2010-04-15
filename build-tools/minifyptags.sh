#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pines/pines/components/com_ptags/includes/jquery.ptags.js" > "../../pines/pines/components/com_ptags/includes/jquery.ptags.min.js"
java -jar compiler.jar --js="../../pines/pines/components/com_ptags/includes/jquery.ptags.js" >> "../../pines/pines/components/com_ptags/includes/jquery.ptags.min.js"

echo Done.