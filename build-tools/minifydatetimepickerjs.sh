#!/bin/sh

echo Compressing JavaScript with Google Closure Compiler.
head -n 19 "../../components/com_datetimepicker/includes/jquery-ui-timepicker-addon.js" > "../../components/com_datetimepicker/includes/jquery-ui-timepicker-addon.min.js"
java -jar compiler.jar --js="../../components/com_datetimepicker/includes/jquery-ui-timepicker-addon.js" >> "../../components/com_datetimepicker/includes/jquery-ui-timepicker-addon.min.js"

echo Done.
