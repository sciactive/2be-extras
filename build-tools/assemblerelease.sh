#! /bin/bash

# Make our directories.
mkdir release
cd release/

mkdir repos
cd repos/

# And get 2be.
git clone git://github.com/sciactive/2be-core.git core
git clone git://github.com/sciactive/2be-packages.git packages

cd ..
mkdir 2be

# Now copy the core code.
mv repos/core/* 2be/

# Now copy the distribution components.
mv repos/packages/com_about/ 2be/components/
mv repos/packages/com_bootstrap/ 2be/components/
#mv repos/packages/com_ckeditor/ 2be/components/
mv repos/packages/com_configure/ 2be/components/
mv repos/packages/com_content/ 2be/components/
mv repos/packages/com_dash/ 2be/components/
mv repos/packages/com_elfinder/ 2be/components/
mv repos/packages/com_elfinderupload/ 2be/components/
mv repos/packages/com_entityhelper/ 2be/components/
mv repos/packages/com_entitytools/ 2be/components/
mv repos/packages/com_fancybox/ 2be/components/
mv repos/packages/com_iframe/ 2be/components/
mv repos/packages/com_imodules/ 2be/components/
mv repos/packages/com_istyle/ 2be/components/
mv repos/packages/com_jquery/ 2be/components/
mv repos/packages/com_jstree/ 2be/components/
mv repos/packages/com_logger/ 2be/components/
mv repos/packages/com_mailer/ 2be/components/
mv repos/packages/com_markdown/ 2be/components/
mv repos/packages/com_menueditor/ 2be/components/
mv repos/packages/com_modules/ 2be/components/
mv repos/packages/com_nivoslider/ 2be/components/
mv repos/packages/com_notes/ 2be/components/
mv repos/packages/com_oxygenicons/ 2be/components/
mv repos/packages/com_package/ 2be/components/
mv repos/packages/com_pform/ 2be/components/
mv repos/packages/com_pgrid/ 2be/components/
mv repos/packages/com_plaza/ 2be/components/
mv repos/packages/com_pnotify/ 2be/components/
mv repos/packages/com_popeye/ 2be/components/
mv repos/packages/com_ptags/ 2be/components/
mv repos/packages/com_replace/ 2be/components/
mv repos/packages/com_slim/ 2be/components/
mv repos/packages/com_su/ 2be/components/
mv repos/packages/com_timeoutnotice/ 2be/components/
mv repos/packages/com_tinymce/ 2be/components/
mv repos/packages/com_uasniffer/ 2be/components/
mv repos/packages/com_user/ 2be/components/
mv repos/packages/tpl_mobile/ 2be/templates/
mv repos/packages/tpl_pines/ 2be/templates/
mv repos/packages/tpl_pinescms/ 2be/templates/
mv repos/packages/tpl_print/ 2be/templates/

# Now copy MySQL code.
cp -r 2be/ 2be-mysql/
mv repos/packages/com_myentity/ 2be-mysql/components/
mv repos/packages/com_mysql/ 2be-mysql/components/

# Now copy Postgres code.
cp -r 2be/ 2be-pgsql/
mv repos/packages/com_pgentity/ 2be-pgsql/components/
mv repos/packages/com_pgsql/ 2be-pgsql/components/

# Now clean up.
rm --interactive=never -r repos/
rm --interactive=never -r 2be/

# And we're done.
echo All done.
