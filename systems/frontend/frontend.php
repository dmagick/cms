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
        $page = 'post';
        if (isset($_SERVER['PATH_INFO']) === TRUE) {
            $page = trim($_SERVER['PATH_INFO'], '/');
        }

        $pageStart = microtime(TRUE);

        $menuItems = array(
            '/' => array(
                'name'     => 'Home',
                'selected' => TRUE,
            ),
            '/about' => array(
                'name' => 'About',
            ),
            /**
            '/contact' => array(
                'name' => 'Contact',
            ),
             */
        );

        /**
         * Set the default page title to nothing.
         * This is used for including extra information (eg the post subject).
         */
        template::setKeyword('header', 'pagetitle', '');

        if (empty($page) === FALSE) {
            $info = trim($page, '/');
            $bits = explode('/', $info);
            if (empty($bits[0]) === FALSE) {
                $system = array_shift($bits);

                if ($system !== 'frontend') {
                    template::serveTemplate('header');
                }

                /**
                 * Uhoh! Someone's trying to find something that
                 * doesn't exist.
                 */
                if (loadSystem($system) === FALSE) {
                    $url = '';
                    if (isset($_SERVER['PHP_SELF']) === TRUE) {
                        $url = $_SERVER['PHP_SELF'];
                    }
                    $msg = "Unable to find system '".$system."' for url '".$url."'";
                    messagelog::LogMessage($msg);
                    template::unload('header');
                    template::serveTemplate('error.technical');
                    template::display();
                    return;
                }

                $url = '/'.$system;
                if (isset($menuItems[$url]) === TRUE) {
                    $menuItems[$url]['selected'] = TRUE;
                    unset($menuItems['/']['selected']);
                }

                $bits = implode('/', $bits);
                if (isValidSystem($system) === TRUE) {
                    call_user_func_array(array($system, 'process'), array($bits));
                }
            }
        }

        $menu = '';
        foreach ($menuItems as $url => $info) {
            $class = '';
            if (isset($info['selected']) === TRUE && $info['selected'] === TRUE) {
                $class = 'here';
            }

            $menu .= '<li class="'.$class.'">';
            $menu .= '<a href="~url::baseurl~'.$url.'">'.$info['name'].'</a>';
            $menu .= '</li>';
            $menu .= "\n";
        }

        template::setKeyword('header', 'menu', $menu);

        template::serveTemplate('footer');
        template::display();

        $timeTaken  = microtime(TRUE) - $pageStart;
        $queryCount = db::getQueryCount();
        stats::recordHit($timeTaken, $queryCount);

    }
}

/* vim: set expandtab ts=4 sw=4: */
