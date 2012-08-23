<?php
/**
 * View class file.
 * This will generate javascript for gallery or blog images.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * View class file.
 * This will generate javascript for gallery or blog images.
 *
 * @package cms
 */
class View
{
    /**
     * The process function is the only one that does any work.
     */
    public static function process()
    {

        $page = frontend::getCurrentPage();

        // Get rid of '/view' from the front.
        $parts = explode('/', $page);
        array_shift($parts);

        if (empty($parts) === TRUE) {
            url::redirect();
            exit;
        }

        $system = array_shift($parts);
        if (loadSystem($system) === FALSE) {
            $msg = "Unable to use system '".$system."'; server info:".var_export($_SERVER, TRUE);
            messagelog::LogMessage($msg);
            url::redirect();
            exit;
        }

        switch ($system) {
            case 'post':
                $date    = '';
                $subject = '';
                if (isset($parts[0]) === TRUE) {
                    $date = $parts[0];
                }
                if (isset($parts[1]) === TRUE) {
                    $subject = $parts[1];
                }

                $post = Post::getPostByDate($date, $subject);

                if (empty($post) === TRUE) {
                    $msg = "Unable to use system '".$system."'; can't find post for date '".$date."' and subject '".$subject."'; server info:".var_export($_SERVER, TRUE);
                    messagelog::LogMessage($msg);
                    exit;
                }

                $content = Post::getGallery($post);
                echo $content;
                exit;
            break;
        }

    }

}

/* vim: set expandtab ts=4 sw=4: */
