<?php

require dirname(dirname(__FILE__)).'/init.php';

// These are the only systems we need in the admin area.
$systems = array(
    'admin',
    'adminpost',
    'user',
);

// Use different templates for the admin area.
template::setDir($basedir.'/templates/admin', 'template');

// The admin area will always use a session to see if a visitor
// is logged in or not, so we'll start it up now.
session::start();

// We also always need the user system.
loadSystem('user');

