<?php

/**
 * A quick script to regenerate the javascript (with full urls) for displaying the gallery.
 *
 * Uses the url from the config file as the prefix.
 */

$basedir = dirname(dirname(__FILE__));
require $basedir.'/systems/init.php';

$files = findFiles($basedir.'/gallery', '.jpg');
natsort($files);
$code  = <<<EOT
            \$(document).ready(function() {
                \$(".fancybox").fancybox();
                \$("#show-gallery").click(function() {
                    \$.fancybox.open([
EOT;

$images = '';
foreach ($files as $file) {
    $url = str_replace($basedir, $config['url'], $file);
    $images .= <<<EOT
                        {
                            href  : '${url}',
                            title : 'My title',
                        },

EOT;
}
$images = rtrim($images);
$images = rtrim($images, ',')."\n";

$code .= $images;

$code .= <<<EOT
                        ],
                        {
                        helpers : {
                            title : {
                                type: 'over'
                            },
                            thumbs : {
                                width: 75,
                                height: 50
                            },
                            overlay	: {
                                opacity: 0.8
                            }
                        },
                        prevEffect	: 'fade',
                        nextEffect	: 'fade'
                    });
                });

            });
EOT;

file_put_contents($basedir.'/web/js/gallery.js', $code);

/**
 * A recursive function to find files based on the filename passed in.
 * The filename passed in is used as a regex to see if it matches.
 *
 * @param string $path      The path to search
 * @param string $filename  The filename to search the path for. This is
 *                          used as a regex to check if it matches.
 * @param array $ignoreDirs An array of directories to ignore (or not searched).
 *
 * @return array Returns an array of (full path) filenames that meet the
 *               criteria.
 */
function findFiles($path, $filename, $ignoreDirs=array())
{
    if (is_dir($path) === FALSE) {
        return array();
    }

    $found = array();
    if ($handle = opendir($path)) {
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullpath = $path."/".$file;
            if (is_dir($fullpath) === TRUE) {
                if (in_array($fullpath, $ignoreDirs) === FALSE) {
                    $subFound = findFiles($fullpath, $filename, $ignoreDirs);
                    $found    = array_merge($found, $subFound);
                }
                continue;
            }
            if (is_file($fullpath) === TRUE) {
                if (preg_match('/'.preg_quote($filename).'/', $fullpath) == 1) {
                    $found[] = $fullpath;
                }
            }
        }
        closedir($handle);
    }
    return $found;
}


