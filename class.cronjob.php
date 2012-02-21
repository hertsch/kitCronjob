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

class dbCronjobConfig extends dbConnectLE {
  
  const FIELD_ID = 'cfg_id';
  const FIELD_NAME = 'cfg_name';
  const FIELD_TYPE = 'cfg_type';
  const FIELD_VALUE = 'cfg_value';
  const FIELD_LABEL = 'cfg_label';
  const FIELD_HINT = 'cfg_hint';
  const FIELD_STATUS = 'cfg_status';
  const FIELD_TIMESTAMP = 'cfg_timestamp';
  
  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_DELETED = 'DELETED';
  const STATUS_LOCKED = 'LOCKED';
  
  const TYPE_UNDEFINED = 'UNDEFINED';
  const TYPE_ARRAY = 'ARRAY';
  const TYPE_BOOLEAN = 'BOOLEAN';
  const TYPE_EMAIL = 'EMAIL';
  const TYPE_FLOAT = 'FLOAT';
  const TYPE_INTEGER = 'INTEGER';
  const TYPE_LIST = 'LIST';
  const TYPE_PATH = 'PATH';
  const TYPE_STRING = 'STRING';
  const TYPE_URL = 'URL';
  
  private $createTable = false;
  private $message = '';
  
  const CFG_CRONJOB_KEY = 'cfg_cronjob_key';
  const CFG_CRONJOB_ACTIVE = 'cfg_cronjob_active';
  
  public $config_array = array(
      array(        
          'label' => 'LABEL_CFG_CRONJOB_KEY', 
          'name' => self::CFG_CRONJOB_KEY, 
          'type' => self::TYPE_STRING, 
          'value' => '', 
          'hint' => 'HINT_CFG_CRONJOB_KEY'
      ),
      array(
          'label' => 'LABEL_CFG_CRONJOB_ACTIVE',
          'name' => self::CFG_CRONJOB_ACTIVE,
          'type' => self::TYPE_BOOLEAN,
          'value' => 1,
          'hint' => 'HINT_CFG_CRONJOB_ACTIVE'
          )
  );
  
  /**
   * Constructor
   * 
   * @param $createTable boolean         
   */
  public function __construct($createTable = false) {
    $this->createTable = $createTable;
    parent::__construct();
    $this->setTableName('mod_kit_cj_config');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_NAME, "VARCHAR(32) NOT NULL DEFAULT ''");
    $this->addFieldDefinition(self::FIELD_TYPE, "ENUM('UNDEFINED','ARRAY','BOOLEAN','EMAIL','FLOAT','INTEGER','LIST','PATH','STRING','URL') NOT NULL DEFAULT 'UNDEFINED'"); //"TINYINT UNSIGNED NOT NULL DEFAULT '" . self::TYPE_UNDEFINED . "'");
    $this->addFieldDefinition(self::FIELD_VALUE, "TEXT", false, false, true);
    $this->addFieldDefinition(self::FIELD_LABEL, "VARCHAR(64) NOT NULL DEFAULT '- undefined -'");
    $this->addFieldDefinition(self::FIELD_HINT, "TEXT");
    $this->addFieldDefinition(self::FIELD_STATUS, "ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->setIndexFields(array(self::FIELD_NAME));
    $this->setAllowedHTMLtags('<a><abbr><acronym><span>');
    $this->checkFieldDefinitions();
    // Tabelle erstellen
    if ($this->createTable) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    // Default Werte garantieren
    if ($this->sqlTableExists()) {
      $this->checkConfig();
    }
  } // __construct()
  
