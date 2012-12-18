<?php
/**
 * Template class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The template class.
 * Handles loading, processing keywords, display templates.
 *
 * @package cms
 */
class Template
{

    /**
     * A list of templates for this system to serve up
     * when the occasion arises.
     *
     * @static
     */
    private static $_templateStack = array();

    /**
     * An array of keywords per template to process.
     *
     * @static
     */
    private static $_keywords = array();

    /**
     * Where to get template files from, and also
     * cache files (where to get them from and put them).
     *
     * @var string
     * @see setDir
     * @see getDir
     *
     * @static
     */
    private static $_directories = array();

    /**
     * Set the directory where to get templates from.
     * This is generally done at the top of the main index script.
     * Does a basic check to make sure the dir exists, and if
     * it doesn't it will throw an exception.
     *
     * @param string $dir  The dir to use.
     * @param string $type The type of directory being set (template or cache).
     *
     * @see _directories
     *
     * @return void
     * @throws exception Throws an exception if the template dir
     *                   doesn't exist.
     *
     * @static
     */
    public static function setDir($dir, $type='template')
    {
        if (is_dir($dir) === FALSE) {
            throw new Exception("Template dir doesn't exist");
        }

        self::$_directories[$type] = $dir;
    }

    /**
     * Get the current template directory.
     *
     * @param string $type The type of directory to return.
     *
     * @see _directories
     *
     * @return string    Returns the dir (template or cache).
     * @throws exception Throws an exception if the dir hasn't been
     *                   set before.
     *
     * @static
     */
    public static function getDir($type='template')
    {
        if (isset(self::$_directories[$type]) === FALSE) {
            throw new Exception("The ".$type." dir has not been set.");
        }
        return self::$_directories[$type];
    }

    /**
     * Gets a template to be processed. This will be ready made html
     * but with some basic placeholders.
     * All this function does is return the template to the caller.
     * If it doesn't exist, an exception is thrown.
     *
     * @param string $templateName The template name you're looking for.
     *
     * @return string    The template contents if it exists.
     * @throws exception Throws an exception if the template file doesn't
     *                   exist.
     *
     * @static
     */
    public static function getTemplate($templateName=NULL)
    {
        $file = self::getDir('template').'/'.$templateName.'.tpl';
        if (is_file($file) === FALSE) {
            throw new Exception("Template ".$templateName." doesn't exist");
        }

        $contents = file_get_contents($file);
        return $contents;
    }

    /**
     * Put a template to display on the stack.
     * We don't actually serve it yet in case a page does a redirect
     * or something like that, we just store it.
     * display() goes through the stack and does all the work.
     *
     * @param string $templateName The name of the template to put on
     *                             the stack.
     *
     * @return void
     *
     * @static
     */
    public static function serveTemplate($templateName=NULL)
    {
        self::$_templateStack[] = $templateName;
    }

    /**
     * Process template actions. These are found in templates in the
     * form of a keyword.
     * ~template::action::item~
     * eg to include another template, you do
     * ~template::include::otherTemplateName~
     * and otherTemplateName is processed as a normal template including
     * keywords and possibly recursion.
     *
     * @param string $action Template action to perform.
     *
     * @return string    Returns a string with the action performed,
     *                   for example the template included and keywords
     *                   already processed.
     * @throws exception Throws an exception if you try to process an
     *                   action that isn't handled yet.
     *
     * @uses template::getTemplate
     * @uses template::processKeywords
     *
     * @static
     */
    private static function processTemplateAction($action)
    {
        list($action, $item) = explode('::', $action);
        switch ($action) {
            case 'include':
                $content = self::getTemplate($item);
                $content = self::processKeywords($content, $item);
            break;

            default:
                throw new Exception("Unknown template action ".$action);
        }

        return $content;
    }

    /**
     * Process keywords for a template if there are any to be processed.
     * Returns the content with keywords replaced.
     *
     * @param string $content      The content to put the keywords into.
     * @param string $templateName The name of the template so we know which
     *                             keywords to get.
     *
     * @return string Returns the content with the keywords processed.
     *
     * @uses template::processTemplateAction
     *
     * @static
     */
    private static function processKeywords($content, $templateName)
    {
        preg_match_all('/~template::(.*?)~/', $content, $matches);
        if (empty($matches[1]) === FALSE) {
            foreach ($matches[1] as $mpos => $match) {
                $result = self::processTemplateAction($match);
                $content = str_replace($matches[0][$mpos], $result, $content);
            }
        }

        if (isset(self::$_keywords[$templateName]) === FALSE) {
            return $content;
        }

        if (empty(self::$_keywords[$templateName]) === TRUE) {
            return $content;
        }

        // Keywords are kept as an array - once for each time the template
        // is in the stack.
        // So if we add a template 3 times to the stack, we need values
        // for displaying the template 3 times.
        $keywords = array_keys(self::$_keywords[$templateName]);
        $values   = array();
        foreach ($keywords as $keyword) {
            $values[$keyword] = array_shift(self::$_keywords[$templateName][$keyword]);
        }

        $content  = str_replace($keywords, $values, $content);
        return $content;
    }

