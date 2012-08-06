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
        session::start();

        template::setKeyword('header', 'pagetitle', ' - Contact Us');

        $elements = array(
            'email'   => 'email address',
            'message' => 'message',
        );

        if (isset($_POST) === FALSE || empty($_POST) === TRUE) {
            $action = '';
        }

        $contactheader = '';
        $sendform      = FALSE;
        $errormessage  = '';

        switch ($action)
        {
            case 'submit':
                $token         = '';
                $spamcheck     = '';
                $sendform      = TRUE;

                try {
                    $token     = session::get('token');
                    $spamcheck = session::get('spamcheck');
                } catch (Exception $e) {
                    $errormessage = 'There was a problem submitting the form. Please try again.';
                    $sendform     = FALSE;
                }

                if (
                    empty($token) === TRUE ||
                    isset($_POST['token']) === FALSE ||
                    ($_POST['token'] !== $token)
                ) {
                    $errormessage = 'There was a problem submitting the form. Please try again.';
                    $sendform     = FALSE;
                }

                if (
                    isset($_POST['spamcheck']) === FALSE ||
                    empty($_POST['spamcheck']) === TRUE ||
                    ($_POST['spamcheck'] != $spamcheck)
                ) {
                    $errormessage = 'There was a problem checking your answer. Please try again.';
                    $sendform     = FALSE;
                }
            break;
        }

        foreach ($elements as $element => $elementDescription) {
            $$element = '';
            if (isset($_POST[$element]) === TRUE && empty($_POST[$element]) === FALSE) {
                $$element = htmlspecialchars($_POST[$element]);
            } else {
                if ($sendform === TRUE) {
                    $errormessage .= 'You forgot to fill in the '.$elementDescription."\n";
                    $sendform      = FALSE;
                }
            }
            template::setKeyword('contact', $element, $$element);
            template::setKeyword('contact.send', $element, $$element);

            if ($element === 'message') {
                template::setKeyword('contact.send', $element, nl2br($$element));
            }
        }

        if ($sendform === TRUE) {
            $log  = "Someone contacted you. Details are:\n";
            $log .= "From: ".$email."\n";
            $log .= "Message: ".$message."\n\n";
            messageLog::LogMessage($log);
            template::serveTemplate('contact.send');
            return;
        }

        if (empty($_POST) === FALSE) {
            $log  = "Someone tried to contact you but failed. Details are:\n";
            $log .= "Post variables:".print_r($_POST, TRUE)."\n";
            $log .= "Session info:".print_r($_SESSION, TRUE)."\n\n";
            messageLog::LogMessage($log);
        }

        $types = array('+', '-');
        $typeK = array_rand($types);
        $type  = $types[$typeK];

        $number1 = rand(1,15);
        $number2 = $number1;
        while ($number2 == $number1) {
            $number2 = rand(1, 15);
        }

        switch ($type) {
            case '+':
                $answer = $number1 + $number2;
                break;

            case '-':
                if ($number2 > $number1) {
                    $numberSwitch = $number2;
                    $number2      = $number1;
                    $number1      = $numberSwitch;
                }

                $answer = $number1 - $number2;
                break;
        }

        $spamcheck = $number1." ".$type." ".$number2;

        $token = md5(uniqid('contact'.rand(1,20), TRUE));
        session::set('token', $token);
        session::set('spamcheck', $answer);

        template::setKeyword('contact', 'token', $token);
        template::setKeyword('contact', 'spamcheck', $spamcheck);
        template::setKeyword('contact', 'contactheader', nl2br(trim($contactheader."\n".$errormessage)));

        template::serveTemplate('contact');
    }

}

/* vim: set expandtab ts=4 sw=4: */
