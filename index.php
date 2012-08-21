<?php
/**
 * Index file handles setting up a couple of base things.
 * Init sets up the rest.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package cms
 */

/**
 * Initialize the system.
 * Init handles everything we need apart from displaying
 * the front system.
 */

require dirname(__FILE__).'/systems/init.php';

frontend::display();

/* vim: set expandtab ts=4 sw=4: */
