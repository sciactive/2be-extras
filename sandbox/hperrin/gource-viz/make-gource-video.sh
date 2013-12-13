#!/bin/bash

# Clone all the repos.
git clone git://github.com/sciactive/pines-core.git
git clone git://github.com/sciactive/pines-components.git
git clone git://github.com/sciactive/pines-tools.git
git clone git://github.com/sciactive/pform.git
git clone git://github.com/sciactive/pnotify.git
git clone git://github.com/sciactive/pgrid.git
git clone git://github.com/sciactive/ptags.git

# Output a custom log of each repo.
gource --output-custom-log core.txt pines-core/
gource --output-custom-log components.txt pines-components/
gource --output-custom-log tools.txt pines-tools/
gource --output-custom-log pform.txt pform/
gource --output-custom-log pnotify.txt pnotify/
gource --output-custom-log pgrid.txt pgrid/
gource --output-custom-log ptags.txt ptags/

# Add a directory name to each log entry.
sed -i -r "s#(.+)\|#\1|/core#" core.txt
sed -i -r "s#(.+)\|#\1|/components#" components.txt
sed -i -r "s#(.+)\|#\1|/tools#" tools.txt
sed -i -r "s#(.+)\|#\1|/pform#" pform.txt
sed -i -r "s#(.+)\|#\1|/pnotify#" pnotify.txt
sed -i -r "s#(.+)\|#\1|/pgrid#" pgrid.txt
sed -i -r "s#(.+)\|#\1|/ptags#" ptags.txt

# Combine all logs into a big sorted log.
cat core.txt components.txt tools.txt pform.txt pnotify.txt pgrid.txt ptags.txt | sort -n > combined.txt

# Create the gource video.
gource --title WonderPHP -1280x720 --highlight-colour 22FF55 --hide mouse,filenames --highlight-users --max-user-speed 1000 --user-scale 5 --max-file-lag 0.1 --max-files 0 -i 0 -a 1 -s .5 -c 4 -e 0.01 -o gource-output.ppm -r 60 combined.txt
