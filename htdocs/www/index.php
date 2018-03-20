<?php
define('DS', DIRECTORY_SEPARATOR);
define('DOC_ROOT', dirname(__FILE__).DS);
define('BASE_URL', dirname($_SERVER['SCRIPT_NAME']).DS);
ob_start();
is_file('../bootstrap.php') ? require('../bootstrap.php') : die('boot fail');
is_file('../process.php') ? require('../process.php') : die('process fail');

