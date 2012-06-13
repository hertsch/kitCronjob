<?php

/**
 * kitCronjob
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 phpManufaktur by Ralf Hertsch
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


$module_directory = 'kit_cronjob';
$module_name = 'kitCronjob';
$module_function = 'tool';
$module_version = '0.10';
$module_status = 'BETA';
$module_platform = '2.8';
$module_author = 'Projekt EM & Ralf Hertsch - Berlin (Germany)';
$module_license = 'GNU Public License (GPL)';
$module_description = 'Cronjobs for KeepInTouch (KIT)';
$module_home = 'http://phpmanufaktur.de/kit_cronjob';
$module_guid = 'BEDF5ED1-8C29-46A6-8A4C-0C71BBD5B80D';

?>