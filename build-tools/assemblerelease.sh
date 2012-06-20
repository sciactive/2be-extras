#! /bin/bash

# Make our directories.
mkdir release
cd release/

mkdir repos
cd repos/

# And get pines.
git clone git://github.com/sciactive/pines-core.git core
git clone git://github.com/sciactive/pines-components.git components

cd ..
mkdir pines

# Now copy the core code.
mv repos/core/* pines/

# Now copy the distribution components.
mv repos/components/com_about/ pines/components/
mv repos/components/com_bootstrap/ pines/components/
#mv repos/components/com_ckeditor/ pines/components/
mv repos/components/com_configure/ pines/components/
mv repos/components/com_content/ pines/components/
mv repos/components/com_dash/ pines/components/
mv repos/components/com_elfinder/ pines/components/
mv repos/components/com_elfinderupload/ pines/components/
mv repos/components/com_entitytools/ pines/components/
mv repos/components/com_fancybox/ pines/components/
mv repos/components/com_iframe/ pines/components/
mv repos/components/com_imodules/ pines/components/
mv repos/components/com_istyle/ pines/components/
mv repos/components/com_jquery/ pines/components/
mv repos/components/com_jstree/ pines/components/
mv repos/components/com_logger/ pines/components/
mv repos/components/com_mailer/ pines/components/
mv repos/components/com_menueditor/ pines/components/
mv repos/components/com_modules/ pines/components/
mv repos/components/com_nivoslider/ pines/components/
mv repos/components/com_notes/ pines/components/
mv repos/components/com_oxygenicons/ pines/components/
mv repos/components/com_package/ pines/components/
mv repos/components/com_pform/ pines/components/
mv repos/components/com_pgrid/ pines/components/
mv repos/components/com_plaza/ pines/components/
mv repos/components/com_pnotify/ pines/components/
mv repos/components/com_popeye/ pines/components/
mv repos/components/com_ptags/ pines/components/
mv repos/components/com_replace/ pines/components/
mv repos/components/com_slim/ pines/components/
mv repos/components/com_su/ pines/components/
mv repos/components/com_timeoutnotice/ pines/components/
mv repos/components/com_tinymce/ pines/components/
mv repos/components/com_uasniffer/ pines/components/
mv repos/components/com_user/ pines/components/
mv repos/components/tpl_mobile/ pines/templates/
mv repos/components/tpl_pines/ pines/templates/
mv repos/components/tpl_pinescms/ pines/templates/
mv repos/components/tpl_print/ pines/templates/

# Now copy MySQL code.
cp -r pines/ pines-mysql/
mv repos/components/com_myentity/ pines-mysql/components/
mv repos/components/com_mysql/ pines-mysql/components/

# Now copy Postgres code.
cp -r pines/ pines-pgsql/
mv repos/components/com_pgentity/ pines-pgsql/components/
mv repos/components/com_pgsql/ pines-pgsql/components/

# Now clean up.
rm --interactive=never -r repos/
rm --interactive=never -r pines/

# And we're done.
echo All done.
