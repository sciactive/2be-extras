#!/bin/bash

PWDDIR="$(pwd)"
DIR="$(dirname $0)"
if [ -e "$DIR/build-dev-env.sh" ]; then
    cd "$DIR/../.."
fi

question () {
	echo -n "$1"
	read -p " (y/n)[n] " -n 1 answer
	echo
	if [ "$answer" = "y" ]; then
		return 0
	elif [ "$answer" = "Y" ]; then
		return 0
	else
		return 1
	fi
}

question "Are you authenticated to the 2be repos on GitHub?" && {
	git clone git@github.com:sciactive/2be-core.git
	git clone git@github.com:sciactive/2be-packages.git
} || {
	git clone https://github.com/sciactive/2be-core.git
	git clone https://github.com/sciactive/2be-packages.git
}

mkdir 2be
cd 2be
ln -s ../2be-core/* ./
rm components templates media
mkdir components templates media

cd media
ln -s ../../2be-core/media/* ./
ln -s ../../2be-packages/meta_packages/* ./

cd ../templates
ln -s ../../2be-packages/tpl_* ./

cd ../components
ln -s ../../2be-packages/com_* ./

rm com_ckeditor com_inuitcss

question "Are you using MySQL?" && {
	echo "MySQL selected. Disabling PostgreSQL components."
	mv com_pgsql .com_pgsql
	mv com_pgentity .com_pgentity
} || {
	echo "PostgreSQL selected. Disabling MySQL components."
	mv com_mysql .com_mysql
	mv com_myentity .com_myentity
}

cd "$PWDDIR"
