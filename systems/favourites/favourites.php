<?php
/**
 * Favourites class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The fave class.
 *
 * @package cms
 */
class favourites
{

    /**
     * Process an action for favourites.
     *
     * @param string $action The action to process.
     *
     * @return void
     */
    public static function process($action='')
    {

        switch ($action)
        {
            default:
                // Check if any images have been marked as fave's.
                $galleryImages = self::getGalleryImages();
                if (empty($galleryImages) === TRUE) {
                    template::serveTemplate('favourites.empty');
                    break;
                }

                // If there are some images marked as fave's,
                // this also checks if they are still available (and live).
                // If they aren't, it will return empty - hence the
                // extra check below.
                $gallery = self::printGalleryImages($galleryImages);

                if (empty($gallery) === TRUE) {
                    template::serveTemplate('favourites.empty');
                    break;
                }

                template::setKeyword('favourites', 'gallery', $gallery);
                template::serveTemplate('favourites');
        }
    }

    public static function getGalleryImages()
    {
        $sql  = "SELECT f.imagename, p.postid, p.subject, p.postdate";
        $sql .= " FROM ".db::getPrefix()."favourites f INNER JOIN ".db::getPrefix()."posts p";
        $sql .= " ON (f.postid=p.postid)";
        $sql .= " ORDER BY f.showorder ASC";

        $query   = db::select($sql);
        $results = db::fetchAll($query);

        return $results;

    }

    public static function printGalleryImages($galleryImages=array())
    {

        $dataDir = config::get('datadir');

        // We can just use the post system to generate the div contents.
        loadSystem('post');

        $postInfo = array();
        $files    = array();
        foreach ($galleryImages as $row => $info) {
            $postDir = $dataDir.'/post/'.$info['postid'];
            if (is_dir($postDir) === FALSE) {
                continue;
            }
            if (is_file($postDir.'/'.$info['imagename']) === FALSE) {
                continue;
            }

            $files[] = $postDir.'/'.$info['imagename'];

            $postInfo[] = array(
                'subject'  => $info['subject'],
                'postdate' => $info['postdate'],
                'postid'   => $info['postid'],
            );
        }

        $urls = post::getImageUrls($files);

        $code = '';

        if (empty($urls) === TRUE) {
            return $code;
        }

        $code = '
            <div id="galleria">
        ';
 
        foreach ($urls as $k => $url) {
            $postDate    = $postInfo[$k]['postdate'];
            $postSubject = $postInfo[$k]['subject'];


            $code .= '<a href="~url::baseurl~/post/'.post::safeUrl($postDate, $postSubject).'">';
            $code .= post::displayImage($url, $postInfo[$k]);
            $code .= '</a>';
        }

        $code .= '
            </div><!-- end gallery//-->
        ';

        return $code;
    }

}

/* vim: set expandtab ts=4 sw=4: */
