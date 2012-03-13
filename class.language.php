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

// load language depending onfiguration
if (!file_exists(WB_PATH.'/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php')) {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.cfg.php');
} else {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');
}
if (! file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
  if (!defined('KIT_CRONJOB_LANGUAGE')) define('KIT_CRONJOB_LANGUAGE', 'DE'); // important: language flag is used by template selection
} else {
  if (!defined('KIT_CRONJOB_LANGUAGE')) define('KIT_CRONJOB_LANGUAGE', LANGUAGE);
}

if (!class_exists('dbconnectle')) require_once WB_PATH.'/modules/dbconnect_le/include.php';

class dbManufakturI18n extends dbConnectLE {

  const FIELD_ID = 'i18n_id';
  const FIELD_KEY = 'i18n_key';
  const FIELD_LANGUAGE = 'i18n_language';
  const FIELD_TRANSLATION = 'i18n_translation';
  const FIELD_DESCRIPTION = 'i18n_description';
  const FIELD_USAGE = 'i18n_usage';
  const FIELD_STATUS = 'i18n_status';
  const FIELD_AUTHOR = 'i18n_author';
  const FIELD_LAST_SYNC = 'i18n_last_sync';
  const FIELD_TIMESTAMP = 'i18n_timestamp';

  const USAGE_TEXT = 'TEXT';
  const USAGE_MESSAGE = 'MESSAGE';
  const USAGE_ERROR = 'ERROR';
  const USAGE_HINT = 'HINT';
  const USAGE_LABEL = 'LABEL';
  const USAGE_BUTTON = 'BUTTON';

  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_LOCKED = 'LOCKED';
  const STATUS_BACKUP = 'BACKUP';
  const STATUS_IGNORE = 'IGNORE';

  const AUTHOR_UNKNOWN = '- unknown -';

  private $create_table = false;

  public function __construct($create_table=false) {
    $this->create_table = $create_table;
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    parent::__construct();
    $this->setTableName('mod_manufaktur_i18n');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_KEY, "TEXT", false, false, true);
    $this->addFieldDefinition(self::FIELD_LANGUAGE, "VARCHAR(2) NOT NULL DEFAULT 'EN'");
    $this->addFieldDefinition(self::FIELD_TRANSLATION, "TEXT", false, false, true);
    $this->addFieldDefinition(self::FIELD_DESCRIPTION, "TEXT");
    $this->addFieldDefinition(self::FIELD_USAGE, "ENUM('TEXT','MESSAGE','ERROR','HINT','LABEL','BUTTON') NOT NULL DEFAULT 'TEXT'");
    $this->addFieldDefinition(self::FIELD_STATUS, "ENUM('ACTIVE', 'LOCKED', 'BACKUP', 'IGNORE') NOT NULL DEFAULT 'ACTIVE'");
    $this->addFieldDefinition(self::FIELD_AUTHOR, "VARCHAR(64) NOT NULL DEFAULT '- unknown -'");
    $this->addFieldDefinition(self::FIELD_LAST_SYNC, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->setIndexFields(array(self::FIELD_LANGUAGE));
    $this->setAllowedHTMLtags('<a><abbr><acronym><code><b><div><em><i><label><li><p><pre><span><strong><ul>');
    $this->checkFieldDefinitions();
    // Tabelle erstellen
    if ($this->create_table) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }


  } // __construct()

} // class dbManufakturI18n

class dbManufakturI18nSources extends dbConnectLE {

  const FIELD_ID = 'src_id';
  const FIELD_I18N_ID = 'i18n_id';
  const FIELD_FILE = 'src_file';
  const FIELD_DIRECTORY = 'src_directory';
  const FIELD_MODULE = 'src_module';
  const FIELD_LINE = 'src_line';
  const FIELD_TIMESTAMP = 'src_timestamp';

  private $create_table = false;

  public function __construct($create_table=false) {
    $this->create_table = $create_table;
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    parent::__construct();
    $this->setTableName('mod_manufaktur_i18n_src');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_I18N_ID, "INT(11) NOT NULL DEFAULT '-1'");
    $this->addFieldDefinition(self::FIELD_FILE, "VARCHAR(64) NOT NULL DEFAULT ''");
    $this->addFieldDefinition(self::FIELD_DIRECTORY, "TEXT");
    $this->addFieldDefinition(self::FIELD_MODULE, "VARCHAR(64) NOT NULL DEFAULT ''");
    $this->addFieldDefinition(self::FIELD_LINE, "INT(11) NOT NULL DEFAULT '-1'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->setIndexFields(array(self::FIELD_I18N_ID));
    $this->checkFieldDefinitions();
    // Tabelle erstellen
    if ($this->create_table) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }
  } // __construct()

} // class dbManufakturI18nSources

global $dbI18n;
if (!is_object($dbI18n)) $dbI18n = new dbManufakturI18n();

global $dbI18nSrc;
if (!is_object($dbI18nSrc)) $dbI18nSrc = new dbManufakturI18nSources();

class manufakturI18n {

