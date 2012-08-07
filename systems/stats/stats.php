<?php
/**
 * Stats class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The stats class.
 * Logs hits into the db.
 *
 * @package cms
 */
class stats
{

    /**
     * Records a page hit into the database.
     *
     * @param float $timetaken   The time taken to generate/display the page.
     * @param array $querycounts The number of queries taken to generate the page.
     *                           This includes the unique number and total number.
     *
     * @return void
     */
    public static function recordHit($timetaken=0, $querycounts=array())
    {
        $sql  = "INSERT INTO ".db::getPrefix()."stats";
        $sql .= "(ip, url, referer, logtime, timetaken, querytotal, queryunique)";
        $sql .= " VALUES ";
        $sql .= "(:ip, :url, :referer, NOW(), :timetaken, :querytotal, :queryunique)";

        $url = '';
        if (isset($_SERVER['REQUEST_URI']) === TRUE) {
            $url = $_SERVER['REQUEST_URI'];
        }

        if (isset($querycounts['total']) === FALSE) {
            $querycounts['total'] = -1;
        }

        if (isset($querycounts['unique']) === FALSE) {
            $querycounts['unique'] = -1;
        }

        $referer = '';
        if (isset($_SERVER['REDIRECT_URL']) === TRUE) {
            $referer = $_SERVER['REDIRECT_URL'];
        }

        $values = array(
            ':ip'          => getIp(),
            ':url'         => $url,
            ':referer'     => $referer,
            ':timetaken'   => number_format($timetaken, 8),
            ':querytotal'  => $querycounts['total'],
            ':queryunique' => $querycounts['unique'],
        );

        db::execute($sql, $values);
    }

}

/* vim: set expandtab ts=4 sw=4: */
