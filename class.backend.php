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

require_once(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class.interface.php');

if (!class_exists('Dwoo'))
  require_once LEPTON_PATH.'/modules/dwoo/include.php';

// initialize the template engine
global $parser;
if (!is_object($parser)) {
  $cache_path = LEPTON_PATH.'/temp/cache';
  if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
  $compiled_path = LEPTON_PATH.'/temp/compiled';
  if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
  $parser = new Dwoo($compiled_path, $cache_path);
}
// load extensions for the template engine
$loader = $parser->getLoader();
$loader->addDirectory(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/plugins/');

require_once LEPTON_PATH.'/modules/manufaktur_config/class.dialog.php';

// load the standard functions of LEPTON framework
require_once LEPTON_PATH . '/framework/functions.php';

class cronjobBackend {

  const REQUEST_ACTION = 'act';
  const REQUEST_ITEMS = 'its';

  const ACTION_ABOUT = 'abt';
  const ACTION_CONFIG = 'cfg';
  const ACTION_DEFAULT = 'def';
  const ACTION_CRONJOBS = 'cron';
  const ACTION_EDIT = 'edt';
  const ACTION_EDIT_CHECK = 'edtc';
  const ACTION_PROTOCOL = 'prt';

  private static $page_link = '';
  private static $img_url = '';
  private static $error = '';
  private static $message = '';

  protected $lang = NULL;
  protected static $tab_navigation_array = null;

  public function __construct() {
    global $I18n;
    self::$page_link = ADMIN_URL . '/admintools/tool.php?tool=kit_cronjob';
    self::$img_url = LEPTON_URL . '/modules/' . basename(dirname(__FILE__)) . '/images/';
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $I18n;
    // don't translate the Tab Strings here - this will be done in the template!
    self::$tab_navigation_array = array(
        self::ACTION_CRONJOBS => 'Cronjob list',
        self::ACTION_EDIT => 'Cronjob edit',
        self::ACTION_PROTOCOL => 'Protocol',
        self::ACTION_CONFIG => 'Settings',
        self::ACTION_ABOUT => 'About');
  } // __construct()

  /**
   * Set self::$error to $error
   *
   * @param $error string
   */
  protected function setError($error) {
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

  /**
   * Set self::$message to $message
   *
   * @param $message string
   */
  protected function setMessage($message) {
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
   * Return Version of Module
   *
   * @return float
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
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
              array(
                  'template' => basename($load_template),
                  'error' => $e->getMessage())
              )));
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Verhindert XSS Cross Site Scripting
   *
   * @param $_REQUEST reference array
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
   * @return string result dialog or message
   */
  public function action() {
    $html_allowed = array();
    foreach ($_REQUEST as $key => $value) {
      // ignore config values!
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }

    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DEFAULT;

    switch ($action) :
    case self::ACTION_CONFIG:
      $this->show(self::ACTION_CONFIG, $this->dlgConfig());
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
    case self::ACTION_PROTOCOL:
      $this->show(self::ACTION_PROTOCOL, $this->dlgProtocol());
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
   * @param string $action aktives Navigationselement
   * @param string $content Inhalt
   *
   * @return echo result
   */
  protected function show($action, $content) {
    $navigation = array();
    foreach (self::$tab_navigation_array as $key => $value) {
      $navigation[] = array(
          'active' => ($key == $action) ? 1 : 0,
          'url' => sprintf('%s&%s', self::$page_link, http_build_query(array(
              self::REQUEST_ACTION => $key))),
          'text' => $value);
    }
    $data = array(
        'LEPTON_URL' => LEPTON_URL,
        'IMG_URL' => self::$img_url,
        'navigation' => $navigation,
        'error' => ($this->isError()) ? 1 : 0,
        'content' => ($this->isError()) ? $this->getError() : $content);
    echo $this->getTemplate('body.lte', $data);
  } // show()

  /**
   * Information about kitIdea
   *
   * @return string dialog
   */
  protected function dlgAbout() {
    $notes = file_get_contents(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/CHANGELOG');
    $use_markdown = 0;
    if (file_exists(LEPTON_PATH.'/modules/lib_markdown/standard/markdown.php')) {
      require_once LEPTON_PATH.'/modules/lib_markdown/standard/markdown.php';
      $notes = Markdown($notes);
      $use_markdown = 1;
    }
    $data = array(
        'img_url' => self::$img_url,
        'release' => array(
            'number' => sprintf('%01.2f', $this->getVersion()),
            'notes' => $notes,
            'use_markdown' => $use_markdown
            )
        );
        //_notes' => file_get_contents(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt'),);
    return $this->getTemplate('about.lte', $data);
  } // dlgAbout()

  /**
   * kitCronjob settings
   *
   * @return string dialog
   */
  protected function dlgConfig() {
    // set the link to call the dlgConfig()
    $link = sprintf('%s&%s',
        self::$page_link,
        http_build_query(array(
            self::REQUEST_ACTION => self::ACTION_CONFIG
            )));
    // set the abort link
    $abort = sprintf('%s&%s',
        self::$page_link,
        http_build_query(array(
            self::REQUEST_ACTION => self::ACTION_DEFAULT
            )));
    // exec manufakturConfig
    $dialog = new manufakturConfigDialog('kit_cronjob', 'kitCronjob', $link, $abort);
    return $dialog->action();
  } // dlgConfig()

  /**
   * Dialog for create or edit a Cronjob
   *
   * @return boolean|Ambigous <boolean, string, mixed>
   */
  protected function dlgEdit() {
    global $cronjobInterface;

    $id = isset($_REQUEST[cronjobInterface::CRONJOB_ID]) ? (int) $_REQUEST[cronjobInterface::CRONJOB_ID]
        : -1;
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
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The cronjob with the ID {{ id }} does not exists!', array(
            'id' => $id))));
        return false;
      }
    }
    // walk through the fields and check for $_REQUESTs
    $cronjobInterface->checkCronjobRequests($fields);

    $items = array();
    // walk through the fields and build the $items array for the template
    foreach ($fields as $key => $value) {

      if (isset($_REQUEST[$key]))
        $value = $_REQUEST[$key];

      $items[$key] = array('name' => $key, 'value' => $value);

      switch ($key) {
      case cronjobInterface::CRONJOB_STATUS:
        // ENUM() fields
        $entries = array('ACTIVE','LOCKED','DELETED');
        $items[$key]['options'] = $entries;
        break;
      case cronjobinterface::CRONJOB_MINUTE:
      case cronjobInterface::CRONJOB_HOUR:
      case cronjobInterface::CRONJOB_DAY_OF_MONTH:
      case cronjobInterface::CRONJOB_DAY_OF_WEEK:
      case cronjobInterface::CRONJOB_MONTH:
        $entries = array();
        if (!$cronjobInterface->fieldDefaults2array($key, $entries)) {
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
      }
    }

    $data = array(
        'form' => array('name' => 'cronjob_edit', 'action' => self::$page_link),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_EDIT_CHECK),
        'message' => array(
            'active' => (int) $this->isMessage(),
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'fields' => $items,
        'img_url' => self::$img_url,);
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
      $this->setMessage($this->lang->translate('At minimum please set a value for hour, minute, day, weekday and month for definition of the cronjob!'));
      return $this->dlgEdit();
    }
    // check the cronjob name
    if (empty($_REQUEST[cronjobInterface::CRONJOB_NAME])) {
      $this->setMessage($this->lang->translate('Please define a unique name for the cronjob!'));
      return $this->dlgEdit();
    }
    $minimum_name_length = $cronjobInterface->getCronjobConfigValue(cronjobInterface::CFG_CRONJOB_NAME_MINIMUM_LENGTH);
    if (strlen(trim($_REQUEST[cronjobInterface::CRONJOB_NAME])) < $minimum_name_length) {
      $this->setMessage($this->lang->translate('The cronjob name must be at minimum {{ length }} characters long.', array(
          'length' => $minimum_name_length)));
      return $this->dlgEdit();
    }
    // check the cronjob command
    if (empty($_REQUEST[cronjobInterface::CRONJOB_COMMAND])) {
      $this->setMessage($this->lang->translate('Please define the command to execute by the cronjob!'));
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
          $this->setMessage($this->lang->translate('The cronjob name {{ name }} is not unique, please select another name!', array(
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
        $this->setMessage($this->lang->translate('The cronjob name {{ name }} is not unique, please select another name!', array(
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
          // implode array values
          if (is_array($_REQUEST[$key])) {
            $data[$key] = implode(',', $_REQUEST[$key]);
          } else {
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
      $this->setMessage($this->lang->translate('The cronjob with the ID {{ id }} was successfull updated.', array(
          'id' => $id)));
    } else {
      // insert a new cronjob
      $id = -1;
      if (!$cronjobInterface->insertCronjob($data, $id)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
        return false;
      }
      $this->setMessage($this->lang->translate('The cronjob with the ID {{ id }} was successfull inserted', array(
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
  	global $cronjobInterface;

  	$cronjobs = array();
  	if (!$cronjobInterface->getCronjobsByStatus($cronjobs)) {
  		$this->setError(sprintf('[%s .- %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
  		return false;
  	}

  	$items = array();
  	$options_minute = array();
  	$options_hour = array();
  	$options_day_of_month = array();
  	$options_day_of_week = array();
  	$options_month = array();
  	$cronjobInterface->fieldDefaults2array(cronjobInterface::CRONJOB_MINUTE, $options_minute);
  	$cronjobInterface->fieldDefaults2array(cronjobInterface::CRONJOB_HOUR, $options_hour);
  	$cronjobInterface->fieldDefaults2array(cronjobInterface::CRONJOB_DAY_OF_MONTH, $options_day_of_month);
  	$cronjobInterface->fieldDefaults2array(cronjobInterface::CRONJOB_DAY_OF_WEEK, $options_day_of_week);
  	$cronjobInterface->fieldDefaults2array(cronjobInterface::CRONJOB_MONTH, $options_month);

  	foreach ($cronjobs as $cronjob) {
  		$items[$cronjob[cronjobInterface::CRONJOB_ID]] = array(
  				'id' => array(
  						'name' => cronjobInterface::CRONJOB_ID,
  						'value' => $cronjob[cronjobInterface::CRONJOB_ID]
  						),
  				'name' => array(
  						'name' => cronjobInterface::CRONJOB_NAME,
  						'value' => $cronjob[cronjobInterface::CRONJOB_NAME]
  						),
  				'description' => array(
  						'name' => cronjobInterface::CRONJOB_DESCRIPTION,
  						'value' => $cronjob[cronjobInterface::CRONJOB_DESCRIPTION]
  						),
  				'minutes' => array(
  						'name' => cronjobInterface::CRONJOB_MINUTE,
  						'values' => $cronjob[cronjobInterface::CRONJOB_MINUTE],
  						'options' => $options_minute
  						),
  				'hours' => array(
  						'name' => cronjobInterface::CRONJOB_HOUR,
  						'values' => $cronjob[cronjobInterface::CRONJOB_HOUR],
  						'options' => $options_hour
  				    ),
  				'days_of_month' => array(
  						'name' => cronjobInterface::CRONJOB_DAY_OF_MONTH,
  						'values' => $cronjob[cronjobInterface::CRONJOB_DAY_OF_MONTH],
  						'options' => $options_day_of_month
  				     ),
  				'days_of_week' => array(
  						'name' => cronjobInterface::CRONJOB_DAY_OF_WEEK,
  						'values' => $cronjob[cronjobInterface::CRONJOB_DAY_OF_WEEK],
  						'options' => $options_day_of_week
  				    ),
  				'months' => array(
  						'name' => cronjobInterface::CRONJOB_MONTH,
  						'values' => $cronjob[cronjobInterface::CRONJOB_MONTH],
  						'options' => $options_month
  				    ),
  				'command' => array(
  						'name' => cronjobInterface::CRONJOB_COMMAND,
  						'value' => $cronjob[cronjobInterface::CRONJOB_COMMAND],
  				    ),
  				'last_run' => array(
  						'name' => cronjobInterface::CRONJOB_LAST_RUN,
  						'value' => $cronjob[cronjobInterface::CRONJOB_LAST_RUN],
  						'formatted' => ($cronjob[cronjobInterface::CRONJOB_LAST_RUN] != '0000-00-00 00:00:00') ? date(CFG_DATETIME_STR, strtotime($cronjob[cronjobInterface::CRONJOB_LAST_RUN])) : ''
  				    ),
  				'next_run' => array(
  						'name' => cronjobInterface::CRONJOB_NEXT_RUN,
  						'value' => $cronjob[cronjobInterface::CRONJOB_NEXT_RUN],
  						'formatted' => ($cronjob[cronjobInterface::CRONJOB_NEXT_RUN] != '0000-00-00 00:00:00') ? date(CFG_DATETIME_STR, strtotime($cronjob[cronjobInterface::CRONJOB_NEXT_RUN])) : ''
  				    ),
  				'last_status' => array(
  						'name' => cronjobInterface::CRONJOB_LAST_STATUS,
  						'value' => $cronjob[cronjobInterface::CRONJOB_LAST_STATUS]
  				    ),
  				'status' => array(
  						'name' => cronjobInterface::CRONJOB_STATUS,
  						'value' => $cronjob[cronjobInterface::CRONJOB_STATUS],
  						'options' => array('ACTIVE','LOCKED','DELETED') //$cronjobInterface->enumColumn2array(cronjobInterface::CRONJOB_STATUS)
  				    ),
  				'timestamp' => array(
  						'name' => cronjobInterface::CRONJOB_TIMESTAMP,
  						'value' => $cronjob[cronjobInterface::CRONJOB_TIMESTAMP],
  						'formatted' => date(CFG_DATETIME_STR, strtotime($cronjob[cronjobInterface::CRONJOB_TIMESTAMP]))
  				    ),
  				'action' => array(
  						'edit' => sprintf('%s&%s', self::$page_link, http_build_query(array(
  								self::REQUEST_ACTION => self::ACTION_EDIT,
  								cronjobInterface::CRONJOB_ID => $cronjob[cronjobInterface::CRONJOB_ID]
  								))),
  						)
  				);
  	}

  	$data = array(
  	    'message' => array(
  	        'active' => (int) $this->isMessage(),
  	        'text' => $this->getMessage()
  	        ),
  			'cronjob' => array(
  			    'items' => $items,
  			    'count' => count($items)
  			    )
  			);
  	return $this->getTemplate('list.lte', $data);
  } // dlgCronjob()

  /**
   * Show the kitCronjob protocol
   *
   * @return string dialog
   */
  protected function dlgProtocol() {
    global $cronjobInterface;

    if (!$cronjobInterface->shrinkProtocol()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
      return false;
    }
    // limit the entries of the protocol
    $limit = $cronjobInterface->getCronjobConfigValue(cronjobInterface::CFG_LOG_LIST_LIMIT);
    // suppress no-load entries in the protocol
    $show_no_load = $cronjobInterface->getCronjobConfigValue(cronjobInterface::CFG_LOG_SHOW_NO_LOAD);

    $protocol = array();
    if (!$cronjobInterface->getCronjobProtocol($protocol, $limit, $show_no_load)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $cronjobInterface->getError()));
      return false;
    }
    if (count($protocol) > 0) {
      $last_call = $protocol[0]['log_timestamp'];
    }
    else {
      $last_call = 0;
    }
    $key = $cronjobInterface->getCronjobConfigValue(cronjobInterface::CFG_CRONJOB_KEY);

    $data = array(
        'cronjob' => array(
            'url' => sprintf('%s/modules/kit_cronjob/cronjob.php?key=%s', LEPTON_URL, $key)
            ),
        'protocol' => array(
            'last_call' => $last_call,
            'count' => count($protocol),
            'entries' => $protocol,
            ),
        'message' => array(
  	        'active' => (int) $this->isMessage(),
  	        'text' => $this->getMessage()
  	        ),
  			);
    return $this->getTemplate('protocol.lte', $data);
  } // dlgProtocol()

} // class cronjobBackend
