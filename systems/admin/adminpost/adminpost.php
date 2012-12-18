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

        if (strpos($action, '/') !== FALSE) {
            $bits   = explode('/', $action);
            $action = array_shift($bits);
            $info   = implode('/', $bits);
        }

        switch ($action)
        {
            case 'update':
                template::clearStack();
                $result = self::_updatePost($info);
                if ($result === TRUE) {
                    echo 'Post updated';
                } else {
                    echo 'Something went wrong';
                }
                exit;
                break;

            case '':
                self::_listPosts();
            break;

        }
    }

    private static function _updatePost($postid)
    {
        db::beginTransaction();

        $status = $_POST['status'];
        $content = $_POST['content'];
        $subject = $_POST['subject'];

        $sqlUpdate  = 'UPDATE ~tablename~ SET';
        $sqlUpdate .= ' subject=:subject,';
        $sqlUpdate .= ' content=:content,';
        $sqlUpdate .= ' modifieddate=NOW()';
        $sqlUpdate .= ' WHERE ';
        $sqlUpdate .= ' postid=:postid';

        $updateData = array(
            ':subject' => $subject,
            ':content' => $content,
            ':postid'  => $postid,
        );

        $sqlInsert  = 'INSERT INTO ~tablename~';
        $sqlInsert .= '(postid, subject, content, postdate, modifieddate, postby)';
        $sqlInsert .= ' SELECT postid, :subject, :content, postdate, NOW(), postby ';
        $sqlInsert .= ' FROM ~othertablename~';
        $sqlInsert .= ' WHERE postid=:postid';

        $insertData = array(
            ':subject' => $subject,
            ':content' => $content,
            ':postid'  => $postid,
        );

        $sqlDelete  = 'DELETE FROM ~tablename~ WHERE postid=:postid';

        $deleteData = array(
            ':postid' => $postid,
        );

        switch ($status)
        {
            case 'live':
                $updateTable  = 'posts';
                $insertTable1 = 'posts';
                $insertTable2 = 'posts_queue';
                $deleteTable  = 'posts_queue';
                break;

            case 'uc':
                $updateTable  = 'posts_queue';
                $insertTable1 = 'posts_queue';
                $insertTable2 = 'posts';
                $deleteTable  = 'posts';
                break;
        }

        $updated = db::execute(
            str_replace('~tablename~', db::getPrefix().$updateTable, $sqlUpdate),
            $updateData
        );

        if ($updated == 0) {
            $inserted = db::execute(
                str_replace(
                    array(
                        '~tablename~',
                        '~othertablename~',
                    ),
                    array(
                        db::getPrefix().$insertTable1,
                        db::getPrefix().$insertTable2,
                    ),
                    $sqlInsert
                ),
                $insertData
            );
            if ($inserted == 0) {
                messageLog::LogMessage('Unable to move from '.$insertTable1.' to '.$insertTable2);
                db::rollbackTransaction();
                return FALSE;
            }

            $deleted = db::execute(
                str_replace('~tablename~', db::getPrefix().$deleteTable, $sqlDelete),
                $deleteData
            );

            if ($deleted == 0) {
                messageLog::LogMessage('Unable to delete from '.$deleteTable);
                db::rollbackTransaction();
                return FALSE;
            }
        }
        db::commitTransaction();
        return TRUE;
    }

    private static function _getPosts($limit=10)
    {
        $sql  = "SELECT postid, subject, content, postdate, postedby, status";
        $sql .= " FROM ";
        $sql .= "(";
        $sql .= "(";
        $sql .= " SELECT p.postid, p.subject, p.content, p.postdate, u.username AS postedby, 'live' as status";
        $sql .= " FROM ".db::getPrefix()."posts p INNER JOIN ".db::getPrefix()."users u";
        $sql .= " ON (p.postby=u.userid)";
        $sql .= " ORDER BY postdate DESC LIMIT ".$limit;
        $sql .= ")";
        $sql .= " UNION ALL ";
        $sql .= "(";
        $sql .= "SELECT q.postid, q.subject, q.content, q.postdate, u.username AS postedby, 'uc' as status";
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
            $details['content']  = htmlspecialchars($details['content']);

            $details['livechecked'] = '';
            $details['ucchecked']   = '';
            if ($details['status'] === 'uc') {
                $details['ucchecked'] = ' CHECKED';
            }
            if ($details['status'] === 'live') {
                $details['livechecked'] = ' CHECKED';
            }

            $keywords = array(
                'postid',
                'postedby',
                'postdate',
                'status',
                'subject',
                'content',
                'livechecked',
                'ucchecked',
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
