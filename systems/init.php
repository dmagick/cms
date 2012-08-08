<?php
/**
 * Init file handles the start up stuff.
 * Its all then handed off to the appropriate
 * system for it to deal with the rest.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * Set up the base dir.
 */
$basedir = dirname(dirname(__FILE__));

/**
 * Of course we need our config.
 */
require $basedir.'/config/config.php';

if (function_exists('date_default_timezone_set') === TRUE) {
    date_default_timezone_set('Australia/NSW');
}

/**
 * A list of systems that are required for each page load.
 */
$requiredSystems = array(
    'db',
    'frontend',
    'stats',
    'messagelog',
    'session',
    'template',
    'url',
);

/**
 * A list of valid non-required systems.
 * This list is also used to by isValidSystem
 * to make sure a user isn't trying to cause errors by
 * making up their own url.
 * 
 * @see isValidSystem
 * @see loadSystem
 */
$systems = array(
    'about',
    //'contact',
    'post',
    'user',
);

/**
 * Helper function to make sure the requested system is valid.
 * Just in case someone decides to change the url (hoping for
 * information disclosure etc).
 *
 * @param string $systemName The system being checked
 *
 * @uses systems
 *
 * @return boolean
 */
function isValidSystem($systemName=NULL)
{
    global $systems;
    global $requiredSystems;
    if (
        in_array($systemName, $systems) === TRUE ||
        in_array($systemName, $requiredSystems) === TRUE
    ) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Load a particular system into memory.
 *
 * If something has already been loaded (either it's required or something
 * else has loaded it previously), this will just return.
 *
 * @param string $systemName The system to load.
 *
 * @return boolean Returns false if the system is invalid, otherwise it
 *                 loads the system and returns true.
 */
function loadSystem($systemName=NULL)
{
    global $basedir;
    global $requiredSystems;

    static $_loaded = array();
    if (isset($_loaded[$systemName]) === TRUE) {
        return TRUE;
    }

    if (isValidSystem($systemName) === TRUE) {
        if (in_array($systemName, $requiredSystems) === FALSE) {
            require $basedir.'/systems/'.$systemName.'/'.$systemName.'.php';
        }

        $_loaded[$systemName] = TRUE;
        return TRUE;
    }

    return FALSE;
}

/**
 * Gets the ip from the users browser.
 * Checks for X_FORWARDED_FOR in case they are behind a proxy.
 * If that's not available, uses REMOTE_ADDR
 *
 * @return string The users ip.
 *
 * @static
 */
function getIp()
{
    $ip = '';
    if (isset($_SERVER['X_FORWARDED_FOR']) === TRUE) {
        $addrs = explode(',',$_SERVER['X_FORWARDED_FOR']);
        $ip    = array_pop($addrs);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return trim($ip);
}

/**
 * Include all of our required systems.
 * Since we're using a consistent structure,
 * we can just loop over 'em to do it all in one go.
 */
foreach ($requiredSystems as $system) {
    require $basedir.'/systems/'.$system.'/'.$system.'.php';
}

template::setDir($basedir.'/templates');
url::setUrl($config['url']);

try {
    session::setDir($config['cachedir']);
    messagelog::setLog($config['cachedir'].'/debug.log');
} catch (Exception $e) {
    error_log('Unable to set session dir or message log:'.$e->getMessage());
    template::serveTemplate('error.technical');
    template::display();
    exit;
}

try {
    db::connect($config['db']);
} catch (Exception $e) {
    messagelog::LogMessage($e->getMessage());
    template::serveTemplate('error.technical');
    template::display();
    exit;
}

/* vim: set expandtab ts=4 sw=4: */
