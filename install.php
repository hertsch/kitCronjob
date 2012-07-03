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

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

if (defined('LEPTON_VERSION'))
  $database->prompt_on_error(false);

global $admin;
global $database;

$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_kit_cj_cronjob` ( ".
    "`cronjob_id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`cronjob_name` VARCHAR(64) NOT NULL DEFAULT '', ".
    "`cronjob_description` TEXT, ".
    "`cronjob_minute` VARCHAR(255) NOT NULL DEFAULT '0', ".
    "`cronjob_hour` VARCHAR(255) NOT NULL DEFAULT '0', ".
    "`cronjob_day_of_month` VARCHAR(255) NOT NULL DEFAULT '1', ".
    "`cronjob_day_of_week` VARCHAR(255) NOT NULL DEFAULT 'Sunday', ".
    "`cronjob_month` VARCHAR(255) NOT NULL DEFAULT 'January', ".
    "`cronjob_command` TEXT, ".
    "`cronjob_last_run` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ".
    "`cronjob_last_status` TEXT, ".
    "`cronjob_next_run` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ".
    "`cronjob_status` ENUM('ACTIVE','LOCKED','DELETED') DEFAULT 'ACTIVE', ".
    "`cronjob_timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`cronjob_id`)".
    " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
if (null == $database->query($SQL))
  $admin->print_error($database->get_error());

$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_kit_cj_log` ( ".
    "`log_id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`log_status` ENUM('OK','INACTIVE','ERROR') NOT NULL DEFAULT 'OK', ".
    "`log_message` TEXT, ".
    "`cronjob_id` INT(11) NOT NULL DEFAULT '-1', ".
    "`cronjob_name` VARCHAR(64) NOT NULL DEFAULT '', ".
    "`cronjob_status` TEXT, ".
    "`cronjob_run` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ".
    "`log_timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`log_id`)".
    " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
if (null == $database->query($SQL))
  $admin->print_error($database->get_error());


require_once LEPTON_PATH.'/modules/manufaktur_config/library.php';
// initialize the configuration
$config = new manufakturConfig();
if (!$config->readXMLfile(LEPTON_PATH.'/modules/kit_cronjob/config/kitCronjob.xml', 'kit_cronjob', true)) {
  $admin->print_error($config->getError());
}
