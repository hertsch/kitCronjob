<?php

/**
 * kitCronjob
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitCronjob
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

$PRECHECK['WB_VERSION'] = array('VERSION' => '2.8', 'OPERATOR' => '>=');
$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.2.0', 'OPERATOR' => '>=');
$PRECHECK['WB_ADDONS'] = array(
    'manufaktur_config'	=> array('VERSION' => '0.14', 'OPERATOR' => '>='),
);

global $database;
$SQL = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$charset = $database->get_one($SQL, MYSQL_ASSOC);
$PRECHECK['CUSTOM_CHECKS'] = array(
	'Default Charset' => array(
		'REQUIRED' => 'utf-8',
		'ACTUAL' => $charset,
		'STATUS' => ($charset === 'utf-8')
	)
);
