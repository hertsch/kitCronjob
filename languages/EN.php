<?php

/**
 * kitCronjob
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 - phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
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

if ('á' != "\xc3\xa1") {
  // important: language files must be saved as UTF-8 (without BOM)
  trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

/**
 * IMPORTANT NOTE:
 * This language file is shurely NOT COMPLETE and does not contain all strings
 * used by this addon. To get an overview of all used language strings please
 * have a look to the /languages/DE.php - german is the origin language of this
 * addon and so the DE.php should be used as base for all translations.
 */

$LANG = array(
    'HINT_CFG_CRONJOB_ACTIVE'
      => 'Switch kitCronjob on or off. 1=ON, 0=OFF',
    'HINT_CFG_CRONJOB_KEY'
      => 'For security reasons the cronjob.php will be only executed if a valid key is given as parameter. Call cronjob.php?<b>key=KEY</b>.',
    'HINT_CJ_COMMAND'
      => '',
    'HINT_CJ_DAY'
      => '',
    'HINT_CJ_DESCRIPTION'
      => '',
    'HINT_CJ_HOUR'
      => '',
    'HINT_CJ_LAST_RUN'
      => '',
    'HINT_CJ_LAST_STATUS'
      => '',
    'HINT_CJ_MINUTE'
      => '',
    'HINT_CJ_MONTH'
      => '',
    'HINT_CJ_NAME'
      => '',
    'HINT_CJ_NEXT_RUN'
      => '',
    'HINT_CJ_PERIODIC'
      => '',
    'HINT_CJ_STATUS'
      => '',
    'HINT_CJ_TIMESTAMP'
      => '',
    'HINT_CJ_YEAR'
      => '',
    'HINT_CFG_CRONJOB_ACTIVE'
      => 'Cronjob aktive',
    'LABEL_CFG_CRONJOB_KEY'
      => 'Cronjob kex',
    'LABEL_CJ_COMMAND'
      => 'Command',
    'LABEL_CJ_DAY_OF_MONTH'
      => 'Day',
    'LABEL_CJ_DAY_OF_WEEK'
      => 'Weekday',
    'LABEL_CJ_DESCRIPTION'
      => 'Description',
    'LABEL_CJ_HOUR'
      => 'Hour',
    'LABEL_CJ_LAST_RUN'
      => 'Last execution',
    'LABEL_CJ_LAST_STATUS'
      => 'Last status',
    'LABEL_CJ_MINUTE'
      => 'Minute',
    'LABEL_CJ_MONTH'
      => 'Month',
    'LABEL_CJ_NAME'
      => 'Name',
    'LABEL_CJ_NEXT_RUN'
      => 'Next run',
    'LABEL_CJ_STATUS'
      => 'Status',
    'LABEL_CJ_TIMESTAMP'
      => 'Timestamp',
  	'LABEL_CRONJOB'
		  => 'Define cronjob',

);