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

if [ ! -d "pines" ]; then
	echo "Cloning git repositories..."
	if [ ! -d "pines-core" ]; then
		git clone git://github.com/sciactive/pines-core.git
	fi
	if [ ! -d "pines-components" ]; then
		git clone git://github.com/sciactive/pines-components.git
	fi

	echo "Setting up doc installation..."
	mkdir pines
	mv pines-core/* pines/
	mv pines-components/com_* pines/components/
	mv pines-components/tpl_* pines/templates/

	echo "Removing git repos..."
	rm --interactive=never -r pines-core
	rm --interactive=never -r pines-components
else
	echo "Found an existing doc installation. Using that..."
fi

if [ -d "pines-docs" ]; then
	echo "Removing old docs folder..."
	rm -r pines-docs
fi

echo "Generating documentation..."
mkdir pines-docs
phpdoc project:run --sourcecode -c ../phpdoc-config.xml -p -d pines -t pines-docs
cd ..
echo "Done."