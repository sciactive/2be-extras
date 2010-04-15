#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pines/pines/components/com_pgrid/includes/jquery.pgrid.js" > "../../pines/pines/components/com_pgrid/includes/jquery.pgrid.min.js"
java -jar compiler.jar --js="../../pines/pines/components/com_pgrid/includes/jquery.pgrid.js" >> "../../pines/pines/components/com_pgrid/includes/jquery.pgrid.min.js"

echo Done.