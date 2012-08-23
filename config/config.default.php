<?php

$config = array(
    'db' => array(
        'dbname'   => 'cms',
        'username' => '',
        'password' => '',
        'type'     => 'pgsql',
        'prefix'   => 'cms_',
    ),

    'cachedir' => realpath(dirname(__FILE__).'/../cache'),
    'datadir'  => realpath(dirname(__FILE__).'/../data'),
    'logfile'  => realpath(dirname(__FILE__).'/../cache/log'),

    'url'         => 'http://',
    'defaultpage' => '',

    // where to send contact form submissions.
    'contactemail' => '',
);

/* vim: set expandtab ts=4 sw=4: */
