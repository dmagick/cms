<?php
/**
 * Admin post class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The admin post class.
 * Deals with creating, editing and deleting posts.
 *
 * @package cms
 */
class adminpost
{

    /**
     * Process an action for the backend.
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
                self::_listPosts();
            break;

        }
    }

    private static function _getPosts($limit=10)
    {
        $sql  = "SELECT postid, subject, postdate, postedby, status";
        $sql .= " FROM ";
        $sql .= "(";
        $sql .= "(";
        $sql .= " SELECT p.postid, p.subject, p.postdate, u.username AS postedby, 'live' as status";
        $sql .= " FROM ".db::getPrefix()."posts p INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (p.postby=u.userid)";
        $sql .= " ORDER BY postdate DESC LIMIT ".$limit;
        $sql .= ")";
        $sql .= " UNION ALL ";
        $sql .= "(";
        $sql .= "SELECT q.postid, q.subject, q.postdate, u.username AS postedby, 'uc' as status";
        $sql .= " FROM ".db::getPrefix()."posts_queue q INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (q.postby=u.userid)";
        $sql .= " ORDER BY postdate DESC LIMIT ".$limit;
        $sql .= ")";
        $sql .= ") as postlist";
        $sql .= " ORDER BY postdate DESC LIMIT ".$limit;

        $query   = db::select($sql);
        $results = db::fetchAll($query);

        return $results;
    }

    private static function _listPosts()
    {
        template::serveTemplate('post.list.new');

        $list = self::_getPosts();

        if (empty($list) === TRUE) {
            template::serveTemplate('post.list.empty');
            return;
        }

        template::serveTemplate('post.list.header');
        foreach ($list as $k => $details) {
            $details['postdate'] = niceDate($details['postdate']);
            $keywords = array(
                'postid',
                'postedby',
                'postdate',
                'status',
                'subject',
            );

            foreach ($keywords as $keyword) {
                template::setKeyword('post.list.details', $keyword, $details[$keyword]);
            }
            template::serveTemplate('post.list.details');
        }

        template::serveTemplate('post.list.footer');
    }

}

/* vim: set expandtab ts=4 sw=4: */