    /**
     * Replace built in keywords and return the new content.
     * For example, replace url keyword.
     *
     * @param string $content The content to process keywords for.
     *
     * @return string
     *
     * @uses session::getFlashMessages
     * @uses template::getTemplate
     *
     * @static
     */
    private static function processBuiltInKeywords($t, $content)
    {
        $source  = array(
                    '~url::baseurl~',
                    '~url::adminurl~',
                   );
        $replace = array(
                    url::getUrl(),
                    url::getUrl().'/admin',
                   );
        
        if (strpos($content, '~flashmessage~') !== FALSE) {
            $allMessages = '';
            $flashMessages = session::getFlashMessages();
            foreach ($flashMessages as $messageInfo) {
                $message     = $messageInfo[0];
                $messageType = $messageInfo[1];

                switch ($messageType) {
                    case 'error':
                        $templateName = 'flash.message.error';
                    break;
                    case 'success':
                        $templateName = 'flash.message.success';
                    break;
                }

                $template     = self::getTemplate($templateName);
                $template     = str_replace('~message~', $message, $template);
                $allMessages .= $template;
            }
            /**
             * Make sure we replace keywords in our messages as well,
             * before we add the flashmessage to the replacement list.
             */
            $allMessages = str_replace($source, $replace, $allMessages);

            $source[]  = '~flashmessage~';
            $replace[] = $allMessages;
        }
        $content = str_replace($source, $replace, $content);
        return $content;
    }

    /**
     * Go through the list of templates we've been told to process,
     * fix up keywords and print the template out.
     * This should be the last step of a page, so we go through the
     * list of templates previously set, process keywords
     * and print them out.
     *
     * @return void Prints out the content, doesn't return it.
     *
     * @uses template::getTemplate
     * @uses template::processKeywords
     * @uses template::_templateStack
     *
     * @static
     */
    public static function display()
    {
        foreach (self::$_templateStack as $template) {
            $content = self::getTemplate($template);
            $content = self::processKeywords($content, $template);
            $content = self::processBuiltInKeywords($template, $content);
            echo $content;
        }
        self::$_templateStack = array();
    }

    /**
     * Set a keyword and value for a particular template.
     * This is used by processKeywords to go through and replace.
     *
     * @param string $template Template we are setting the keyword for.
     * @param string $keyword  Keyword name.
     * @param string $value    Keyword value.
     *
     * @static
     */
    public function setKeyword($template, $keyword, $value)
    {
        if (isset(self::$_keywords[$template]) === FALSE) {
            self::$_keywords[$template] = array();
        }
        if (isset(self::$_keywords[$template]['~'.$keyword.'~']) === FALSE) {
            self::$_keywords[$template]['~'.$keyword.'~'] = array();
        }
        self::$_keywords[$template]['~'.$keyword.'~'][] = $value;
    }

    /**
     * Unload a template from the template stack.
     *
     * This is useful if the header is loaded but then something happens
     * and the empty header needs to be displayed instead.
     * The header template can be unloaded from the stack so you only
     * see the header once instead of twice.
     * It removes it from the stack and also removes all keywords that may
     * have been set.
     *
     * @param string $template The template to unload from the stack.
     *
     * @return void
     */
    public static function unload($template='')
    {
        if (in_array($template, self::$_templateStack) === TRUE) {
            $key = array_search($template, self::$_templateStack);
            if ($key !== FALSE) {
                unset(self::$_templateStack[$key]);
            }
            if (isset(self::$_keywords[$template]) === TRUE) {
                unset(self::$_keywords[$template]);
            }
        }
    }

    /**
     * Clear out the entire template stack and all keywords.
     * Used by adminpost ajax requests so it can return clean, empty
     * data to jquery.
     *
     * @return void
     */
    public static function clearStack()
    {
        foreach (self::$_templateStack as $template) {
            if (isset(self::$_keywords[$template]) === TRUE) {
                unset(self::$_keywords[$template]);
            }
        }
        self::$_templateStack = array();
    }
}

/* vim: set expandtab ts=4 sw=4: */
