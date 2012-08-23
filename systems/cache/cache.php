<?php
/**
 * Cache class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The cache class.
 * Handles loading, saving, deleting.
 *
 * @package cms
 */
class cache
{
    /**
     * Base cache dir.
     */
    private static $_cacheDir = NULL;

    /**
     * Set the directory where to get cache files from.
     *
     * @param string $dir The dir to use.
     *
     * @see _cacheDir
     *
     * @return void
     */
    public static function setDir($dir)
    {
        if (is_dir($dir) === FALSE) {
            throw new Exception("Cache dir doesn't exist");
        }

        if (is_writable($dir) === FALSE) {
            throw new Exception("Cache dir is not writable");
        }

        self::$_cacheDir = $dir;
    }

    private static function _checkDir($name)
    {
        if (self::$_cacheDir === NULL) {
            throw new Exception("Cache dir hasn't been set yet");
        }

        $path = realpath(self::$_cacheDir.'/'.$name);

        if ($path === FALSE || file_exists($path) === FALSE) {
            return FALSE;
        }

        // Make sure the path is inside the cache dir.
        if (strpos($path, self::$_cacheDir) !== 0) {
            return FALSE;
        }

        return $path;
    }

    public static function getFileName($type='', $id=0, $name='')
    {
        // We must use integer's for the id's.
        if ((int)$id !== $id) {
            return FALSE;
        }

        if (empty($name) === TRUE) {
            return FALSE;
        }

        $path = self::_checkDir($type.'/'.$id.'/'.$name);

        // If the file doesn't exist, return.
        if ($path === FALSE) {
            return FALSE;
        }

        return $path;
    }


    public static function get($type='', $id=0, $name='', $lastModified=NULL)
    {
        $path = self::getFileName($type, $id, $name);
        if ($path === FALSE) {
            return FALSE;
        }

        if ($lastModified !== NULL) {
            $lastModified = strtotime($lastModified);
            if (filemtime($path) < $lastModified) {
                unlink($path);
                clearstatcache();
                return FALSE;
            }
        }

        $contents = file_get_contents($path);
        return $contents;
    }

    public static function set($type='', $id=0, $name='', $contents='')
    {
        // We must use integer's for the id's.
        if ((int)$id !== $id) {
            return FALSE;
        }

        // Make sure there are no '/' or '.' in the type.
        // Maybe someone is trying to do './path' or '../path' or '/path'.
        if (preg_match('!(/|\.)!', $type) > 0) {
            return FALSE;
        }

        // Also double check the file name.
        if (preg_match('!/!', $name) > 0) {
            return FALSE;
        }

        $path = self::_checkDir($type.'/'.$id);
        if ($path === FALSE) {
            $path = self::$_cacheDir.'/'.$type.'/'.$id;
            if (is_dir($path) === FALSE) {
                $result = mkdir($path, 0755, TRUE);
                if ($result === FALSE) {
                    return FALSE;
                }
            }
        }


        $file   = $path.'/'.$name;
        $result = file_put_contents($file, $contents);

        // it returns the number of bytes written,
        // but we don't care - we just want a true/false answer.
        if (is_int($result) === TRUE) {
            return TRUE;
        }
        return FALSE;
    }

}

/* vim: set expandtab ts=4 sw=4: */
