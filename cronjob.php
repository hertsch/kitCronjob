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

// first we need the LEPTON config.php
require_once('../../config.php');

// wb2lepton compatibility
if (!defined('LEPTON_PATH'))
  require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/wb2lepton.php';


class cronjobExec {

  private $error = '';
  private $cronjob_active = false;
  private $cronjob_key = '';
  private $use_ssl = false;
  private $timezone = 'Europe/Berlin';
  private $cronjob_executed = false;
  private $php_exec = '/usr/bin/exec';

  const LOG_STATUS = 'log_status';
  const LOG_MESSAGE = 'log_message';
  const CRONJOB_ID = 'cronjob_id';
  const CRONJOB_NAME = 'cronjob_name';
  const CRONJOB_STATUS = 'cronjob_status';
  const CRONJOB_RUN = 'cronjob_run';

  const STATUS_OK = 'OK';
  const STATUS_INACTIVE = 'INACTIVE';
  const STATUS_ERROR = 'ERROR';

  private $log = array(
      self::LOG_STATUS => self::STATUS_OK,
      self::LOG_MESSAGE => '',
      self::CRONJOB_ID => '-1',
      self::CRONJOB_NAME => '',
      self::CRONJOB_STATUS => '',
      self::CRONJOB_RUN => '0000-00-00 00:00:00'
      );

  public function __construct() {

  } // __construct()

  /**
   * Set an error and write to Logfile
   *
   * @param unknown_type $error
   */
  protected function setError($error) {
    global $database;
    $this->error = $error;
    $this->log[self::LOG_STATUS] = self::STATUS_ERROR;
    $this->log[self::LOG_MESSAGE] = addslashes($error);
    $this->writeLog();
  } // setError()

  /**
   * Get the last error message
   *
   * @return string
   */
  protected function getError() {
    return $this->error;
  } // getError()