  /**
   * Set a message to $this->message
   * 
   * @param $message string         
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
   * Aktualisiert den Wert $new_value des Datensatz $name
   * 
   * @param $new_value string - Wert, der uebernommen werden soll
   * @param $id integer - ID des Datensatz, dessen Wert aktualisiert werden soll
   * @return boolean Ergebnis
   */
  public function setValueByName($new_value, $name) {
    $where = array();
    $where[self::FIELD_NAME] = $name;
    $config = array();
    if (!$this->sqlSelectRecord($where, $config)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
      return false;
    }
    if (count($config) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, 
          $this->lang->translate('Error: There is no record for the configuration of <b>{{ name }}</b>!', 
              array('name' => $name))));
      return false;
    }
    return $this->setValue($new_value, $config[0][self::FIELD_ID]);
  } // setValueByName()
  
  /**
   * Haengt einen Slash an das Ende des uebergebenen Strings
   * wenn das letzte Zeichen noch kein Slash ist
   * 
   * @param $path string         
   * @return string
   */
  public static function addSlash($path) {
    $path = substr($path, strlen($path) - 1, 1) == "/" ? $path : $path . "/";
    return $path;
  } // addSlash()
  
  /**
   * Wandelt einen String in einen Float Wert um.
   * Geht davon aus, dass Dezimalzahlen mit ',' und nicht mit '.'
   * eingegeben wurden.
   * 
   * @param $string string         
   * @return float
   */
  public static function str2float($string) {
    $string = str_replace('.', '', $string);
    $string = str_replace(',', '.', $string);
    $float = floatval($string);
    return $float;
  } // str2float()
  
  /**
   * convert a string into a integer value
   * 
   * @param $string string         
   * @return integer
   */
  public static function str2int($string) {
    $string = str_replace('.', '', $string);
    $string = str_replace(',', '.', $string);
    $int = intval($string);
    return $int;
  } // str2int()
  
  /**
   * Ueberprueft die uebergebene E-Mail Adresse auf logische Gueltigkeit
   * 
   * @param $email string         
   * @return boolean
   */
  public static function validateEMail($email) {
    if (preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
      return true;
    } 
    else {
      return false;
    }
  } // validateEMail()
  
  /**
   * Generate a password with the desired length
   * 
   * @param integer $length
   * @return string
   */
  public static function generatePassword($length=7) {
    $new_pass = '';
    $salt = 'abcdefghjkmnpqrstuvwxyz123456789';
    srand((double)microtime()*1000000);
    $i=0;
    while ($i <= $length) {
      $num = rand() % 33;
      $tmp = substr($salt, $num, 1);
      $new_pass = $new_pass . $tmp;
      $i++;
    }
    return $new_pass;
  } // generatePassword()
  
  
  /**
   * Aktualisiert den Wert $new_value des Datensatz $id
   * 
   * @param $new_value string - Wert, der uebernommen werden soll
   * @param $id integer - ID des Datensatz, dessen Wert aktualisiert werden soll
   * 
   * @return boolean Ergebnis
   */
  public function setValue($new_value, $id) {
    $value = '';
    $where = array();
    $where[self::FIELD_ID] = $id;
    $config = array();
    if (!$this->sqlSelectRecord($where, $config)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
      return false;
    }
    if (sizeof($config) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, 
          $this->lang->translate('Error: The record with the <b>ID {{ id }}</b> does not exists!', 
              array('id' => $id))));
      return false;
    }
    $config = $config[0];
    switch ($config[self::FIELD_TYPE]) :
      case self::TYPE_ARRAY:
        // Funktion geht davon aus, dass $value als STR uebergeben wird!!!
        $worker = explode(",", $new_value);
        $data = array();
        foreach ($worker as $item) {
          $data[] = trim($item);
        }
        $value = implode(",", $data);
        break;
      case self::TYPE_BOOLEAN :
        $value = (bool) $new_value;
        $value = (int) $value;
        break;
      case self::TYPE_EMAIL :
        if ($this->validateEMail($new_value)) {
          $value = trim($new_value);
        } 
        else {
          $this->setMessage($this->lang->translate('<p>The email address <b>{{ email }}</b> is not valid!</p>', 
              array('email' => $new_value)));
          return false;
        }
        break;
      case self::TYPE_FLOAT :
        $value = $this->str2float($new_value);
        break;
      case self::TYPE_INTEGER :
        $value = $this->str2int($new_value);
        break;
      case self::TYPE_URL :
      case self::TYPE_PATH :
        $value = $this->addSlash(trim($new_value));
        break;
      case self::TYPE_STRING :
        $value = (string) trim($new_value);
        // Hochkommas demaskieren
        $value = str_replace('&quot;', '"', $value);
        break;
      case self::TYPE_LIST :
        $lines = nl2br($new_value);
        $lines = explode('<br />', $lines);
        $val = array();
        foreach ($lines as $line) {
          $line = trim($line);
          if (!empty($line))
            $val[] = $line;
        }
        $value = implode(",", $val);
        break;
    endswitch;
    unset($config[self::FIELD_ID]);
    $config[self::FIELD_VALUE] = (string) $value;
    if (!$this->sqlUpdateRecord($config, $where)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
      return false;
    }
    return true;
  } // setValue()
  
  /**
   * Gibt den angeforderten Wert zurueck
   * 
   * @param $name - Bezeichner 
   * @return WERT entsprechend des TYP
   */
  public function getValue($name) {
    $result = '';
    $where = array();
    $where[self::FIELD_NAME] = $name;
    $config = array();
    if (!$this->sqlSelectRecord($where, $config)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
      return false;
    }
    if (sizeof($config) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, 
          $this->lang->translate('Error: There is no record for the configuration of <b>{{ name }}</b>!', 
              array('name' => $name))));
      return false;
    }
    $config = $config[0];
    switch ($config[self::FIELD_TYPE]) :
      case self::TYPE_ARRAY :
        $result = explode(",", $config[self::FIELD_VALUE]);
        break;
      case self::TYPE_BOOLEAN :
        $result = (bool) $config[self::FIELD_VALUE];
        break;
      case self::TYPE_EMAIL :
      case self::TYPE_PATH :
      case self::TYPE_STRING :
      case self::TYPE_URL :
        $result = (string) utf8_decode($config[self::FIELD_VALUE]);
        break;
      case self::TYPE_FLOAT :
        $result = (float) $config[self::FIELD_VALUE];
        break;
      case self::TYPE_INTEGER :
        $result = (integer) $config[self::FIELD_VALUE];
        break;
      case self::TYPE_LIST :
        $result = str_replace(",", "\n", $config[self::FIELD_VALUE]);
        break;
      default :
        echo $config[self::FIELD_VALUE];
        $result = utf8_decode($config[self::FIELD_VALUE]);
        break;
    endswitch;
    return $result;
  } // getValue()
  
  /**
   * Check if the configuration enty exists and create the entry if needed
   * 
   * @return boolean
   */
  public function checkConfig() {
    foreach ($this->config_array as $item) {
      $where = array();
      $where[self::FIELD_NAME] = $item['name'];
      $check = array();
      if (!$this->sqlSelectRecord($where, $check)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        return false;
      }
      if (sizeof($check) < 1) {
        // Eintrag existiert nicht
        if ($item['name'] == self::CFG_CRONJOB_KEY) {
          // special: create a random key for the cronjob
          $item['value'] = $this->generatePassword(12);
        }
        $data = array();
        $data[self::FIELD_LABEL] = $item['label'];
        $data[self::FIELD_NAME] = $item['name'];
        $data[self::FIELD_TYPE] = $item['type'];
        $data[self::FIELD_VALUE] = $item['value'];
        $data[self::FIELD_HINT] = $item['hint'];
        if (!$this->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
          return false;
        }
      }
    }
    return true;
  }

} // class dbKITcronjobConfig

