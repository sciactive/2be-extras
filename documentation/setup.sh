#! /bin/bash

echo "Getting dependencies..."
sudo apt-get install xmlto dblatex texlive-xetex ttf-linux-libertine ttf-freefont python-pygments

echo "Installing custom Pygments alterations..."
sudo cp sources-pdf/_mapping.py /usr/share/pyshared/pygments/formatters/_mapping.py
sudo cp sources-pdf/latexlisting.py /usr/share/pyshared/pygments/formatters/latexlisting.py
sudo ln -s "../../../../../share/pyshared/pygments/formatters/latexlisting.py" /usr/lib/python2.7/dist-packages/pygments/formatters/latexlisting.py
sudo ln -s "../../../../../share/pyshared/pygments/formatters/latexlisting.py" /usr/lib/python2.6/dist-packages/pygments/formatters/latexlisting.py
