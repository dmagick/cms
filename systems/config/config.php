<?php
/**
 * Config class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The config class.
 * Quick way to get details from the config.
 *
 * @package cms
 */
class config
{
    private static $_config = array();

    public static function set($config=array())
    {
        self::$_config = $config;
    }

    public static function get($element=NULL)
    {
        if (isset(self::$_config[$element]) === FALSE) {
            throw new Exception("Unable to get ".$element." from config, it doesn't exist");
        }

        return self::$_config[$element];
    }
}

/* vim: set expandtab ts=4 sw=4: */
