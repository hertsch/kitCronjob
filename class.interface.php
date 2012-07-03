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
  if (defined('LEPTON_VERSION'))
    include(WB_PATH . '/framework/class.secure.php');
} else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include($root . '/framework/class.secure.php');
  } else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// wb2lepton compatibility
if (!defined('LEPTON_PATH'))
  require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/wb2lepton.php';

// use LEPTON 2.x I18n for access to language files
if (!class_exists('LEPTON_Helper_I18n'))
  require_once LEPTON_PATH.'/modules/manufaktur_config/framework/LEPTON/Helper/I18n.php';

global $I18n;
if (!is_object($I18n))
  $I18n = new LEPTON_Helper_I18n();

if (file_exists(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/'.LANGUAGE.'.php')) {
  $I18n->addFile(LANGUAGE.'.php', LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');
}

// load language depending configuration file
if (file_exists(LEPTON_PATH.'/modules/manufaktur_config/languages/'.LANGUAGE.'.cfg.php'))
  require_once LEPTON_PATH.'/modules/manufaktur_config/languages/'.LANGUAGE.'.cfg.php';
else
  require_once LEPTON_PATH.'/modules/manufaktur_config/languages/EN.cfg.php';

require_once LEPTON_PATH.'/modules/manufaktur_config/library.php';
global $manufakturConfig;
if (!is_object($manufakturConfig)) $manufakturConfig = new manufakturConfig('kit_cronjob');

global $cronjobInterface;
if (!is_object($cronjobInterface))
  $cronjobInterface = new cronjobInterface();

class cronjobInterface {

  const CRONJOB_ID = 'cronjob_id';
  const CRONJOB_NAME = 'cronjob_name';
  const CRONJOB_DESCRIPTION = 'cronjob_description';
  const CRONJOB_HOUR = 'cronjob_hour';
  const CRONJOB_MINUTE = 'cronjob_minute';
  const CRONJOB_DAY_OF_MONTH = 'cronjob_day_of_month';
  const CRONJOB_DAY_OF_WEEK = 'cronjob_day_of_week';
  const CRONJOB_MONTH = 'cronjob_month';
  const CRONJOB_COMMAND = 'cronjob_command';
  const CRONJOB_LAST_STATUS = 'cronjob_last_status';
  const CRONJOB_LAST_RUN = 'cronjob_last_run';
  const CRONJOB_NEXT_RUN = 'cronjob_next_run';
  const CRONJOB_STATUS = 'cronjob_status';
  const CRONJOB_TIMESTAMP = 'cronjob_timestamp';

  const CFG_CRONJOB_KEY = 'cfg_cronjob_key';
  const CFG_CRONJOB_ACTIVE = 'cfg_cronjob_active';
  const CFG_USE_SSL = 'cfg_use_ssl';
  const CFG_CRONJOB_NAME_MINIMUM_LENGTH = 'cfg_cronjob_name_minimum_length';
  const CFG_USE_TIMEZONE = 'cfg_use_timezone';
  const CFG_PHP_EXEC = 'cfg_php_exec';
  const CFG_LOG_LIST_LIMIT = 'cfg_log_list_limit';
  const CFG_LOG_SHOW_NO_LOAD = 'cfg_log_show_no_load';
  const CFG_LOG_TABLE_LIMIT = 'cfg_log_table_limit';

  protected static $field_array = array(
      self::CRONJOB_ID => -1,
      self::CRONJOB_NAME => '',
      self::CRONJOB_DESCRIPTION => '',
      self::CRONJOB_HOUR => array(),
      self::CRONJOB_MINUTE => array(),
      self::CRONJOB_DAY_OF_MONTH => array(),
      self::CRONJOB_DAY_OF_WEEK => array(),
      self::CRONJOB_MONTH => array(),
      self::CRONJOB_COMMAND => '',
      self::CRONJOB_LAST_STATUS => '',
      self::CRONJOB_LAST_RUN => '0000-00-00 00:00:00',
      self::CRONJOB_NEXT_RUN => '0000-00-00 00:00:00',
      self::CRONJOB_STATUS => 'ACTIVE',
      self::CRONJOB_TIMESTAMP => '0000-00-00 00:00:00');

  protected static $field_translate_array = array(
      'cronjob_minute',
      'cronjob_hour',
      'cronjob_day_of_month',
      'cronjob_day_of_week',
      'cronjob_month'
  );

  protected static $minute_array = array(0,5,10,15,20,25,30,35,40,45,50,55);
  protected static $hour_array = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
  protected static $day_of_month_array = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
  protected static $day_of_week_array = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
  protected static $month_array = array('January','February','March','April','May','June','July','August','September','October','November','December');

  private static $error = '';
  private static $message = '';

  protected $lang = NULL;

  /**
   * Constructor for conjobInterface
   */
  public function __construct() {
    global $I18n;
    $this->lang = $I18n;
    // check if the cronjob key is already set
    $key = $this->getCronjobConfigValue(self::CFG_CRONJOB_KEY);
    if (empty($key)) {
      // create a new random key for the cronjob
      $key = manufakturConfig::generatePassword(12);
      $this->setCronjobConfigValue(self::CFG_CRONJOB_KEY, $key);
    }
  } // construct()

  /**
   * Set self::$error to $error
   *
   * @param string $error
   */
  public function setError($error) {
    self::$error = $error;
  } // setError()

  /**
   * Get Error from self::$error;
   *
   * @return string self::$error
   */
  public function getError() {
    return self::$error;
  } // getError()

  /**
   * Check if self::$error is empty
   *
   * @return boolean
   */
  public function isError() {
    return (bool) !empty(self::$error);
  } // isError

  /** Set self::$message to $message
   *
   * @param string $message
   */
  public function setMessage($message) {
    self::$message = $message;
  } // setMessage()

  /**
   * Get Message from self::$message;
   *
   * @return string self::$message
   */
  public function getMessage() {
    return self::$message;
  } // getMessage()

  /**
   * Check if self::$message is empty
   *
   * @return boolean
   */
  public function isMessage() {
    return (bool) !empty(self::$message);
  } // isMessage

  /**
   * Get the cronjob with the id $id from the database.
   *
   * @param integer $id
   * @param reference array $cronjob
   * @return boolean true on success, false on error
   */
  public function getCronjob($id, &$cronjob = array()) {
    global $database;

    $SQL = sprintf("SELECT * FROM `%smod_kit_cj_cronjob` WHERE `cronjob_id`='%s'", TABLE_PREFIX, $id);
    if (null === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query->numRows() < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: The record with the <b>ID {{ id }}</b> does not exists!',
              array('id' => $id))));
      return false;
    }
    $cronjob = $query->fetchRow(MYSQL_ASSOC);
    // explode the fields minute, hour, day_of_month, day_of_week and month
    foreach (self::$field_translate_array as $key) {
      $array = explode(',', $cronjob[$key]);
      $cronjob[$key] = $array;
    }
    return true;
  } // getCronjob()

  /**
   * Insert a new cronjob record to the database table
   *
   * @param array $cronjob data
   * @param integer $id the ID of the new record
   * @return boolean
   */
  public function insertCronjob($cronjob, &$id = -1) {
    global $database;

    $fields = '';
    $values = '';
    foreach ($cronjob as $key => $value) {
      if ($key == 'cronjob_id') continue;
      // implode the fields minute, hour, day_of_month, day_of_week and month
      if (in_array($key, self::$field_translate_array) && is_array($value))
        $value = implode(',', $value);
      if (!empty($fields)) {
        $fields .= ",`$key`";
        $values .= ",'$value'";
      }
      else {
        $fields .= "`$key`";
        $values .= "'$value'";
      }
    }
    $SQL = sprintf("INSERT INTO `%smod_kit_cj_cronjob` (%s) VALUES (%s)",
        TABLE_PREFIX, $fields, $values);
    if (null === $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $id = mysql_insert_id();
    return true;
  } // insertCronjob()

  /**
   * Update the existing cronjob with the id $id and the data $dronjob
   *
   * @param integer $id
   * @param array $cronjob
   * @return boolean
   */
  public function updateCronjob($id, $cronjob) {
    global $database;

    $values = '';
    foreach ($cronjob as $key => $value) {
      // implode the fields minute, hour, day_of_month, day_of_week and month
      if (in_array($key, self::$field_translate_array) && is_array($value))
        $value = implode(',', $value);
      $values .= (!empty($values)) ? ",`$key`='$value'" : "`$key`='$value'";
    }
    $SQL = sprintf("UPDATE `%smod_kit_cj_cronjob` SET %s WHERE `cronjob_id`='%d'",
        TABLE_PREFIX, $values, $id);
    if (null === $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // updateCronjob()

  /**
   * Return the cronjob field array
   *
   * @return array
   */
  public function getCronjobFieldArray() {
    return self::$field_array;
  } // getCronjobFieldArray()

  /**
   * Check if a $_REQUEST isset for a cronjob field and update the field array
   *
   * @param reference array $cronjob
   * @return boolean
   */
  public function checkCronjobRequests(&$cronjob) {
    foreach (self::$field_array as $key => $value) {
      if (isset($_REQUEST[$key])) {
        $cronjob[$key] = (in_array($key, self::$field_translate_array)) ? implode(',', $_REQUEST[$key]) : $_REQUEST[$key];
      }
    }
    return true;
  } // checkCronjobRequests()

  /**
   * Check if a cronjob name is unique or not.
   * If $ignore_ID is specified the cronjob with this ID will be ignored.
   *
   * @param string $name
   * @param integer $ignore_ID
   * @return boolean result
   */
  public function checkCronjobNameIsUnique($name, $ignore_ID = null) {
    global $database;

    $ignore = ($ignore_ID == null) ? '' : sprintf(" AND `cronjob_id`!='%d'", $ignore_ID);
    $SQL = sprintf("SELECT `cronjob_id` FROM `%smod_kit_cj_cronjob` WHERE `cronjob_name`='%s'%s",
        TABLE_PREFIX, $name, $ignore);
    if (null === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return ($query->numRows() > 0) ? false : true;
  } // checkCronjobNameIsUnique()

  /**
   * Return the default entries for the fields DAY_OF_MONTH, DAY_OF_WEEK, HOUR,
   * MINUTE or MONTH as array (for usage in templates a.s.o).
   *
   * @param string $field
   * @param array refrence $entries
   * @return boolean
   */
  public function fieldDefaults2array($field, &$entries = array()) {
    switch ($field) {
  	  case self::CRONJOB_DAY_OF_MONTH:
  	  	$entries = self::$day_of_month_array;
  	  	break;
  	  case self::CRONJOB_DAY_OF_WEEK:
  	  	$entries = self::$day_of_week_array;
  	  	break;
  	  case self::CRONJOB_HOUR:
  	  	$entries = self::$hour_array;
  	  	break;
  	  case self::CRONJOB_MINUTE:
  	  	$entries = self::$minute_array;
  	  	break;
  	  case self::CRONJOB_MONTH:
  	  	$entries = self::$month_array;
  	  	break;
  	  default:
  	  	$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
  	  	    $this->lang->translate('There exists no array with default values for the field {{ field }}.',
  	  	        array('field' => $field))));
  	  	return false;
  	}
  	return true;
  } /// fieldDefaults2array()

  /**
   * Get the desired configuration $key and returns the value in the correct
   * data format.
   *
   * @param string $key
   * @return mixed configuration value
   */
  public function getCronjobConfigValue($key) {
    global $manufakturConfig;
    return $manufakturConfig->getValue($key, 'kit_cronjob');
  } // getCronjobConfigValue()

  /**
   * Set a new value $value for the desired configuration $key
   *
   * @param string $key
   * @param string $value
   * @return boolean result
   */
  public function setCronjobConfigValue($key, $value) {
    global $manufakturConfig;
    $data = array(
        manufakturConfig::FIELD_MODULE_NAME => 'kitCronjob',
        manufakturConfig::FIELD_MODULE_DIRECTORY => 'kit_cronjob',
        manufakturConfig::FIELD_NAME => $key,
        manufakturConfig::FIELD_VALUE => $value
        );
    return $manufakturConfig->setValue($data);
  } // setCronjobConfigValue()

  /**
   * Return an array $cronjobs with all cronjobs of a status desired in the
   * $status_array
   *
   * @param array $status_array - possible: ACTICE, LOCKED, DELETED
   * @param array reference $cronjobs
   * @return boolean
   */
  public function getCronjobsByStatus(&$cronjobs=array(), $status_array=array('ACTIVE', 'LOCKED')) {
  	global $database;

  	$SQL = "SELECT * FROM `".TABLE_PREFIX."mod_kit_cj_cronjob` WHERE ";
  	$start = true;
  	foreach ($status_array as $status) {
  		if (!$start) $SQL .= " OR ";
  		$start = false;
  		$SQL .= "`cronjob_status`='$status'";
  	}
  	if (null == ($query = $database->query($SQL))) {
  	  $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
  	  return false;
  	}
  	$cronjobs = array();
  	while (false !== ($cronjob = $query->fetchRow(MYSQL_ASSOC))) {
  	  foreach (self::$field_translate_array as $key)
  	    $cronjob[$key] = explode(',', $cronjob[$key]);
  	  $cronjobs[] = $cronjob;
  	}
  	return true;
  } // getCronjobsByStatus()

  public function getCronjobProtocol(&$protocol_array, $limit=100, $show_no_load=false) {
    global $database;

    if ($show_no_load) {
      $SQL = sprintf("SELECT * FROM `%smod_kit_cj_log` ORDER BY `log_timestamp` DESC LIMIT %d",
          TABLE_PREFIX, $limit);
    }
    else {
      $SQL = sprintf("SELECT * FROM `%smod_kit_cj_log` WHERE NOT (`log_status`='OK' AND `cronjob_id`='-1') ORDER BY `log_timestamp` DESC LIMIT %d",
          TABLE_PREFIX, $limit);
    }
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $protocol_array = array();
    while (false !== ($protocol = $query->fetchRow(MYSQL_ASSOC)))
      $protocol_array[] = $protocol;
    return true;
  } // getCronjobProtocol()

  /**
   * Shrink the protocol table to the limit of records specified in the settings
   *
   * @return boolean
   */
  public function shrinkProtocol() {
    global $database;

    $SQL = sprintf("SELECT MAX(`log_id`) FROM `%smod_kit_cj_log`", TABLE_PREFIX);
    if ((null == ($max = $database->get_one($SQL, MYSQL_ASSOC))) && $database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }

    $limit = $this->getCronjobConfigValue(self::CFG_LOG_TABLE_LIMIT);
    $shrink = $max - $limit;
    if ($shrink > 0) {
      $SQL = sprintf("DELETE FROM `%smod_kit_cj_log` WHERE `log_id` < '%d'", TABLE_PREFIX, $shrink);
      if (null == $database->query($SQL)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
    }
    return true;
  } // shrinkProtocol()

} // class CronjobInterface
