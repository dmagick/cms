<?php
/**
 * Messagelog class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The messagelog class.
 * Handles saving log files to a particular location.
 * Useful for debugging or even action logs.
 *
 * @package cms
 */
class MessageLog
{
    /**
     * The current log level.
     */
    private static $_logLevel = '';

    /**
     * The log file that will be written to.
     *
     * @see setLog
     */
    private static $_logFile = NULL;

    /**
     * Set the message log location to a particular file.
     *
     * Checks to make sure that:
     * - the log file exists and is writable
     * - or the log directory is writable (so we can create the
     *   log file ourselves)
     * - or we can create the log directory and the log file
     * If none of these conditions are met, an exception is thrown.
     *
     * @param string $logFile The log file location to use.
     *
     * @return void
     * @throws exception Throws an exception if the writable log file
     *                   conditions aren't met.
     */
    public static function setLog($logFile)
    {
        if (file_exists($logFile) === TRUE) {
            if (is_writable($logFile) === TRUE) {
                self::$_logFile = $logFile;
                return;
            }
            throw new Exception("Unable to set log file - it exists but is not writable");
        }
        $parent = dirname($logFile);
        if (is_dir($parent) === TRUE) {
            if (is_writable($parent) === TRUE) {
                self::$_logFile = $logFile;
                return;
            }
            throw new Exception("Unable to set log file - parent directory exists but is not writable");
        }
        if (mkdir($parent, 0755, TRUE) === TRUE) {
            self::$_logFile = $logFile;
            return;
        }
        throw new Exception("Unable to set log file - unable to make directory");
    }

    /**
     * Set the current log level to a particular one.
     *
     * @param string $level The new level to set.
     *
     * @return void
     * @throws exception Throws an exception if the level isn't valid.
     */
    public static function setLogLevel($level='info')
    {
        $levels = array(
            'info',
            'debug',
        );

        if (in_array($level, $levels) === FALSE) {
            throw new Exception("Unable to set log level to ".$level);
        }
        self::$_logLevel = $level;
    }

    /**
     * Log a particular message to the previously set location if logging is
     * enabled.
     *
     * @param mixed $info The message to log.
     *                    It can be a boolean variable, array, string,
     *                    object, and it's logged appropriately.
     *
     * @return void
     * @throws exception Throws an exception if the log location isn't set
     *                   or if we give it an invalid log message type to
     *                   deal with.
     */
    public static function LogMessage($info, $level='info')
    {

        /**
         * If we are trying to log a 'debug' message,
         * but the current level is only set to 'info',
         * don't log it.
         */
        switch ($level) {
            case 'debug':
                if (self::$_logLevel === 'info') {
                    return;
                }
            break;
        }

        if (self::$_logFile === NULL) {
            throw new Exception("Log file has not been set");
        }

        $time = date('Y-m-d H:i:s');
        $type = gettype($info);
        switch ($type) {
            case 'boolean':
                $info = var_export($info, TRUE);
                
            case 'double':
            case 'integer':
            case 'string':
                $info = trim($info);
            break;

            case 'array':
            case 'object':
                $info = print_r($info, TRUE);
            break;

            default:
                throw new Exception("Not sure how to handle this type of variable: ".gettype($info));
        }

        error_log($time." ".str_replace("\n", "\n\t", $info)."\n", 3, self::$_logFile);
    }
}

/* vim: set expandtab ts=4 sw=4: */
