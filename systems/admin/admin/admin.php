<?php
/**
 * Admin class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The admin class.
 * Works out which page you are trying to view and processes it.
 * Could hand off requests to other systems if it needs to.
 *
 * @package cms
 */
class admin
{

    /**
     * Display a page.
     *
     * If the user hasn't logged in, it remembers the page you are trying
     * to view, takes you to the login page, then if that works, redirects
     * the user back to the original page.
     *
     * @return void
     *
     * @uses isValidSystem
     * @uses session::get
     * @uses session::has
     * @uses session::remove
     * @uses session::set
     * @uses template::display
     * @uses template::serveTemplate
     * @uses user::process
     */
    public static function display()
    {
        $page = self::getCurrentPage();

        if (session::has('user') === FALSE) {
            user::setLoginUrl('~url::adminurl~');
            if (session::has('viewPage') === FALSE) {
                session::set('viewPage', $page);
            }
            user::process();
            return;
        }

        if (session::has('viewPage') === TRUE) {
            $page = session::get('viewPage');
            session::remove('viewPage');
        }

        template::serveTemplate('footer');
        template::display();

    }

    /**
     * Get the current page trying to be viewed.
     *
     * @return string Returns the current page, or default page.
     */
    public static function getCurrentPage()
    {
        $page = '';

        if (isset($_SERVER['REQUEST_URI']) === TRUE && isset($_SERVER['HTTP_HOST']) === TRUE) {
            $protocol = 'http';
            $page     = $protocol.'//'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
            $page     = substr($page, strlen(url::getUrl()));
            $page     = trim($page, '/');
        }

        return $page;
    }

}

/* vim: set expandtab ts=4 sw=4: */