  private $error = '';
  private $message = '';

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
   * Iterate directory tree very efficient
   * Function postet from donovan.pp@gmail.com at
   * http://www.php.net/manual/de/function.scandir.php
   *
   * @param string $directory
   * @return array - directoryTree
   */
  public static function getDirectoryTree($directory, $extensions_only=NULL) {
    if (substr($directory, -1) == "/") $directory = substr($directory, 0, -1);
    $path = array();
    $stack = array();
    $stack[] = $directory;
    while ($stack) {
      $thisdir = array_pop($stack);
      if (false !== ($dircont = scandir($thisdir))) {
      		$i=0;
      		while (isset($dircont[$i])) {
      		  if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
      		    $current_file = "{$thisdir}/{$dircont[$i]}";
      		    if (is_file($current_file)) {
      		      if ($extensions_only == NULL) {
      		        $path[] = "{$thisdir}/{$dircont[$i]}";
      		      }
      		      else {
      		        $path_info = pathinfo("{$thisdir}/{$dircont[$i]}");
      		        if (isset($path_info['extension']) && in_array($path_info['extension'], $extensions_only))
      		          $path[] = "{$thisdir}/{$dircont[$i]}";
      		      }
      		    }
      		    elseif (is_dir($current_file)) {
      		      $stack[] = $current_file;
      		    }
      		  }
      		  $i++;
      		}
      }
    }
    return $path;
  } // getDirectoryTree()


  public function scanDirectory() {
    global $dbI18n;
    global $dbI18nSrc;

    $check = self::getDirectoryTree(WB_PATH.'/modules', array('lte','php'));
    $translation = array();
    foreach ($check as $file_path) {
      $lines = file($file_path);
      $line_number = 0;
      $path_info = pathinfo($file_path);
      $module_directory = substr($path_info['dirname'], strlen(WB_PATH));
      $module_name = substr($path_info['dirname'], strlen(WB_PATH.'/modules/'));
      if (false !== strpos($module_name, DIRECTORY_SEPARATOR))
        $module_name = substr($module_name, 0, strpos($module_name, DIRECTORY_SEPARATOR));
      foreach ($lines as $line) {
        $line_number++;
        $matches = array();
        preg_match_all('/translate(.?)\((.?[\'"](.*?)[\'"])/', $line, $matches);
        if (isset($matches[3])) {
          foreach ($matches[3] as $item) {
            $translation[] = array(
                'key' => trim($item),
                'file' => $path_info['basename'],
                'directory' => $module_directory,
                'module' => $module_name,
                'line' => $line_number
                );
          }
        }
      }
    }

    foreach($translation as $entry) {
      $SQL = sprintf("SELECT `%s`FROM %s WHERE `%s`='%s'",
          dbManufakturI18n::FIELD_ID,
          $dbI18n->getTableName(),
          dbManufakturI18n::FIELD_KEY,
          addslashes($entry['key'])
          );
      $result = array();
      if (!$dbI18n->sqlExec($SQL, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
        return false;
      }
      if (count($result) > 0) {
        // entry already exists, keep only the source usage
        $data = array(
            dbManufakturI18nSources::FIELD_I18N_ID => $result[0][dbManufakturI18n::FIELD_ID],
            dbManufakturI18nSources::FIELD_DIRECTORY => $entry['directory'],
            dbManufakturI18nSources::FIELD_FILE => $entry['file'],
            dbManufakturI18nSources::FIELD_LINE => $entry['line'],
            dbManufakturI18nSources::FIELD_MODULE => $entry['module']
            );
        if (!$dbI18nSrc->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18nSrc->getError()));
          return false;
        }
      }
      else {
        // create a new entry
        $data = array(
            dbManufakturI18n::FIELD_AUTHOR => (isset($_SESSION['DISPLAY_NAME'])) ? $_SESSION['DISPLAY_NAME'] : dbManufakturI18n::AUTHOR_UNKNOWN,
            dbManufakturI18n::FIELD_DESCRIPTION => '',
            dbManufakturI18n::FIELD_KEY => $entry['key'],
            dbManufakturI18n::FIELD_LANGUAGE => 'EN',
            dbManufakturI18n::FIELD_LAST_SYNC => date("Y-m-d H:i:s", time()),
            dbManufakturI18n::FIELD_STATUS => dbManufakturI18n::STATUS_ACTIVE,
            dbManufakturI18n::FIELD_TRANSLATION => $entry['key'],
            dbManufakturI18n::FIELD_USAGE => dbManufakturI18n::USAGE_TEXT
            );
        $id = -1;
        if (!$dbI18n->sqlInsertRecord($data, $id)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
          return false;
        }
        // ok - add source usage...
        $data = array(
            dbManufakturI18nSources::FIELD_I18N_ID => $id,
            dbManufakturI18nSources::FIELD_DIRECTORY => $entry['directory'],
            dbManufakturI18nSources::FIELD_FILE => $entry['file'],
            dbManufakturI18nSources::FIELD_LINE => $entry['line'],
            dbManufakturI18nSources::FIELD_MODULE => $entry['module']
        );
        if (!$dbI18nSrc->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18nSrc->getError()));
          return false;
        }
      }
    }
    echo "do...";
    if (!$dbI18n->sqlAlterTableChangeField(dbManufakturI18n::FIELD_STATUS, dbManufakturI18n::FIELD_STATUS, "ENUM('ACTIVE', 'LOCKED', 'BACKUP', 'IGNORE') NOT NULL DEFAULT 'ACTIVE'")) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
      return false;
    }
    return true;
  } // scanDirectory()

} // class manufakturLanguage