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

require_once(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class.interface.php');
require_once LEPTON_PATH . '/modules/manufaktur_i18n/class.dialog.php';
// load the standard functions of LEPTON framework
require_once LEPTON_PATH . '/framework/functions.php';

class cronjobBackend {

  const REQUEST_ACTION = 'act';
  const REQUEST_ITEMS = 'its';

  const ACTION_ABOUT = 'abt';
  const ACTION_CONFIG = 'cfg';
  const ACTION_CONFIG_CHECK = 'cfgc';
  const ACTION_DEFAULT = 'def';
  const ACTION_CRONJOBS = 'cron';
  const ACTION_EDIT = 'edt';
  const ACTION_EDIT_CHECK = 'edtc';
  const ACTION_LANGUAGE = 'lng';

  private $page_link = '';
  private $img_url = '';
  private $template_path = '';
  private $error = '';
  private $message = '';

  protected $lang = NULL;
  protected $tab_navigation_array = null;

  public function __construct() {
    global $lang;
    $this->page_link = ADMIN_URL . '/admintools/tool.php?tool=kit_cronjob';
    $this->img_url = LEPTON_URL . '/modules/' . basename(dirname(__FILE__)) . '/images/';
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $lang;
    // don't translate the Tab Strings here - this will be done in the template!
    $this->tab_navigation_array = array(
        self::ACTION_CRONJOBS => 'Cronjobs',
        self::ACTION_EDIT => 'Edit',
        self::ACTION_CONFIG => 'Settings',
        self::ACTION_LANGUAGE => 'Languages',
        self::ACTION_ABOUT => 'About');
  } // __construct()

  /**
   * Set $this->error to $error
   *
   * @param $error STR
   */
  protected function setError($error) {
    $this->error = $error;
  } // setError()

  /**
   * Get Error from $this->error;
   *
   * @return STR $this->error
   */
  public function getError() {
    return $this->error;
  } // getError()

  /**
   * Check if $this->error is empty
   *
   * @return BOOL
   */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Reset Error to empty String
   */
  protected function clearError() {
    $this->error = '';
  }

  /**
   * Set $this->message to $message
   *
   * @param $message STR
   */
  protected function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
   * Get Message from $this->message;
   *
   * @return STR $this->message
   */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
   * Check if $this->message is empty
   *
   * @return BOOL
   */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage

  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.php');
    if ($info_text == false) {
      return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      }
    }
    return -1;
  } // getVersion()

  /**
   * Return the needed template
   *
   * @param $template string
   * @param $template_data array
   */
  protected function getTemplate($template, $template_data) {
    global $parser;

    $template_path = LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/backend/';

    // check if a custom template exists ...
    $load_template = (file_exists($template_path . 'custom.' . $template)) ? $template_path . 'custom.' . $template
        : $template_path . $template;
    try {
      $result = $parser->get($load_template, $template_data);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n('Error executing the template ' . '<b>{{ template }}</b>: {{ error }}', array(
          'template' => basename($load_template),
          'error' => $e->getMessage()))));
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Verhindert XSS Cross Site Scripting
   *
   * @param $_REQUEST REFERENCE
   *          Array
   * @return $request
   */
  protected function xssPrevent(&$request) {
    if (is_string($request)) {
      $request = html_entity_decode($request);
      $request = strip_tags($request);
      $request = trim($request);
      $request = stripslashes($request);
    }
    return $request;
  } // xssPrevent()

  /**
   * Action handler of the class
   *
   * @return STR result dialog or message
   */
  public function action() {
    $html_allowed = array();
    foreach ($_REQUEST as $key => $value) {
      if (strpos($key, 'cfg_') == 0)
        continue;
      // ignore config values!
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }

    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION]
        : self::ACTION_DEFAULT;
    if (($action == self::ACTION_DEFAULT) && isset($_REQUEST[I18n_Dialog::REQUEST_ACTION]))
      $action = self::ACTION_LANGUAGE;

    switch ($action) :
    case self::ACTION_CONFIG:
      $this->show(self::ACTION_CONFIG, $this->dlgConfig());
      break;
    case self::ACTION_CONFIG_CHECK:
      $this->show(self::ACTION_CONFIG, $this->checkConfig());
      break;
    case self::ACTION_ABOUT:
      $this->show(self::ACTION_ABOUT, $this->dlgAbout());
      break;
    case self::ACTION_EDIT:
      $this->show(self::ACTION_EDIT, $this->dlgEdit());
      break;
    case self::ACTION_EDIT_CHECK:
      $this->show(self::ACTION_EDIT, $this->checkEdit());
      break;
    case self::ACTION_LANGUAGE:
      $i18n_dialog = new I18n_Dialog('kit_cronjob');
      $this->show(self::ACTION_LANGUAGE, $i18n_dialog->action());
      break;
    case self::ACTION_CRONJOBS:
      $this->show(self::ACTION_CRONJOBS, $this->dlgCronjobs());
      break;
    default:
      $this->show(self::ACTION_ABOUT, $this->dlgAbout());
      break;
    endswitch;
  } // action

  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   *
   * @param STR $action - aktives Navigationselement
   * @param STR $content - Inhalt
   *
   * @return ECHO RESULT
   */
  protected function show($action, $content) {
    $navigation = array();
    foreach ($this->tab_navigation_array as $key => $value) {
      $navigation[] = array(
          'active' => ($key == $action) ? 1 : 0,
          'url' => sprintf('%s&%s', $this->page_link, http_build_query(array(
              self::REQUEST_ACTION => $key))),
          'text' => $value);
    }
    $data = array(
        'LEPTON_URL' => LEPTON_URL,
        'IMG_URL' => $this->img_url,
        'navigation' => $navigation,
        'error' => ($this->isError()) ? 1 : 0,
        'content' => ($this->isError()) ? $this->getError() : $content);
    echo $this->getTemplate('body.lte', $data);
  } // show()

  /**
   * Information about kitIdea
   *
   * @return STR dialog
   */
  protected function dlgAbout() {
    $data = array(
        'version' => sprintf('%01.2f', $this->getVersion()),
        'img_url' => $this->img_url,
        'release_notes' => file_get_contents(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt'),);
    return $this->getTemplate('about.lte', $data);
  } // dlgAbout()

  /**
   * Dialog zur Konfiguration und Anpassung von kitIdea
   *
   * @return STR dialog
   */
  protected function dlgConfig() {
    global $dbCronjobConfig;

    $SQL = sprintf("SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s", $dbCronjobConfig->getTableName(), dbCronjobConfig::FIELD_STATUS, dbCronjobConfig::STATUS_DELETED, dbCronjobConfig::FIELD_NAME);
    $config = array();
    if (!$dbCronjobConfig->sqlExec($SQL, $config)) {
      $this->setError($dbCronjobConfig->getError());
      return false;
    }
    $count = array();
    $items = array();
    // bestehende Eintraege auflisten
    foreach ($config as $entry) {
      $id = $entry[dbCronjobConfig::FIELD_ID];
      $count[] = $id;
      $value = ($entry[dbCronjobConfig::FIELD_TYPE] == dbCronjobConfig::TYPE_LIST) ? $dbCronjobConfig->getValue($entry[dbCronjobConfig::FIELD_NAME])
          : $entry[dbCronjobConfig::FIELD_VALUE];
      if (isset($_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id]))
        $value = $_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id];
      $value = str_replace('"', '&quot;', stripslashes($value));
      $items[] = array(
          'id' => $id,
          'identifier' => $entry[dbCronjobConfig::FIELD_LABEL],
          'value' => $value,
          'name' => sprintf('%s_%s', dbCronjobConfig::FIELD_VALUE, $id),
          'description' => $entry[dbCronjobConfig::FIELD_HINT],
          'type' => $entry[dbCronjobConfig::FIELD_TYPE],
          'field' => $entry[dbCronjobConfig::FIELD_NAME]);
    }
    $data = array(
        'form' => array('name' => 'cronjob_cfg', 'action' => $this->page_link),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_CONFIG_CHECK),
        'items' => array(
            'name' => self::REQUEST_ITEMS,
            'value' => implode(",", $count)),
        'message' => array(
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'items' => $items,);
    return $this->getTemplate('config.lte', $data);
  } // dlgConfig()

  /**
   * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
   * und aktualisiert die entsprechenden Datensaetze.
   *
   * @return STR DIALOG dlgConfig()
   */
  protected function checkConfig() {
    global $dbCronjobConfig;

    $message = '';
    // ueberpruefen, ob ein Eintrag geaendert wurde
    if ((isset($_REQUEST[self::REQUEST_ITEMS])) && (!empty($_REQUEST[self::REQUEST_ITEMS]))) {
      $ids = explode(",", $_REQUEST[self::REQUEST_ITEMS]);
      foreach ($ids as $id) {
        if (isset($_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id])) {
          $value = $_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id];
          $where = array();
          $where[dbCronjobConfig::FIELD_ID] = $id;
          $config = array();
          if (!$dbCronjobConfig->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobConfig->getError()));
            return false;
          }
          if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n('Error reading the configuration record with the <b>ID {{ id }}</b>.', array(
                'id' => $id))));
            return false;
          }
          $config = $config[0];
          if ($config[dbCronjobConfig::FIELD_VALUE] != $value) {
            // Wert wurde geaendert
            if (!$dbCronjobConfig->setValue($value, $id) && $dbCronjobConfig->isError()) {
              $this->setError($dbCronjobConfig->getError());
              return false;
            } elseif ($dbCronjobConfig->isMessage()) {
              $message .= $dbCronjobConfig->getMessage();
            } else {
              // Datensatz wurde aktualisiert
              $message .= $this->lang->I18n('<p>The setting for <b>{{ name }}</b> was changed.</p>', array(
                  'name' => $config[dbCronjobConfig::FIELD_NAME]));
            }
          }
          unset($_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id]);
        }
      }
    }
    $this->setMessage($message);
    return $this->dlgConfig();
  } // checkConfig()

  /**
   * Dialog for create or edit a Cronjob
   *
   * @return boolean|Ambigous <boolean, string, mixed>
   */
  protected function dlgEdit() {
    global $cronjobInterface;

    $id = isset($_REQUEST[cronjobInterface::CRONJOB_ID]) ? (int) $_REQUEST[cronjobInterface::CRONJOB_ID] : -1;
    if ($id < 1) {
      // create a new cronjob
      $fields = $cronjobInterface->getCronjobFieldArray();
      $fields[cronjobInterface::CRONJOB_ID] = -1;
    } else {
      $fields = array();
      if (!$cronjobInterface->getCronjob($id, $fields)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
        return false;
      }
      if (count($fields) < 1) {
      	$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, 
      			$this->lang->I18n('The cronjob with the ID {{ id }} does not exists!',
      					array('id' => $id))));
      	return false;
      }
    }
