<?php
/**
 * Admin comment class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The admin comment class.
 * Deals with moderating comments.
 *
 * @package cms
 */
class admincomments
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

        messageLog::setLogLevel('debug');

        if (strpos($action, '/') !== FALSE) {
            $bits   = explode('/', $action);
            $action = array_shift($bits);
            $info   = implode('/', $bits);
        }

        // The empty action means 'list'.
        // Anything else is an ajax request, so
        // we need to clear the stack.
        if (empty($action) === FALSE) {
            template::clearStack();
        }

        switch ($action)
        {
            case 'delete':
                $result = self::_deleteComment($info);
                if ($result === TRUE) {
                    session::setFlashMessage('Comment deleted', 'success');
                } else {
                    session::setFlashMessage('Comment not deleted', 'error');
                }
                exit;
                break;

            case 'update':
                $result = self::_updateComment($info);
                if ($result === TRUE) {
                    echo 'Comment updated';
                } else {
                    echo 'Something went wrong';
                }
                exit;
                break;

            default:
                self::_listComments();
            break;

        }
    }

    private static function _deleteComment($commentid)
    {
        db::beginTransaction();

        $sqlDelete  = 'DELETE FROM ~tablename~ WHERE commentid=:commentid';
        $deleteData = array(
            ':commentid' => $commentid,
        );

        $deleted = 0;
        foreach (array('comments', 'comments_queue') AS $table) {
            $deleted += db::execute(
                str_replace('~tablename~', db::getPrefix().$table, $sqlDelete),
                $deleteData
            );
        }

        if ($deleted === 0) {
            db::rollbackTransaction();
            return FALSE;
        }

        db::commitTransaction();
        return TRUE;
    }

    private static function _updateComment($commentid)
    {
        db::beginTransaction();

        messageLog::logMessage('commentid:'.$commentid);
        messageLog::logMessage('post:'.print_r($_POST, true));

        $status       = $_POST['status'];
        $content      = $_POST['content'];
        $commentby    = $_POST['commentby'];
        $commentemail = $_POST['commentemail'];

        $sqlUpdate  = 'UPDATE ~tablename~ SET';
        $sqlUpdate .= ' commentby=:commentby,';
        $sqlUpdate .= ' commentemail=:commentemail,';
        $sqlUpdate .= ' content=:content,';
        $sqlUpdate .= ' modifieddate=NOW()';
        $sqlUpdate .= ' WHERE ';
        $sqlUpdate .= ' commentid=:commentid';

        $updateData = array(
            ':commentby'    => $commentby,
            ':commentemail' => $commentemail,
            ':content'      => $content,
            ':commentid'    => $commentid,
        );

        $sqlInsert  = 'INSERT INTO ~tablename~';
        $sqlInsert .= '(commentid, content, commentemail, commentby, commentdate, modifieddate, postid)';
        $sqlInsert .= ' SELECT commentid, :content, :commentemail, :commentby, commentdate, NOW(), postid';
        $sqlInsert .= ' FROM ~othertablename~';
        $sqlInsert .= ' WHERE commentid=:commentid';

        $insertData = array(
            ':commentby'    => $commentby,
            ':commentemail' => $commentemail,
            ':content'      => $content,
            ':commentid'    => $commentid,
        );

        $sqlDelete  = 'DELETE FROM ~tablename~ WHERE commentid=:commentid';

        $deleteData = array(
            ':commentid' => $commentid,
        );

        switch ($status)
        {
            case 'live':
                $updateTable  = 'comments';
                $insertTable1 = 'comments';
                $insertTable2 = 'comments_queue';
                $deleteTable  = 'comments_queue';
                break;

            case 'uc':
                $updateTable  = 'comments_queue';
                $insertTable1 = 'comments_queue';
                $insertTable2 = 'comments';
                $deleteTable  = 'comments';
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

    private static function _getComments($limit=10)
    {
        $sql  = "SELECT commentid, content, commentemail, commentby, commentdate, postid, status";
        $sql .= " FROM ";
        $sql .= "(";
        $sql .= "(";
        $sql .= " SELECT c.commentid, c.content, c.commentemail, c.commentby, c.commentdate, c.postid, 'live' as status";
        $sql .= " FROM ".db::getPrefix()."comments c INNER JOIN ".db::getPrefix()."posts p";
        $sql .= " ON (c.postid=p.postid)";
        $sql .= " ORDER BY commentid DESC LIMIT ".$limit;
        $sql .= ")";
        $sql .= " UNION ALL ";
        $sql .= "(";
        $sql .= " SELECT q.commentid, q.content, q.commentemail, q.commentby, q.commentdate, q.postid, 'uc' as status";
        $sql .= " FROM ".db::getPrefix()."comments_queue q INNER JOIN ".db::getPrefix()."posts p";
        $sql .= " ON (q.postid=p.postid)";
        $sql .= " ORDER BY commentid DESC LIMIT ".$limit;
        $sql .= ")";
        $sql .= ") as commentlist";
        $sql .= " ORDER BY commentid DESC LIMIT ".$limit;

        $query   = db::select($sql);
        $results = db::fetchAll($query);

        return $results;
    }

    private static function _listComments()
    {
        $list  = self::_getComments();

        if (empty($list) === TRUE) {
            template::serveTemplate('comment.list.empty');
            return;
        }

        template::serveTemplate('comment.list.header');

        foreach ($list as $k => $details) {
            $details['commentdate'] = niceDate($details['commentdate']);
            $details['content']  = htmlspecialchars(stripslashes($details['content']));

            $details['livechecked'] = '';
            $details['ucchecked']   = '';
            if ($details['status'] === 'uc') {
                $details['ucchecked'] = ' CHECKED';
            }
            if ($details['status'] === 'live') {
                $details['livechecked'] = ' CHECKED';
            }

            $keywords = array(
                'commentid',
                'commentemail',
                'commentby',
                'commentdate',
                'status',
                'content',
                'livechecked',
                'ucchecked',
                'postid',
            );

            foreach ($keywords as $keyword) {
                template::setKeyword('comment.list.details', $keyword, $details[$keyword]);
            }
            template::serveTemplate('comment.list.details');
        }

        template::serveTemplate('comment.list.footer');
    }

}

/* vim: set expandtab ts=4 sw=4: */
