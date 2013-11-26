#!/bin/bash

git clone git@github.com:sciactive/wonder-core.git
git clone git@github.com:sciactive/wonder-packages.git

mkdir wonder
cd wonder
ln -s ../wonder-core/* ./
rm components templates media
mkdir components templates media

cd media
ln -s ../../wonder-core/media/* ./
ln -s ../../wonder-packages/meta_packages/* ./

cd ../templates
ln -s ../../wonder-packages/tpl_* ./

cd ../components
ln -s ../../wonder-packages/com_* ./

rm com_ckeditor com_inuitcss
mv com_pgsql .com_pgsql
mv com_pgentity .com_pgentity

cd ../../
