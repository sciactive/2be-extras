#! /bin/sh

echo "Building PDF..."

echo "Fetching new minted.sty"
wget -O sources-pdf/minted.sty.new http://minted.googlecode.com/hg/minted.sty

if [ ! -s sources-pdf/minted.sty.new ]; then
	echo "Couldn't fetch minted.sty. Using the old copy."
	cp sources-pdf/minted.sty sources-pdf/minted.sty.new
fi

cat sources-pdf/minted.sty.new | sed -e "s/-f latex /-f latexlisting /" - > sources-pdf/minted.sty
rm sources-pdf/minted.sty.new

#export PATH="$(pwd)/bin:$PATH"
PATH="$(pwd)/bin:$PATH"
dblatex -p sources-pdf/options.xsl -s sources-pdf/style.sty -b 'xetex' --pdf Pines-Development.xml

# Should this be removed?
#rm sources-pdf/minted.sty
