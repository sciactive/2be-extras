#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../../pgrid/jquery.pgrid.js" > "../../pgrid/jquery.pgrid.min.js"
java -jar compiler.jar --js="../../pgrid/jquery.pgrid.js" >> "../../pgrid/jquery.pgrid.min.js"

echo Done.