echo "<pre>";
print_R($fields);
echo "</pre>";    
    // walk through the fields and check for $_REQUESTs
    $cronjobInterface->checkCronjobRequests($fields);

    $items = array();
    $items['cronjob'] = array(
        'label' => $this->lang->I18n('LABEL_CRONJOB'),
        'hint' => $this->lang->I18n('HINT_CRONJOB'));

    // walk through the fields and build the $items array for the template
    foreach ($fields as $key => $value) {

      if (isset($_REQUEST[$key]))
        $value = $_REQUEST[$key];

      $items[$key] = array('name' => $key, 'value' => $value);

      switch ($key) :
      case cronjobinterface::CRONJOB_MINUTE:
      case cronjobInterface::CRONJOB_HOUR:
      case cronjobInterface::CRONJOB_DAY_OF_MONTH:
      case cronjobInterface::CRONJOB_DAY_OF_WEEK:
      case cronjobInterface::CRONJOB_MONTH:
      case cronjobInterface::CRONJOB_STATUS:
      // ENUM() fields
        $entries = array();
        if (!$cronjobInterface->enumColumn2array($key, $entries)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
          return false;
        }
        $items[$key]['options'] = $entries;
        break;
      case cronjobInterface::CRONJOB_LAST_RUN:
      case cronjobInterface::CRONJOB_NEXT_RUN:
      case cronjobInterface::CRONJOB_TIMESTAMP:
        if ($value == '0000-00-00 00:00:00') {
          $items[$key]['formatted'] = '';
        } else {
          $items[$key]['formatted'] = date(CFG_DATETIME_STR, strtotime($value));
        }
      default:
        break;
      endswitch;
    }

    $data = array(
        'form' => array('name' => 'cronjob_edit', 'action' => $this->page_link),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_EDIT_CHECK),
        'message' => array(
            'active' => (int) $this->isMessage(),
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'fields' => $items,
        'img_url' => $this->img_url,);
