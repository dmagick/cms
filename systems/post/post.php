<?php
/**
 * Post class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The post class.
 *
 * @package cms
 */
class post 
{

    /**
     * Cache images per post so we don't hit the f/s each time we
     * look at the same post.
     */
    private static $_imageCache = array();

    private static $_maxDimensions = array(
        'width'  => 900,
        'height' => 600,
    );

    /**
     * Get the latest posts from the database.
     *
     * @param integer $limit The number of posts to get. Defaults to 10.
     *
     * @return array Returns an array of the last number of posts.
     */
    public static function getPosts($limit=10)
    {
        $sql  = "SELECT p.postid, p.subject, p.content, p.postdate, p.modifieddate, u.username AS postbyuser";
        $sql .= " FROM ".db::getPrefix()."posts p INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (p.postby=u.userid)";
        $sql .= " ORDER BY postdate DESC LIMIT ".$limit;

        $query   = db::select($sql);
        $results = db::fetchAll($query);

        return $results;
    }

    /**
     * Get post content based on the date and subject passed in.
     *
     * This will be used if we are displaying a particular blog post.
     * We need both the date and the subject in case there are multiple
     * posts per day.
     *
     * @param string $postdate    The date to get the post for.
     * @param string $postsubject The subject to get the post for.
     *
     * @return array
     */
    public static function getPostByDate($postdate, $postsubject)
    {
        $sql  = "SELECT p.postid, p.subject, p.content, p.postdate, p.modifieddate, u.username AS postbyuser";
        $sql .= " FROM ".db::getPrefix()."posts p INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (p.postby=u.userid)";
        $sql .= " WHERE DATE(p.postdate) = :postdate AND p.subject=:postsubject";

        $query  = db::select($sql, array($postdate, urldecode($postsubject)));
        $result = db::fetch($query);

        return $result;
    }

    /**
     * Get the next and previous post based on the post id passed in.
     *
     * Returns an array of information about the next and previous:
     * - postid
     * - subject
     * - date
     * - whether it's previous or next
     *
     * @param integer $postid The post to get next/previous for.
     *
     * @return array
     */
    public static function getNextAndPrevPost($postid)
    {
        $sql     = "(";
        $sql    .= " SELECT p.postid, p.subject, p.postdate, 'previous' AS pos";
        $sql    .= " FROM ".db::getPrefix()."posts p";
        $sql    .= " WHERE p.postid < :postprev";
        $sql    .= " ORDER BY postid DESC LIMIT 1";
        $sql    .= ")";
        $sql    .= " UNION ALL ";
        $sql    .= "(";
        $sql    .= " SELECT p.postid, p.subject, p.postdate, 'next' AS pos";
        $sql    .= " FROM ".db::getPrefix()."posts p";
        $sql    .= " WHERE p.postid > :postnext";
        $sql    .= " ORDER BY postid ASC LIMIT 1";
        $sql    .= ")";
        $query   = db::select($sql, array($postid, $postid));
        $results = db::fetchAll($query);

        return $results;
    }

    /**
     * Return a safe url based on the post date and subject.
     *
     * @param string $postdate    The date of the post.
     * @param string $postsubject The subject of the post.
     *
     * @return string
     */
    public static function safeUrl($postdate, $postsubject)
    {
        if (is_numeric($postdate) === FALSE) {
            $postdate = strtotime($postdate);
        }
        $url = date('Y-m-d', $postdate).'/'.urlencode($postsubject);
        return $url;
    }

    /**
     * Process an action for the frontend.
     *
     * @param string $action The action to process.
     *
     * @return void
     */
    public static function process($action='')
    {

        switch ($action)
        {
            case '':
                Post::showLatestPost();
            break;

            default:
                if (strpos($action, '/') === FALSE) {
                    Post::showLatestPost();
                } else {
                    list($date, $subject) = explode('/', $action);

                    // If we're coming from the admin area, the url will be
                    // preview/$postid
                    // so our 'date' will be 'preview' and the 'subject'
                    // will be the postid.
                    if ($date === 'preview') {
                        $postid = $subject;
                        if (session::has('user') === TRUE) {
                            $post = self::getPostById($subject);
                            Post::showPost($post);
                            break;
                        }
                    }

                    $post = Post::getPostByDate($date, $subject);
                    if (empty($post) === FALSE) {
                        Post::showPost($post);
                    } else {
                        template::serveTemplate('post.invalid');
                    }
                }
        }
    }

    /**
     * Show the latest post.
     *
     * Works out the latest and passes it off to the showPost function.
     *
     * @return void
     */
    public static function showLatestPost()
    {
        $post  = array();
        $posts = Post::getPosts(1);
        if (empty($posts) === FALSE) {
            $post = array_shift($posts);
        }

        Post::showPost($post);
    }

