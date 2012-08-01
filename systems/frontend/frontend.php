<?php
/**
 * Frontend class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The frontend class.
 * Works out which page you are trying to view and processes it.
 * Could hand off requests to other systems if it needs to.
 *
 * @package cms
 */
class frontend
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
        $page = '';
        if (isset($_SERVER['PATH_INFO']) === TRUE) {
            $page = trim($_SERVER['PATH_INFO'], '/');
        }

        if (empty($page) === FALSE) {
            $info = trim($page, '/');
            $bits = explode('/', $info);
            if (empty($bits[0]) === FALSE) {
                $system = array_shift($bits);

                if ($system !== 'frontend') {
                    template::serveTemplate('header');
                    template::display();
                }

                $bits   = implode('/', $bits);
                if (isValidSystem($system) === TRUE) {
                    call_user_func_array(array($system, 'process'), array($bits));
                }
            }
        } else {
            template::serveTemplate('header');
            template::display();
            $posts = Post::getPosts(1);
            if (empty($posts) === TRUE) {
                template::serveTemplate('post.empty');
                template::display();
            }
        }

        template::serveTemplate('footer');
        template::display();
    }
}

/* vim: set expandtab ts=4 sw=4: */
