#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 11 "../../psteps/jquery.psteps.js" > "../../psteps/jquery.psteps.min.js"
java -jar compiler.jar --js="../../psteps/jquery.psteps.js" >> "../../psteps/jquery.psteps.min.js"

echo Done.