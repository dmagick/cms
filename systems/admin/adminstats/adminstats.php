<?php
/**
 * Admin stats class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * The admin stats class.
 * Shows basic stats on posts.
 *
 * @package cms
 */
class adminstats
{

    /**
     * Process an action for the backend.
     *
     * @param string $action The action to process.
     *                       In this class, the action is ignored.
     *                       There is only one action - which is to list.
     *
     * @return void
     */
    public static function process($action='')
    {
        self::_listStats();
    }

    private static function _listStats()
    {
        $startTime = date('Y-m-d 00:00:00', strtotime('-2 days'));

        $stats = self::_getStats($startTime);

        if (empty($stats) === TRUE) {
            template::serveTemplate('stats.empty');
            return;
        }

        template::serveTemplate('stats.header');

        template::setKeyword('stats.header', 'starttime', niceDate($startTime));

        // Postgres lowercases the field names (aliases), so do the same.
        $keywords = array(
            'url',
            'hitcount',
            'avgtimetaken',
            'avgqueries',
            'avguniquequeries',
        );

        // Round these fields before displaying.
        $roundFields = array(
            'avgtimetaken',
            'avgqueries',
            'avguniquequeries',
        );

        foreach ($stats as $k => $details) {
            foreach ($keywords as $keyword) {
                if (in_array($keyword, $roundFields) === TRUE) {
                    $details[$keyword] = round($details[$keyword], 4);
                }
                template::setKeyword('stats.details', $keyword, $details[$keyword]);
            }
            template::serveTemplate('stats.details');
        }

        template::serveTemplate('stats.footer');
    }

    private static function _getStats($startTime)
    {
        $sql =  "SELECT";
        $sql .= " url,";
        $sql .= " COUNT(statid) AS hitcount,";
        $sql .= " SUM(timetaken) / COUNT(statid) AS avgTimeTaken,";
        $sql .= " SUM(querytotal) / COUNT(statid) AS avgQueries,";
        $sql .= " SUM(queryunique) / COUNT(statid) AS avgUniqueQueries";
        $sql .= " FROM ".db::getPrefix()."stats";
        $sql .= " WHERE";
        $sql .= " logtime >= :startTime";
        $sql .= " AND url NOT LIKE '/admin%'";
        $sql .= " GROUP BY url";
        $sql .= " ORDER BY hitcount DESC";

        $query   = db::select($sql, array(':startTime' => $startTime));
        $results = db::fetchAll($query);

        return $results;
    }

}

/* vim: set expandtab ts=4 sw=4: */