class dbCronjob extends dbConnectLE {
  
  const FIELD_ID = 'cj_id';
  const FIELD_NAME = 'cj_name';
  const FIELD_DESCRIPTION = 'cj_description';
  const FIELD_MINUTE = 'cj_minute';
  const FIELD_HOUR = 'cj_hour';
  const FIELD_DAY = 'cj_day';
  const FIELD_MONTH = 'cj_month';
  const FIELD_YEAR = 'cj_year';
  const FIELD_PERIODIC = 'cj_periodic';
  const FIELD_COMMAND = 'cj_command';
  const FIELD_LAST_STATUS = 'cj_last_status';
  const FIELD_LAST_RUN = 'cj_last_run';
  const FIELD_NEXT_RUN = 'cj_next_run';
  const FIELD_STATUS = 'cj_status';
  const FIELD_TIMESTAMP = 'cj_timestamp';
  
  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_LOCKED = 'LOCKED';
  const STATUS_DELETED = 'DELETED';
  
  const PERIODIC_YES = 'YES';
  const PERIODIC_NO = 'NO';
  
  private $createTable = false;
  
  /**
   * Constructor
   *
   * @param $createTable boolean
   */
  public function __construct($createTable = false) {
    $this->createTable = $createTable;
    parent::__construct();
    $this->setTableName('mod_kit_cj_cronjob');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_NAME, "VARCHAR(64) NOT NULL DEFAULT ''");
    $this->addFieldDefinition(self::FIELD_DESCRIPTION, "TEXT");
    $this->addFieldDefinition(self::FIELD_MINUTE, "ENUM('*','0','5','10','15','20','25','30','35','40','45','50','55') DEFAULT '*'");
    $this->addFieldDefinition(self::FIELD_HOUR, "ENUM('*','0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23') DEFAULT '*'");
    $this->addFieldDefinition(self::FIELD_DAY, "ENUM('*','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31') DEFAULT '*'");
    $this->addFieldDefinition(self::FIELD_MONTH, "ENUM('*','1','2','3','4','5','6','7','8','9','10','11','12') DEFAULT '*'");
    $this->addFieldDefinition(self::FIELD_YEAR, "ENUM('*','2012','2013','2014','2015','2016','2017','2018','2019','2020','2021','2022') DEFAULT '*'");
    $this->addFieldDefinition(self::FIELD_PERIODIC, "ENUM('YES', 'NO') DEFAULT 'YES'");
    $this->addFieldDefinition(self::FIELD_COMMAND, "TEXT");
    $this->addFieldDefinition(self::FIELD_LAST_RUN, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    $this->addFieldDefinition(self::FIELD_LAST_STATUS, "TEXT");
    $this->addFieldDefinition(self::FIELD_NEXT_RUN, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    $this->addFieldDefinition(self::FIELD_STATUS, "ENUM('ACTIVE','LOCKED','DELETED') DEFAULT 'ACTIVE'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->checkFieldDefinitions();
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    // Tabelle erstellen
    if ($this->createTable) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }
  } // __construct()
  
} // class CronjobList