    /**
     * Show a particular post.
     * 
     * Also works out the next and previous urls to show for the side nav bars.
     *
     * @param array $post The post info to show.
     *
     * @return void
     */
    public static function showPost($post=array())
    {
        if (empty($post) === TRUE) {
            template::serveTemplate('post.empty');
            return;
        }

        $post['gallery']  = post::getGallery($post);
        $post['postdate'] = niceDate($post['postdate']);
        $keywords = array(
            'content',
            'postbyuser',
            'postdate',
            'subject',
            'gallery',
        );

        $images = post::getImages($post);
        foreach ($images as $imagepos => $image) {
            $imageid                 = $imagepos + 1;
            $post['image:'.$imageid] = post::displayImage($image);
            $keywords[]              = 'image:'.$imageid;
        }

        foreach ($keywords as $keyword) {
            template::setKeyword('post.show', $keyword, $post[$keyword]);
        }

        template::setKeyword('header', 'pagetitle', $post['subject']);

        $nextpost = '';
        $prevpost = '';

        $nextandprev = Post::getNextAndPrevPost($post['postid']);
        foreach ($nextandprev as $otherPost) {
            $url = Post::safeUrl($otherPost['postdate'], $otherPost['subject']);
            if ($otherPost['pos'] === 'previous') {
                $prevpost = $url;
            } else {
                $nextpost = $url;
            }
        }

        template::setKeyword('post.next',     'nextpost',     $nextpost);
        template::setKeyword('post.previous', 'previouspost', $prevpost);

        template::serveTemplate('post.show');
    }

    public static function getImages($post=array())
    {
        if (empty($post) === TRUE) {
            return array();
        }

        if (isset($post['postid']) === FALSE || $post['postid'] <= 0) {
            return array();
        }

        if (empty(self::$_imageCache[$post['postid']]) === FALSE) {
            return self::$_imageCache[$post['postid']];
        }

        $dataDir = config::get('datadir');
        $path    = $dataDir.'/post/'.$post['postid'];

        if (is_dir($path) === FALSE) {
            return array();
        }

        $files = glob($path.'/*.jpg');
        if (empty($files) === TRUE) {
            return array();
        }

        natsort($files);

        $images = post::getImageUrls($files);

        self::$_imageCache[$post['postid']] = $images;

        return $images;
    }

    public static function getImageUrls($files=array())
    {
        $images  = array();
        if (empty($files) === TRUE) {
            return $images;
        }

        $dataDir = config::get('datadir');
        $dataUrl = url::getUrl().'/data';

        foreach ($files as $k => $file) {
            // Make sure it's an absolute path.
            if (realpath($file) !== $file) {
                continue;
            }

            // Make sure the path starts with the data dir.
            if (strpos($file, $dataDir) !== 0) {
                continue;
            }

            $info   = getimagesize($file);
            $width  = $info[0];
            $height = $info[1];

            if ($width > self::$_maxDimensions['width']) {
                $ratio = self::$_maxDimensions['width'] / $width;
                $width = self::$_maxDimensions['width'];
                $height = floor($height * $ratio);
            }

            $url        = str_replace($dataDir, $dataUrl, $file);
            $images[$k] = array(
                'url'    => $url,
                'width'  => $width,
                'height' => $height,
            );
        }

        return $images;

    }

    public static function displayImage($image=array(), $ownGallery=TRUE)
    {
        if ($ownGallery === TRUE) {
            $code  = '<div id="gallery">';
        } else {
            $code  = '<div>';
        }

        $code .= '<img src="'.$image['url'].'" width="'.$image['width'].'" height="'.$image['height'].'" />';
        $code .= '</div>';
        return $code;
    }

    public static function getGallery($post=array())
    {

        if (empty($post) === TRUE) {
            return '';
        }

        $images = post::getImages($post);

        if (empty($images) === TRUE) {
            return '';
        }

        $code = '';
        foreach ($images as $image) {
            $code .= self::displayImage($image);
        }

        return $code;
    }

    /**
     * Get a post by id. We need this when we are previewing a post from the admin
     * area.
     * Since we don't know whether it's under construction or live, we check both
     * the queue table and the posts table for the id.
     */
    private static function getPostById($postid=0)
    {
        $sql  = "SELECT postid, subject, content, postdate, modifieddate, postbyuser";
        $sql .= " FROM ";
        $sql .= "(";
        $sql .= "(";
        $sql .= " SELECT p.postid, p.subject, p.content, p.postdate, p.modifieddate, u.username AS postbyuser";
        $sql .= " FROM ".db::getPrefix()."posts p INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (p.postby=u.userid)";
        $sql .= " WHERE p.postid=:livepostid";
        $sql .= ")";
        $sql .= " UNION ALL ";
        $sql .= "(";
        $sql .= " SELECT q.postid, q.subject, q.content, q.postdate, q.modifieddate, u.username AS postbyuser";
        $sql .= " FROM ".db::getPrefix()."posts_queue q INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (q.postby=u.userid)";
        $sql .= " WHERE q.postid=:ucpostid";
        $sql .= ")";
        $sql .= ") as postlist";
        $sql .= " WHERE postid=:postid";

        $query   = db::select($sql, array(':livepostid' => $postid, ':ucpostid' => $postid, ':postid' => $postid));
        $results = db::fetchAll($query);

        $entry = array_shift($results);
        return $entry;
    }

}

/* vim: set expandtab ts=4 sw=4: */
