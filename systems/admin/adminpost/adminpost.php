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

        // The empty action means 'list'.
        // Anything else is an ajax request, so
        // we need to clear the stack.
        if (empty($action) === FALSE) {
            template::clearStack();
        }

        switch ($action)
        {
            case 'delete':
                $result = self::_deletePost($info);
                if ($result === TRUE) {
                    session::setFlashMessage('Post deleted', 'success');
                } else {
                    session::setFlashMessage('Post not deleted', 'error');
                }
                exit;
                break;

            case 'add':
                $result = self::_addPost();
                if ($result === TRUE) {
                    session::setFlashMessage('Post added', 'success');
                } else {
                    session::setFlashMessage('Post not added', 'error');
                }
                exit;
                break;

            case 'update':
                $result = self::_updatePost($info);
                if ($result === TRUE) {
                    echo 'Post updated';
                } else {
                    echo 'Something went wrong';
                }
                exit;
                break;

            case 'favedelete':
                $result = self::_faveDelete($info);
                if ($result === TRUE) {
                    echo 'Favourite deleted';
                } else {
                    echo 'Something went wrong';
                }
                exit;
                break;

            case 'faveadd':
                $result = self::_faveAdd($info);
                if ($result === TRUE) {
                    echo 'Favourite added';
                } else {
                    echo 'Something went wrong';
                }
                exit;
                break;

            default:
                self::_listPosts();
            break;

        }
    }

    private static function _deletePost($postid)
    {
        db::beginTransaction();

        $sqlDelete  = 'DELETE FROM ~tablename~ WHERE postid=:postid';
        $deleteData = array(
            ':postid' => $postid,
        );

        $deleted = 0;
        foreach (array('posts', 'posts_queue') AS $table) {
            $deleted += db::execute(
                str_replace('~tablename~', db::getPrefix().$table, $sqlDelete),
                $deleteData
            );
        }

        if ($deleted === 0) {
            db::rollbackTransaction();
            return FALSE;
        }

        // Clean up images from the data directories as well.
        $dataDir = config::get('datadir');
        $path    = $dataDir.'/post/'.$postid;

        if (is_dir($path) === TRUE) {
            $files = glob($path.'/*.jpg');
            if (empty($files) === FALSE) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            rmdir($path);
        }

        db::commitTransaction();
        return TRUE;
    }

    private static function _addPost()
    {
        db::beginTransaction();

        $postby  = session::get('user');
        $content = $_POST['content'];
        $subject = $_POST['subject'];

        $postdate = date('Y-m-d H:i:0');
        if (empty($_POST['postdate']) === FALSE) {
            $postdate = $_POST['postdate'];
        }

        $postidQuery = db::select("SELECT nextval('".db::getPrefix()."posts_postid') AS postid");
        $postidRow   = db::fetch($postidQuery);
        $postid      = $postidRow['postid'];

        $sqlInsert  = 'INSERT INTO '.db::getPrefix().'posts_queue';
        $sqlInsert .= ' (postid, subject, content, postdate, modifieddate, postby)';
        $sqlInsert .= ' VALUES ';
        $sqlInsert .= ' (:postid, :subject, :content, :postdate, NOW(), :postby)';

        $insertData = array(
            ':subject'  => $subject,
            ':content'  => $content,
            ':postdate' => $postdate,
            ':postid'   => $postid,
            ':postby'   => $postby,
        );

        $inserted = db::execute($sqlInsert, $insertData);

        if ($inserted === 0) {
            db::rollbackTransaction();
            return FALSE;
        }

        db::commitTransaction();
        return TRUE;
    }

    private static function _updatePost($postid)
    {
        db::beginTransaction();

        $status  = $_POST['status'];
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

        $list  = self::_getPosts();
        $faves = self::_getFavourites();

        if (empty($list) === TRUE) {
            template::serveTemplate('post.list.empty');
            return;
        }

        loadSystem('post');

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

            $images    = post::getImages($details);
            $imageList = '';
            $imgCount  = 1;

            // Don't use the key of the array as the image counter.
            // natsort() (used in post::getImages()) doesn't reset
            // the keys, so you end up with an array like
            // 0 = 1.jpg
            // 9 = 10.jpg
            // 2 = 2.jpg
            // ...
            // so your display would be
            // 1 = 1.jpg
            // 10 = 2.jpg
            // 11 = 3.jpg
            // ..
            // 2 = 10.jpg
            foreach ($images as $imageInfo) {
                $imageName = substr(strrchr($imageInfo['url'], '/'), 1);

                $icon         = 'nofave';
                $iconOpposite = 'fave';
                if (isset($faves[$details['postid']]) === TRUE) {
                    if (in_array($imageName, $faves[$details['postid']]) === TRUE) {
                        $icon         = 'fave';
                        $iconOpposite = 'nofave';
                    }
                }

                $imageList .= '<img class="fave-'.$icon.'" id="'.$details['postid'].'~'.htmlentities($imageName).'" src="~url::baseurl~/web/images/admin/fave-'.$icon.'.png" border="0" />';
                $imageList .= $imgCount.'.&nbsp;';
                $imageList .= '<a href="'.$imageInfo['url'].'" target="_blank">';
                $imageList .= $imageName;
                $imageList .= '</a>';
                $imageList .= '<br/>';
                $imgCount++;
            }
            $imageList = rtrim($imageList, '<br/>');

            $details['imagelist'] = $imageList;

            $keywords = array(
                'imagelist',
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

    private function _getFavourites()
    {
        $sql     = 'SELECT postid, imagename FROM '.db::getPrefix().'favourites';
        $query   = db::select($sql);
        $results = db::fetchAll($query);

        $favourites = array();
        foreach ($results as $k => $info) {
            $postid = $info['postid'];
            if (isset($favourites[$postid]) === FALSE) {
                $favourites[$postid] = array();
            }
            $favourites[$postid][] = $info['imagename'];
        }

        return $favourites;
    }

    private function _faveAdd($info)
    {
        if (strpos($info, '~') === FALSE) {
            return FALSE;
        }

        list($postid, $image) = explode('~', $info);

        db::beginTransaction();

        $query  = 'INSERT INTO '.db::getPrefix().'favourites (postid, imagename)';
        $query .= ' VALUES ';
        $query .= '(:postid, :imagename)';

        $bindVars = array(
            ':postid'    => $postid,
            ':imagename' => $image,
        );

        $result = db::execute($query, $bindVars);

        if ($result === 0) {
            db::rollbackTransaction();
            return FALSE;
        }

        db::commitTransaction();
        return TRUE;
    }

    private function _faveDelete($info)
    {
        if (strpos($info, '~') === FALSE) {
            return FALSE;
        }

        list($postid, $image) = explode('~', $info);

        db::beginTransaction();

        $query  = 'DELETE FROM '.db::getPrefix().'favourites';
        $query .= ' WHERE postid=:postid AND imagename=:imagename';

        $bindVars = array(
            ':postid'    => $postid,
            ':imagename' => $image,
        );

        $result = db::execute($query, $bindVars);

        if ($result === 0) {
            db::rollbackTransaction();
            return FALSE;
        }

        db::commitTransaction();
        return TRUE;
    }

}

/* vim: set expandtab ts=4 sw=4: */
