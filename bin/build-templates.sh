#!/bin/bash

if  [[ $1 ]]; then

    if [ -f "${BASH_SOURCE%/*}/../page-templates/$1-template.php" ] || [ -f "${BASH_SOURCE%/*}/../views/$1.twig" ] || [ -f "${BASH_SOURCE%/*}/../js/app/views/$1.js" ] || [ -f "${BASH_SOURCE%/*}/../css/views/_$1.scss" ]; then
        echo "File(s) with that name already exist. Aborting"
        exit 2
    fi

    echo 'Creating PHP Template...'
    if [ ! -d "${BASH_SOURCE%/*}/../page-templates" ]; then
        mkdir ${BASH_SOURCE%/*}/../page-templates
    fi
    CLEANNAME=`echo $1 | tr '-' ' ' | tr '_' ' ' | perl -pe 's/./\u$&/'`
    cat <<EOF >${BASH_SOURCE%/*}/../page-templates/$1-template.php
<?php
/**
* Template Name: $CLEANNAME template
*
* Description:
*/

\$context = Timber::get_context();
\$post = new TimberPost();
\$context['post'] = \$post;
Timber::render( '$1.twig', \$context );
EOF

    echo 'Creating Twig Template...'
	if [ -f "${BASH_SOURCE%/*}/../views/page.twig" ]; then
		echo 'Copying page.twig...'
    	cp ${BASH_SOURCE%/*}/../views/page.twig ${BASH_SOURCE%/*}/../views/$1.twig
	else
		echo 'No page.twig file found. Creating blank template...'
		touch ${BASH_SOURCE%/*}/../views/$1.twig
	fi
	echo 'Creating Javascript File...'
    if [ ! -d "${BASH_SOURCE%/*}/../js/app/views" ]; then
        mkdir ${BASH_SOURCE%/*}/../js/app/views
    fi
    cat <<EOF >${BASH_SOURCE%/*}/../js/app/views/$1.js
\$(document).ready(function(){

});
EOF

	echo 'Creating Advanced Custom Field Page Template File...'
	if [ ! -d "${BASH_SOURCE%/*}/../php/acf-views" ]; then
		mkdir ${BASH_SOURCE%/*}/../php/acf-views
	fi
	cat <<EOF >${BASH_SOURCE%/*}/../php/acf-views/$1-acf.php
<?php

EOF

    echo 'Creating SCSS File...'
    if [ ! -d "${BASH_SOURCE%/*}/../css/views" ]; then
        mkdir ${BASH_SOURCE%/*}/../css/views
    fi
    cat <<EOF >${BASH_SOURCE%/*}/../css/views/$1.scss
@import "css/mixins";
EOF
    exit 0
else
    echo 'File name required. Aborting.'
    exit 1
fi
