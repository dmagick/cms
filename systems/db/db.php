<?php
/**
 * DB class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The db class.
 * Basically a giant wrapper for PDO.
 * Also handles logging (through messagelog).
 *
 * @package cms
 */
class db
{

    /**
     * Query log.
     */
    private static $_queries = array(
        'total' => 0,
    );

    /**
     * Keep the db connection handle.
     */
    private static $_dbconn = NULL;

    /**
     * A table prefix - so we can have a shared database
     * with other products and not have them interfere.
     */
    private static $_tablePrefix = '';

    /**
     * Try to connect to the db based on the details passed in.
     * The details contain:
     * type (pgsql, mysql etc)
     * dbname
     * username
     * password
     * table prefix
     *
     * Throws an exception if a connection can't be established.
     *
     * @return TRUE
     * @throws exception Throws an exception if a connection can't be
     *                   established.
     */
    public static function connect(array $details)
    {
        $required = array(
                'dbname',
                'type',
                'username',
                );

        $connstring = '';
        foreach ($required as $reqField) {
            if (isset($details[$reqField]) === FALSE) {
                throw new Exception("Unable to connect to db: Required field ".$reqField." is missing");
            }
            if (empty($details[$reqField]) === TRUE) {
                throw new Exception("Unable to connect to db: Required field ".$reqField." is empty");
            }
        }

        $connstring .= $details['type'].':dbname='.$details['dbname'].';';
        if (isset($details['host']) === TRUE && empty($details['host']) === FALSE) {
            $connstring .= 'host='.$details['host'].';';
        }
        if (isset($details['port']) === TRUE && empty($details['port']) === FALSE) {
            if ($details['port'] > 0) {
                $connstring .= 'port='.$details['port'].';';
            }
        }

        try {
            $extra  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
            $dbconn = new PDO($connstring, $details['username'], $details['password'], $extra);
        } catch (PDOException $e) {
            throw new Exception("Unable to connect to db: ".$e->getMessage());
        }
        self::$_dbconn = $dbconn;

        if (isset($details['prefix']) === FALSE) {
            $details['prefix'] = '';
        }
        self::$_tablePrefix = $details['prefix'];

        return TRUE;
    }

    /**
     * Disconnect the database connection if it was connected before.
     *
     * @return void
     */
    public static function disconnect()
    {
        if (self::$_dbconn !== NULL) {
            self::$_dbconn = NULL;
        }
    }

    /**
     * Return the table prefix.
     *
     * @return string The table prefix previously set.
     */
    public static function getPrefix()
    {
        return self::$_tablePrefix;
    }

    /**
     * Begin a transaction.
     *
     * Also logs a message.
     *
     * @return mixed Returns the result of PDO::beginTransaction()
     */
    public static function beginTransaction()
    {
        self::_logQuery("BEGIN;");
        $result = self::$_dbconn->beginTransaction();
        return $result;
    }

    /**
     * Commits a transaction.
     *
     * Also logs a message.
     *
     * @return mixed Returns the result of PDO::commit()
     */
    public static function commitTransaction()
    {
        self::_logQuery("COMMIT;");
        $result = self::$_dbconn->commit();
        return $result;
    }

    /**
     * Roll back a transaction.
     *
     * Also logs a message.
     *
     * @return mixed Returns the result of PDO::rollback()
     */
    public static function rollbackTransaction()
    {
        self::_logQuery("ROLLBACK;");
        $result = self::$_dbconn->rollback();
        return $result;
    }

    /**
     * Execute sql passed in.
     *
     * You write your own sql and pass it through here,
     * along with any values to pass in to the sql.
     * This runs the query and returns whether it worked or not.
     *
     * @param string $sql    The sql to run.
     * @param array  $values Bind-values to pass in to the sql
     *
     * @return boolean Returns whether the query worked or not.
     */
    public static function execute($sql, array $values=array())
    {
        try {
            $query = self::$_dbconn->prepare($sql);
            if (empty($values) === TRUE) {
                $result = $query->execute();
            } else {
                $result = $query->execute($values);
            }

            if ($result !== TRUE) {
                $msg  = 'Unable to execute sql:'.$sql."\n";
                $msg .= 'Info:'.var_export($query->errorInfo(), TRUE);
                messageLog::LogMessage($msg);
                return FALSE;
            }

            $rowsAffected = $query->rowCount();
            return $rowsAffected;
        } catch (PDOException $e) {
            $msg  = 'Unable to execute sql:'.$sql."\n";
            $msg .= 'Info:'.var_export($query->errorInfo(), TRUE);
            messageLog::LogMessage($msg);
            return FALSE;
        }
    }

    /**
     * select based on the sql passed in.
     *
     * You write your own sql and pass it through here,
     * along with any values to pass in to the sql.
     * This runs the query and returns a statement handler for use
     * with the fetch() method.
     *
     * @param string $sql    The sql to run.
     * @param array  $values Bind-values to pass in to the sql
     *
     * @return object Returns a PDOStatement object, you have to use
     *                fetch() or fetchAll() to read the results.
     *
     * @see  db::fetch
     * @see  db::fetchAll
     */
    public static function select($sql, array $values=array())
    {
        self::_logQuery($sql, $values);
        $query = self::$_dbconn->prepare($sql);
        if (empty($values) === TRUE) {
            $result = $query->execute();
        } else {
            $result = $query->execute($values);
        }

        if ($result !== TRUE) {
            $msg  = 'Unable to execute sql:'.$sql."\n";
            $msg .= 'Info:'.var_export($query->errorInfo(), TRUE);
            messageLog::LogMessage($msg);
        }

        return $query;
    }

    /**
     * Fetch the next row from a select query.
     *
     * Simply a wrapper for PDOStatement::fetch
     * Sets the row to be returned as an associative array.
     *
     * @param object $queryObject Query object from a previous select() call.
     *
     * @return mixed Returns the results from PDOStatement::fetch
     */
    public static function fetch($queryObject)
    {
        return $queryObject->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows from a select query.
     *
     * Simply a wrapper for PDOStatement::fetchAll
     * Sets the rows to be returned as an associative array.
     *
     * @param object $queryObject Query object from a previous select() call.
     *
     * @return mixed Returns the results from PDOStatement::fetchAll
     */
    public static function fetchAll($queryObject)
    {
        return $queryObject->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Log a query to the message log system and also
     * keep a local cache of the queries (and their count).
     */
    private static function _logQuery($sql, $values=array())
    {
        messagelog::LogMessage($sql, 'debug');
        if (empty($values) === FALSE) {
            messagelog::LogMessage($values, 'debug');
        }

        $sql  = preg_replace('/\s+/', ' ', $sql);
        $hash = md5($sql);
        if (isset(self::$_queries[$hash]) === FALSE) {
            self::$_queries[$hash] = array(
                'count' => 0,
            );
        }
        self::$_queries[$hash]['count']++;
        self::$_queries['total']++;
    }

    /**
     * Return the number of queries run (unique and total).
     *
     * @return array
     */
    public static function getQueryCount()
    {
        // Take 1 off for the unique count, because
        // 'total' is also kept in the _queries array.
        $info = array(
            'total' => self::$_queries['total'],
            'unique' => sizeof(self::$_queries) - 1,
        );
        return $info;
    }

}

/* vim: set expandtab ts=4 sw=4: */
