#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 8 "../pines/components/com_pgrid/includes/jquery.pgrid.js" > "../pines/components/com_pgrid/includes/jquery.pgrid.min.js"
java -jar compiler.jar --js="../pines/components/com_pgrid/includes/jquery.pgrid.js" >> "../pines/components/com_pgrid/includes/jquery.pgrid.min.js"

echo Done.