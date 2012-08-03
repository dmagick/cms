<?php
/**
 * About file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The about class.
 *
 * @package cms
 */
class about
{
    /**
     * Process an action for the frontend.
     * 
     * This class doesn't do any processing, just passes through
     * the template.
     *
     * @param string $action The action to process.
     *                       Doesn't matter what you pass through here,
     *                       it will be ignored.
     *
     * @return void
     */
    public static function process($action='')
    {
        template::setKeyword('header', 'pagetitle', ' - About Splash');

        template::serveTemplate('about');
    }

}

/* vim: set expandtab ts=4 sw=4: */