echo "<pre>";
print_r($data);
echo "</pre>";    
    return $this->getTemplate('cronjob.lte', $data);
  } // dlgEdit()

  /**
   * Check the user input of the dlgEdit() and create or update a cronjob record
   *
   * @return boolean|string
   */
  protected function checkEdit() {
    global $cronjobInterface;

    $checked = true;
    // check if each cronjob value isset
    if (!isset($_REQUEST[cronjobInterface::CRONJOB_HOUR]) || !isset($_REQUEST[cronjobInterface::CRONJOB_MINUTE]) || !isset($_REQUEST[cronjobInterface::CRONJOB_DAY_OF_MONTH]) || !isset($_REQUEST[cronjobInterface::CRONJOB_DAY_OF_WEEK]) || !isset($_REQUEST[cronjobInterface::CRONJOB_MONTH]))
      $checked = false;

    if (!$checked) {
      $this->setMessage($this->lang->I18n('At minimum please set a value for hour, minute, day, weekday and month for definition of the cronjob!'));
      return $this->dlgEdit();
    }
    // check the cronjob name
    if (empty($_REQUEST[cronjobInterface::CRONJOB_NAME])) {
      $this->setMessage($this->lang->I18n('Please define a unique name for the cronjob!'));
      return $this->dlgEdit();
    }
    $minimum_name_length = $cronjobInterface->getCronjobConfigValue(cronjobInterface::CFG_CRONJOB_NAME_MINIMUM_LENGTH);
    if (strlen(trim($_REQUEST[cronjobInterface::CRONJOB_NAME])) < $minimum_name_length) {
      $this->setMessage($this->lang->I18n('The cronjob name must be at minimum {{ length }} characters long.', array(
          'length' => $minimum_name_length)));
      return $this->dlgEdit();
    }
    // check the cronjob command
    if (empty($_REQUEST[cronjobInterface::CRONJOB_COMMAND])) {
      $this->setMessage($this->lang->I18n('Please define the command to execute by the cronjob!'));
      return $this->dlgEdit();
    }

    if ($_REQUEST[cronjobInterface::CRONJOB_ID] > 0) {
      // already existing cronjob
      $cronjob = array();
      if (false === ($cronjobInterface->getCronjob($_REQUEST[cronjobInterface::CRONJOB_ID], $cronjob))) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
        return false;
      }
      // read the $_REQUEST's into an array
      $new_cronjob = array();
      $cronjobInterface->checkCronjobRequests($new_cronjob);

      if ($cronjob[cronjobInterface::CRONJOB_NAME] != $new_cronjob[cronjobInterface::CRONJOB_NAME]) {
        // cronjob name changed - check if the name is unique!
        if (!$cronjobInterface->checkCronjobNameIsUnique($new_cronjob[cronjobInterface::CRONJOB_NAME], $cronjob[cronjobInterface::CRONJOB_ID])) {
          if ($cronjobInterface->isError()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
            return false;
          }
          $this->setMessage($this->lang->I18n('The cronjob name {{ name }} is not unique, please select another name!', array(
              'name' => $cronjob[cronjobInterface::CRONJOB_NAME])));
          return $this->dlgEdit();
        }
      }
    } else {
      // create a new cronjob
      $conjob = array();
      $cronjobInterface->checkCronjobRequests($cronjob);
      // check the cronjob name - must be unique!
      if (!$cronjobInterface->checkCronjobNameIsUnique($cronjob[cronjobInterface::CRONJOB_NAME])) {
        if ($cronjobInterface->isError()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
          return false;
        }
        $this->setMessage($this->lang->I18n('The cronjob name {{ name }} is not unique, please select another name!', array(
            'name' => $cronjob[cronjobInterface::CRONJOB_NAME])));
        return $this->dlgEdit();
      }
    }

    // ok - save the cronjob
    $data = array();
    foreach ($cronjobInterface->getCronjobFieldArray() as $key => $value) {
      if (isset($_REQUEST[$key])) {
        switch ($key) {
        case cronjobinterface::CRONJOB_MINUTE:
        case cronjobInterface::CRONJOB_HOUR:
        case cronjobInterface::CRONJOB_DAY_OF_MONTH:
        case cronjobInterface::CRONJOB_DAY_OF_WEEK:
        case cronjobInterface::CRONJOB_MONTH:
        case cronjobInterface::CRONJOB_STATUS:
        // implode array values
          if (is_array($_REQUEST[$key])) {
          	$data[$key] = implode(',', $_REQUEST[$key]);
          }
          else {
          	$data[$key] = '';
          }
          break;
        case cronjobInterface::CRONJOB_LAST_RUN:
        case cronjobInterface::CRONJOB_NEXT_RUN:
        case cronjobInterface::CRONJOB_TIMESTAMP:
        // nothing to do ...
          break;
        case cronjobInterface::CRONJOB_NAME:
        // convert to a save name
          $name = trim($_REQUEST[$key]);
          $name = page_filename($name);
          $data[$key] = $name;
          $_REQUEST[$key] = $name;
          break;
        default:
        // simply save the value
          $data[$key] = $_REQUEST[$key];
          break;
        }
      }
    }

    if ($_REQUEST[cronjobInterface::CRONJOB_ID] > 0) {
      // update existing cronjob
      $id = $_REQUEST[cronjobInterface::CRONJOB_ID];
      if (!$cronjobInterface->updateCronjob($id, $data)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
        return false;
      }
      $this->setMessage($this->lang->I18n('The cronjob with the ID {{ id }} was successfull updated.', array(
          'id' => $id)));
    } else {
      // insert a new cronjob
      $id = -1;
      if (!$cronjobInterface->insertCronjob($data, $id)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
        return false;
      }
      $this->setMessage($this->lang->I18n('The cronjob with the ID {{ id }} was successfull inserted', array(
          'id' => $id)));
    }
    foreach ($cronjobInterface->getCronjobFieldArray() as $key => $value) {
      if (isset($_REQUEST[$key]))
        unset($_REQUEST[$key]);
    }
    $_REQUEST[cronjobInterface::CRONJOB_ID] = $id;
    return $this->dlgEdit();
  } // checkEdit()

  protected function dlgCronjobs() {
    return __METHOD__;
  } // dlgCronjob()

} // class cronjobBackend
