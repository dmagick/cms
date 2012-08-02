<?php
/**
 * Post contact file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The contact class.
 *
 * @package cms
 */
class contact
{

    /**
     * Process an action for the frontend.
     *
     * @param string $action The action to process.
     *
     * @return void
     */
    public static function process($action='')
    {

        template::setKeyword('header', 'pagetitle', ' - Contact Us');

        $elements = array(
            'content',
            'email',
            'subject',
        );

        foreach ($elements as $element) {
            $$element = '';
            if (isset($_POST[$element]) === TRUE) {
                $$element = htmlspecialchars($_POST[$element]);
            }
            template::setKeyword('contact', $element, $$element);
        }

        if (isset($_POST) === FALSE || empty($_POST) === TRUE) {
            $action = '';
        }

        switch ($action)
        {
            case 'submit':
                template::setKeyword('contact', 'contactheader', 'Thanks for contacting us. We will be in touch as soon as possible.');
            break;

            default:
                template::setKeyword('contact', 'contactheader', '');
        }

        template::serveTemplate('contact');
    }

}

/* vim: set expandtab ts=4 sw=4: */
