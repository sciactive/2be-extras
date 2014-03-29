#! /bin/bash

echo "Checking for phpDocumentor..."
if which phpdoc &> /dev/null; then
    echo "Found."
else
    echo "phpDoc not found."
    echo "Please run as administrator:"
    echo "	pear channel-discover pear.phpdoc.org"
    echo "	pear install phpdoc/phpDocumentor"
	exit
fi

if [ ! -d "api-docs" ]; then
	echo "Making working directory..."
	mkdir api-docs
fi
cd api-docs

if [ ! -d "2be" ]; then
	echo "Cloning git repositories..."
	if [ ! -d "2be-core" ]; then
		git clone git://github.com/sciactive/2be-core.git
	fi
	if [ ! -d "2be-packages" ]; then
		git clone git://github.com/sciactive/2be-packages.git
	fi

	echo "Setting up doc installation..."
	mkdir 2be
	mv 2be-core/* 2be/
	mv 2be-packages/com_* 2be/components/
	mv 2be-packages/tpl_* 2be/templates/

	echo "Removing git repos..."
	rm --interactive=never -r 2be-core
	rm --interactive=never -r 2be-packages
else
	echo "Found an existing doc installation. Using that..."
fi

if [ -d "2be-docs" ]; then
	echo "Removing old docs folder..."
	rm -r 2be-docs
fi

echo "Generating documentation..."
mkdir 2be-docs
phpdoc project:run --sourcecode -c ../phpdoc-config.xml -p -d 2be -t 2be-docs
cd ..
echo "Done."