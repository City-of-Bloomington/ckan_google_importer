<?php
/**
 * Where on the filesystem this application is installed
 */
define('APPLICATION_HOME', __DIR__);
define('BASE_URI', '/ckan_google_importer');

//-------------------------------------------------------------------
// Bootstrap code
// No editing is usually needed after this point
//-------------------------------------------------------------------
/**
 * Enable autoloading for the PHP libraries
 */
$loader = require APPLICATION_HOME.'/vendor/autoload.php';
