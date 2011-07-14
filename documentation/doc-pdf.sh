#! /bin/sh

echo "Building PDF..."
dblatex -p sources-pdf/options.xsl -s sources-pdf/style.sty -b 'xetex' --pdf Pines-Development.xml
#dblatex -p sources-pdf/options.xsl -b 'xetex' --pdf Pines-Development.xml
