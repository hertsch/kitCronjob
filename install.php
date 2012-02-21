<?php

/**
 * kitCronjob
 * 
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 - phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 * @version $Id$
 * 
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
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

// use LEPTON 2.x I18n for access to language files
if (! class_exists('LEPTON_Helper_I18n')) require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/framework/LEPTON/Helper/I18n.php';
global $I18n;
if (!is_object($I18n)) {
  $I18n = new LEPTON_Helper_I18n();
}
else {
  $I18n->addFile('DE.php', WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');
}

// load language depending onfiguration
if (!file_exists(WB_PATH.'/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php')) {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.cfg.php');
} else {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');
}
if (! file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
  if (! defined('KIT_CRONJOB_LANGUAGE')) define('KIT_CRONJOB_LANGUAGE', 'DE'); // important: language flag is used by template selection
} else {
  if (! defined('KIT_CRONJOB_LANGUAGE')) define('KIT_CRONJOB_LANGUAGE', LANGUAGE);
}

require_once WB_PATH.'/modules/kit_cronjob/initialize.php';

global $admin;

$tables = array(
    'dbCronjobConfig'
    );
$error = '';

foreach ($tables as $table) {
  $create = null;
  $create = new $table();
  if (!$create->sqlTableExists()) {
    if (!$create->sqlCreateTable()) {
      $error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
    }
  }
}

// Prompt Errors
if (!empty($error)) {
  $admin->print_error($error);
}
else {
  $admin->print_success('Thank you for using kitIdea!', ADMIN_URL.'/admintools/tool.php?tool=kit_idea&act=abt');
}