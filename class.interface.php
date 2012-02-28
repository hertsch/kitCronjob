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
    include (WB_PATH . '/framework/class.secure.php');
} else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  } else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';

global $cronjobInterface;

if (!is_object($cronjobInterface)) $cronjobInterface = new cronjobInterface();

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
      self::CRONJOB_TIMESTAMP => '0000-00-00 00:00:00'
      );

  private $field_assign = array(
      self::CRONJOB_ID => dbCronjob::FIELD_ID,
      self::CRONJOB_NAME => dbCronjob::FIELD_NAME,
      self::CRONJOB_DESCRIPTION => dbCronjob::FIELD_DESCRIPTION,
      self::CRONJOB_HOUR => dbCronjob::FIELD_HOUR,
      self::CRONJOB_MINUTE => dbCronjob::FIELD_MINUTE,
      self::CRONJOB_DAY_OF_MONTH => dbCronjob::FIELD_DAY_OF_MONTH,
      self::CRONJOB_DAY_OF_WEEK => dbCronjob::FIELD_DAY_OF_WEEK,
      self::CRONJOB_MONTH => dbCronjob::FIELD_MONTH,
      self::CRONJOB_COMMAND => dbCronjob::FIELD_COMMAND,
      self::CRONJOB_LAST_STATUS => dbCronjob::FIELD_LAST_STATUS,
      self::CRONJOB_LAST_RUN => dbCronjob::FIELD_LAST_RUN,
      self::CRONJOB_NEXT_RUN => dbCronjob::FIELD_NEXT_RUN,
      self::CRONJOB_STATUS => dbCronjob::FIELD_STATUS,
      self::CRONJOB_TIMESTAMP => dbCronjob::FIELD_TIMESTAMP
      );

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
  public function getCronjob($id, &$cronjob=array()) {
    global $dbCronjob;

    $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'",
        $dbCronjob->getTableName(),
        dbCronjob::FIELD_ID,
        $id);
    $cronjob = array();
    if (!$dbCronjob->sqlExec($SQL, $cronjob)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
      return false;
    }
    if (count($cronjob) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: The record with the <b>ID {{ id }} does not exists!',
              array('id' => $id))));
      return false;
    }
    $cronjob = $cronjob[0];
    return true;
  } // getCronjob()

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
    foreach ($this->field_array as $key => $value) {
      if (isset($_REQUEST[$key])) $cronjob[$key] = $_REQUEST[$key];
    }
    return true;
  } // checkCronjobRequests()

  /**
   * This function reads entries from type definition of ENUM() fields and
   * return an array with the values of the ENUM() string.
   *
   * @param string $field
   * @param reference string $entries
   * @return boolean - true on success
   */
  public function enumColumn2array($field, &$entries=array()) {
    global $dbCronjob;

    if (!$dbCronjob->enumColumn2array($this->field_assign[$field], $entries)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjob->getError()));
      return false;
    }
    return true;
  } // enumColumn2array()

} // class CronjobInterface