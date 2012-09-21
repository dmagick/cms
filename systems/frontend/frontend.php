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
     * The default page to display.
     *
     * Set by setDefaultPage() and returned by getDefaultPage().
     */
    private static $_defaultPage = '';


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

        $menuItems = array(
            '/' => array(
                'name'     => 'Home',
                'selected' => TRUE,
            ),
            '/about' => array(
                'name' => 'About',
            ),
            '/contact' => array(
                'name' => 'Contact',
            ),
        );

        /**
         * Set the default page title to nothing.
         * This is used for including extra information (eg the post subject).
         */
        template::setKeyword('header', 'pagetitle', '');

        if (empty($page) === FALSE) {
            $bits = explode('/', $page);
            if (empty($bits[0]) === FALSE) {
                $system = array_shift($bits);

                if ($system !== 'frontend') {
                    template::serveTemplate('header');
                }

                /**
                 * Uhoh! Someone's trying to find something that
                 * doesn't exist.
                 */
                if (loadSystem($system) === TRUE) {
                    $url = '/'.$system;
                    if (isset($menuItems[$url]) === TRUE) {
                        $menuItems[$url]['selected'] = TRUE;
                        unset($menuItems['/']['selected']);
                    }

                    $bits = implode('/', $bits);
                    if (isValidSystem($system) === TRUE) {
                        call_user_func_array(array($system, 'process'), array($bits));
                    }
                } else {
                    $url = '';
                    if (isset($_SERVER['PHP_SELF']) === TRUE) {
                        $url = $_SERVER['PHP_SELF'];
                    }
                    $msg = "Unable to find system '".$system."' for url '".$url."'. page is '".$page."'. server info:".var_export($_SERVER, TRUE);
                    messagelog::LogMessage($msg);
                    template::serveTemplate('404');
                }
            }
        } else {
            // No page or default system?
            // Fall back to 'index'.
            template::serveTemplate('header');
            template::serveTemplate('index');
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

        if (empty($page) === TRUE) {
            $page = self::getDefaultPage();
        }

        return $page;
    }



    /**
     * Get the default page, previously set by setDefaultPage
     *
     * @return string
     */
    static public function getDefaultPage()
    {
        return self::$_defaultPage;
    }


    /**
     * Set the default page for the frontend to show.
     *
     * It should come from the config file.
     *
     * @param string $page The new default page.
     */
    static public function setDefaultPage($page='')
    {
        self::$_defaultPage = $page;
    }

}

/* vim: set expandtab ts=4 sw=4: */
