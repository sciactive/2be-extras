#!/bin/bash

git clone git@github.com:sciactive/2be-core.git
git clone git@github.com:sciactive/2be-packages.git

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
mv com_pgsql .com_pgsql
mv com_pgentity .com_pgentity

cd ../../
