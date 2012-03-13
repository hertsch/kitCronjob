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

require_once LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/initialize.php';

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
  const CRONJOB_LAST_STATUS = 'cronjob_last_command';
  const CRONJOB_LAST_RUN = 'cronjob_last_run';
  const CRONJOB_NEXT_RUN = 'cronjob_next_run';
  const CRONJOB_STATUS = 'cronjob_status';
  const CRONJOB_TIMESTAMP = 'cronjob_timestamp';

  const CFG_CRONJOB_KEY = dbCronjobConfig::CFG_CRONJOB_KEY;
  const CFG_CRONJOB_ACTIVE = dbCronjobConfig::CFG_CRONJOB_ACTIVE;
  const CFG_USE_SSL = dbCronjobConfig::CFG_USE_SSL;
  const CFG_CRONJOB_NAME_MINIMUM_LENGTH = dbCronjobConfig::CFG_CRONJOB_NAME_MINIMUM_LENGTH;

  private $field_array = array(
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
      self::CRONJOB_STATUS => dbCronjob::STATUS_ACTIVE,
      self::CRONJOB_TIMESTAMP => '0000-00-00 00:00:00');

  private $error = '';
  private $message = '';

  protected $lang = NULL;

  /**
   * Constructor for conjobInterface
   */
  public function __construct() {
    global $I18n;
    $this->lang = $I18n;
  } // construct()

  /**
   * Set $this->error to $error
   *
   * @param string $error
   */
  public function setError($error) {
    $this->error = $error;
  } // setError()

  /**
   * Get Error from $this->error;
   *
   * @return string $this->error
   */
  public function getError() {
    return $this->error;
  } // getError()

  /**
   * Check if $this->error is empty
   *
   * @return boolean
   */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /** Set $this->message to $message
   *
   * @param string $message
   */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
   * Get Message from $this->message;
   *
   * @return string $this->message
   */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
   * Check if $this->message is empty
   *
   * @return boolean
   */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage

  /**
   * Get the cronjob with the id $id from the database.
   *
   * @param integer $id
   * @param reference array $cronjob
   * @return boolean true on success, false on error
   */
  public function getCronjob($id, &$cronjob = array()) {
    global $dbCronjob;

    $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'", $dbCronjob->getTableName(), dbCronjob::FIELD_ID, $id);
    $cronjob = array();
    if (!$dbCronjob->sqlExec($SQL, $cronjob)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
      return false;
    }
    if (count($cronjob) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: The record with the <b>ID {{ id }} does not exists!', array(
          'id' => $id))));
      return false;
    }
echo "get:<pre>";
print_r($cronjob);
echo "</pre>";    
    $cronjob = $cronjob[0];
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
    global $dbCronjob;
    
    if (!$dbCronjob->sqlInsertRecord($cronjob, $id)) { 
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
      return false;
    }
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
    global $dbCronjob;
    
    $where = array(self::CRONJOB_ID => $id);
    if (!$dbCronjob->sqlUpdateRecord($cronjob, $where)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
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
    return $this->field_array;
  } // getCronjobFieldArray()

  /**
   * Check if a $_REQUEST isset for a cronjob field and update the field array
   *
   * @param reference array $cronjob
   * @return boolean
   */
  public function checkCronjobRequests(&$cronjob) {
    $is_array = array(
        self::CRONJOB_DAY_OF_MONTH,
        self::CRONJOB_DAY_OF_WEEK,
        self::CRONJOB_HOUR,
        self::CRONJOB_MINUTE,
        self::CRONJOB_MONTH);
    foreach ($this->field_array as $key => $value) {
      if (isset($_REQUEST[$key])) {
        $cronjob[$key] = (in_array($key, $is_array)) ? implode($_REQUEST[$key])
            : $_REQUEST[$key];
      }
    }
    return true;
  } // checkCronjobRequests()

  /**
   * Check if a cronjob name is unique or not.
   * If $ignor_ID is specified the cronjob with this ID will be ignored.
   *
   * @param string $name
   * @param integer $ignore_ID
   * @return boolean result
   */
  public function checkCronjobNameIsUnique($name, $ignore_ID = NULL) {
    global $dbCronjob;
    $result = array();
    if ($ignore_ID == NULL) {
      $SQL = sprintf("SELECT `%s` FROM %s WHERE `%s`='%s'", dbCronjob::FIELD_ID, $dbCronjob->getTableName(), dbCronjob::FIELD_NAME, $name);
    } else {
      $SQL = sprintf("SELECT `%s` FROM %s WHERE `%s`='%s' AND `%s`!='%s'", dbCronjob::FIELD_ID, $dbCronjob->getTableName(), dbCronjob::FIELD_NAME, $name, dbCronjob::FIELD_ID, $ignore_ID);
    }
    if (!$dbCronjob->sqlExec($SQL, $result)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
      return false;
    }
    if (count($result) > 0)
      return false;
    return true;
  } // checkCronjobNameIsUnique()

  /**
   * This function reads entries from type definition of ENUM() fields and
   * return an array with the values of the ENUM() string.
   *
   * @param string $field
   * @param reference string $entries
   * @return boolean - true on success
   */
  public function enumColumn2array($field, &$entries = array()) {
    global $dbCronjob;
    if (!$dbCronjob->enumColumn2array($field, $entries)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
      return false;
    }
    return true;
  } // enumColumn2array()

  /**
   * Get the desired configuration $key and returns the value in the correct
   * data format.
   *
   * @param string $key
   * @return mixed configuration value
   */
  public function getCronjobConfigValue($key) {
    global $dbCronjobConfig;
    return $dbCronjobConfig->getValue($key);
  } // getCronjobConfigValue()

  /**
   * Set a new value $value for the desired configuration $key
   *
   * @param string $key
   * @param string $value
   * @return boolean result
   */
  public function setCronjobConfigValue($key, $value) {
    global $dbCronjobConfig;
    return $dbCronjobConfig->setValueByName($value, $key);
  } // setCronjobConfigValue()

} // class CronjobInterface