  protected function writeLog() {
    global $database;
    $fields = '';
    $values = '';
    $start = true;
    foreach ($this->log as $key => $value) {
      if (!$start) {
        $fields .=',';
        $values .= ',';
      }
      $fields .= "`$key`";
      $values .= "'$value'";
      $start = false;
    }
    $SQL = sprintf("INSERT INTO %smod_kit_cj_log (%s) VALUES (%s)", TABLE_PREFIX, $fields, $values);
    if (!$database->query($SQL)) {
      trigger_error(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()), E_USER_ERROR);
      exit();
    }
    return true;
  } // writeLog()

  /**
   * Get the general cronjob settings
   *
   * @return boolean
   */
  protected function getSettings() {
    global $database;
    // is cronjob active?
    $SQL = "SELECT `cfg_value` FROM ".TABLE_PREFIX."mod_kit_cj_config WHERE `cfg_name`='cfg_cronjob_active'";
    $this->cronjob_active = (bool) $database->get_one($SQL, MYSQL_ASSOC);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
    // cronjob key
    $SQL = "SELECT `cfg_value` FROM ".TABLE_PREFIX."mod_kit_cj_config WHERE `cfg_name`='cfg_cronjob_key'";
    $this->cronjob_key = $database->get_one($SQL, MYSQL_ASSOC);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
    // use SSL?
    $SQL = "SELECT `cfg_value` FROM ".TABLE_PREFIX."mod_kit_cj_config WHERE `cfg_name`='cfg_use_ssl'";
    $this->use_ssl = (bool) $database->get_one($SQL, MYSQL_ASSOC);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
    // timezone
    $SQL = "SELECT `cfg_value` FROM ".TABLE_PREFIX."mod_kit_cj_config WHERE `cfg_name`='cfg_timezone'";
    $this->timezone = $database->get_one($SQL, MYSQL_ASSOC);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
    // PHP Exec
    $SQL = "SELECT `cfg_value` FROM ".TABLE_PREFIX."mod_kit_cj_config WHERE `cfg_name`='cfg_php_exec'";
    $this->php_exec = $database->get_one($SQL, MYSQL_ASSOC);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
    return true;
  } // getSettings

  /**
   * Check if the KEY parameter is set and correct.
   *
   * @return boolean
   */
  protected function checkCronjobKey() {
    return (!isset($_REQUEST['key']) || ($_REQUEST['key'] != $this->cronjob_key)) ? false : true;
  } // checkCronjobKey()

  /**
   * Calculate the next date and time the specified cronjob must be executed
   *
   * @param array $cronjob record with the cronjob data
   * @return string in MySQL format Y-m-d H:i:s
   */
  protected function calculateNextRun($cronjob) {
    $hours = explode(',', $cronjob['cronjob_hour']);
    $minutes = explode(',', $cronjob['cronjob_minute']);
    $days = explode(',', $cronjob['cronjob_day_of_month']);
    $months = explode(',', $cronjob['cronjob_month']);
    $days_of_week = explode(',', $cronjob['cronjob_day_of_week']);

    if ((count($days) == 31) && (count($months) == 12) && (count($days_of_week) == 7)) {
      // this job runs daily!
      $act_minute = (int) date('i');
      $next_minute = 0;
      $minutes = explode(',', $cronjob['cronjob_minute']);
      foreach ($minutes as $minute) {
        if ($minute > $act_minute) {
          $next_minute = $minute;
          break;
        }
      }
      if ($next_minute == 0) $next_minute = $minutes[0];
      $act_hour = (int) date('H');
      $next_hour = 0;
      $hours = explode(',', $cronjob['cronjob_hour']);
      foreach ($hours as $hour) {
        if (strtotime(date('H:i', mktime($hour, $next_minute, 0, date('m'), date('d'), date('Y')))) > time()) {
          $next_hour = $hour;
          break;
        }
      }
      if ($next_hour == 0) $next_hour = $hours[0];
      $day_add = 0;
      if ($next_hour < (int) date('H')) $day_add = 1;
      return date('Y-m-d H:i:s', mktime($next_hour, $next_minute, 0, date('m'), date('d')+$day_add, date('Y')));
    }
    elseif ((count($days) == 31) && (count($months) == 12)) {
      // this job runs weekly!
      $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
      $difference = 0;
      $use_date = 0;
      foreach ($days_of_week as $weekday) {
        $x_date = strtotime(sprintf('%s this week', $weekday));
        if ($x_date >= $today) {
          $x = $x_date-$today;
          // choose the nearest day!
          if (($difference == 0) || ($x < $difference)) {
            if ($x_date == $today) {
              // special situation: it's today!
              foreach ($hours as $hour) {
                // walk through the hours...
                if ($hour >= (int) date('H')) {
                  if ($hour == (int) date('H')) {
                    // it's the actual hour, just now!
                    foreach ($minutes as $minute) {
                      if (mktime($hour, $minute, 0, date('m'), date('d'), date('Y')) > time()) {
                        // success: choose this date!
                        return date('Y-m-d H:i:s', mktime($hour, $minute, 0, date('m'), date('d'), date('Y')));
                      }
                    }
                  }
                  else {
                    // success: choose this date!
                    return date('Y-m-d H:i:s', mktime($hour, $minutes[0], 0, date('m'), date('d'), date('Y')));
                  }
                }
              }
            }
            else {
              // mark as best choice
              $difference = $x;
              $use_date = $x_date;
            }
          }
        }
      }
      // ok - choose the best choice and return
      return date('Y-m-d H:i:s', mktime($hours[0], $minutes[0], 0, date('m', $use_date), date('d', $use_date), date('Y', $use_date)));
    }
    elseif ((count($months) == 12) && (count($days_of_week) == 7)) {
      // this job runs monthly
      foreach ($days as $day) {
        if ($day >= (int) date('d')) {
          if ($day == (int) date('d')) {
            // special: it's today!
            foreach ($hours as $hour) {
              if ($hour >= (int) date('H')) {
                foreach ($minutes as $minute) {
                  if (mktime($hour, $minute, 0, date('m'), $day, date('Y')) > time()) {
                    // success: choose this date!
                    return date('Y-m-d H:i:s', mktime($hour, $minute, 0, date('m'), $day, date('Y')));
                  }
                }
              }
            }
          }
          else {
            // choose this day and the first time of the day
            return date('Y-m-d H:i:s', mktime($hours[0], $minutes[0], 0, date('m'), $day, date('Y')));
          }
        }
      }
      // no date within the actual month, select the next month!
      return date('Y-m-d H:i:s', mktime($hours[0], $minutes[0], 0, date('m')+1, $days[0], date('Y')));
    }
    else {
      // this job runs at specific dates
      foreach ($months as $month_name) {
        // convert monthname to integer:
        $month = (int) date("m", strtotime($month_name));
        if ($month >= (int) date('m')) {
          foreach ($days as $day) {
            if ($day >= (int) date('d')) {
              if ($day == (int) date('d')) {
                // it's today!
                foreach ($hours as $hour) {
                  if ($hour >= (int) date('H')) {
                    foreach ($minutes as $minute) {
                      if (mktime($hour, $minute, 0, $month, $day, date('Y')) > time()) {
                        // success: choose this date
                        return date('Y-m-d H:i:s', mktime($hour, $minute, 0, $month, $day, date('Y')));
                      }
                    }
                  }
                }
              }
              else {
                // choose this day for the date
                return date('Y-m-d H:i:s', mktime($hours[0], $minutes[0], 0, $month, $days[0], date('Y')));
              }
            }
          }
        }
      }
      // date is not within the actual year, choose the first entry for the next one!
      return date('Y-m-d H:i:s', mktime($hours[0], $minutes[0], 0, date('m', strtotime($months[0])), $days[0], date('Y')+1));
    }
  } // calculateNextRun()

  /**
   * Update a cronjob record
   *
   * @param array $cronjob data record
   */
  protected function updateCronjob($cronjob) {
    global $database;

    $data = $cronjob;
    unset($data['cronjob_id']);
    unset($data['cronjob_timestamp']);

    $SQL = sprintf("UPDATE %smod_kit_cj_cronjob SET ", TABLE_PREFIX);
    $start = true;
    foreach ($data as $key => $value) {
      if (!$start) $SQL .= ',';
      $SQL .= sprintf("`%s`='%s'", $key, $value);
      $start = false;
    }
    $SQL .= sprintf(" WHERE `cronjob_id`='%d'", $cronjob['cronjob_id']);
    $database->query($SQL);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
  } // updateCronjob()

  /**
   * Check if there are any cronjobs to execute
   *
   */
  protected function checkScheduledJobs() {
    global $database;
    // first get all active cronjobs
    $SQL = sprintf("SELECT * FROM %smod_kit_cj_cronjob WHERE `cronjob_status`='ACTIVE'", TABLE_PREFIX);
    if (null ===($result = $database->query($SQL))) {
      $this->setError(sprintf('[%s _ %s] %s', __METHOD__, __LINE__, $database->get_error()));
      exit($this->getError());
    }
    $cronjobs = array();
    while (false !== ($job = $result->fetchRow(MYSQL_ASSOC))) {
      $cronjobs[$job['cronjob_id']] = $job;
    }
    foreach ($cronjobs as $cronjob) {
      $this->log[self::CRONJOB_ID] = $cronjob['cronjob_id'];
      $this->log[self::CRONJOB_NAME] = $cronjob['cronjob_name'];
      if ($cronjob['cronjob_next_run'] == '0000-00-00 00:00:00') {
        // this cronjob was never executed...
        $cronjob['cronjob_next_run'] = $this->calculateNextRun($cronjob);
        $cronjob['cronjob_last_status'] = 'First call, calculate the next regular run of this cronjob.';
        $cronjob['cronjob_last_run'] = date('Y-m-d H:i:s');
        $this->updateCronjob($cronjob);
        $this->log[self::LOG_MESSAGE] = 'First call, calculate the next regular run of this cronjob.';
        $this->log[self::LOG_STATUS] = self::STATUS_OK;
        $this->log[self::CRONJOB_RUN] = $cronjob['cronjob_last_run'];
        $this->log[self::CRONJOB_STATUS] = 'OK';
        $this->writeLog();
      }
      if (strtotime($cronjob['cronjob_next_run']) < time()) {
        // execute this cronjob
        if (false !== strpos($cronjob['cronjob_command'], LEPTON_URL)) {
          $cronjob['cronjob_command'] = substr($cronjob['cronjob_command'], strlen(LEPTON_URL));
        }
        if (false !== strpos($cronjob['cronjob_command'], LEPTON_PATH)) {
          $cronjob['cronjob_command'] = substr($cronjob['cronjob_command'], strlen(LEPTON_PATH));
        }
        if (strpos($cronjob['cronjob_command'], DIRECTORY_SEPARATOR) == 0) {
          $cronjob['cronjob_command'] = substr($cronjob['cronjob_command'], 1);
        }
        $execute = LEPTON_PATH.DIRECTORY_SEPARATOR.$cronjob['cronjob_command'];
        if (!file_exists($execute)) {
          // write the error to the log but continue with the next cronjob.
          $this->setError(sprintf('The relative path %s does not exists, please check the settings for this cronjob!', $cronjob['cronjob_command']));
          continue;
        }
        if (false === ($result = exec(sprintf("%s %s", $this->php_exec, $execute)))) {
          // error executing - write log and continue
          $this->setError(sprintf('Error executing %s - no more informations available.', $cronjob['cronjob_command']));
          continue;
        }
        $this->log[self::LOG_MESSAGE] = $result;
        $this->log[self::LOG_STATUS] = self::STATUS_OK;
        $this->log[self::CRONJOB_RUN] = date('Y-m-d H:i:s');
        $this->log[self::CRONJOB_STATUS] = 'OK';
        $this->writeLog();
        $cronjob['cronjob_last_run'] = $this->log[self::CRONJOB_RUN];
        $cronjob['cronjob_last_status'] = $result;
        $cronjob['cronjob_next_run'] = $this->calculateNextRun($cronjob);
        $this->updateCronjob($cronjob);
        $this->cronjob_executed = true;
      }
    }
  } // checkScheduledJobs()

  /**
   * The action handler for cronjob.php
   *
   * @return string exit code
   */
  public function action() {
    $this->getSettings();
    if (!date_default_timezone_set($this->timezone)) {
      // invalid timezone
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          sprintf("The timezone '%s' is invalid, please check the settings for kitCronjob!", $this->timezone)));
      // set Europe/Berlin as timezone and continue the job.
      date_default_timezone_set('Europe/Berlin');
    }
    if (!$this->cronjob_active) {
      // the cronjob is not active, so terminate immediate!
      $this->log[self::LOG_STATUS] = self::STATUS_INACTIVE;
      $this->log[self::LOG_MESSAGE] = 'The cronjob is inactive and does not execute any job.';
      $this->writeLog();
      exit('OK');
    }
    if (!$this->checkCronjobKey()) {
      // the cronjob KEY is not set or invalid
      $this->log[self::LOG_STATUS] = self::STATUS_ERROR;
      $this->log[self::LOG_MESSAGE] = 'The cronjob.php was called without the parameter KEY or with a wrong passphrase for the KEY.';
      $this->writeLog();
      exit($this->getError());
    }
    // check the scheduled jobs
    $this->checkScheduledJobs();

    if (!$this->cronjob_executed) {
      // nothing to do - still quit
      $this->log[self::LOG_STATUS] = self::STATUS_OK;
      $this->log[self::LOG_MESSAGE] = 'Nothing to do.';
      $this->log[self::CRONJOB_ID] = -1;
      $this->log[self::CRONJOB_NAME] = '';
      $this->writeLog();
    }
    exit('OK');
  } // action()

} // class cronjobExec


$cronjob = new cronjobExec();
$cronjob->action();
