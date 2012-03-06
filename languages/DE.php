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

$LANG = array(
    'Abort'
      => 'Abbruch',
    'About'
      => '?',
    'Active'
      => 'Aktiv', 
		'ACTIVE'
		  => 'Aktiv',
		'APR'
		  => 'April',
		'At minimum please set a value for hour, minute, day, weekday and month for definition of the cronjob!'
			=> 'Bitte setzen Sie mindestens je einen Wert für die Stunde, Minute, Tag, Wochentag und Monat für die Definition des Cronjobs!',
		'AUG'
		  => 'August',
		'Command'
			=> 'Befehl',
    'Cronjob'
      => 'Cronjob',
		'Cronjob preselect'
			=> 'Cronjob Vorauswahl',
		'Days'
      => 'Tage',
		'DEC'
		  => 'Dezember',
    'Define cronjob'
		  => 'Cronjob definieren',
		'Deleted'
      => 'Gelöscht',
		'DELETED'
		  => 'Gelöscht',
    'Description'
      => 'Beschreibung',
    'Edit'
      => 'Bearbeiten',
    'Edit the cronjob with the {{ id }}.'
      => 'Bearbeiten Sie den Cronjob mit der <b>{{ id }}</b>.',
    'Error reading the configuration record with the <b>ID {{ id }}</b>.'
      => 'Fehler beim Einlesen des Konfigurations Datensatz mit der <b>ID {{ id }}</b>.',
    'Error: The field <b>{{ field }}</b> does not exists!'
      => 'Das Feld <b>{{ field }}</b> existiert nicht!',
    'Error: The field <b>{{ field }}</b> seems not of type <b>ENUM()</b>, can\'t read any values!'
      => 'Das Feld <b>{{ field }}</b> scheint nicht vom Typ <b>ENUM()</b> zu sein, es konnten keine Werte ausgelesen werden!',
    'Error: The record with the <b>ID {{ id }}</b> does not exists!'
      => 'Der Datensatz mit der <b>ID {{ id }}</b> existiert nicht!',
    'Error: There is no record for the configuration of <b>{{ name }}</b>!'
      => 'Es existiert kein Datensatz für die Konfiguration von <b>{{ name }}</b>!',
    'Edit the settings for kitCronjob.'
      => 'Bearbeiten Sie die Einstellungen für kitCronjob',
    'Error executing the template <b>{{ template }}</b>: {{ error }}'
      => 'Fehler beim Ausführen des Templates <b>{{ template }}</b>: {{ error }}',
		'FEB'
		  => 'Februar',
		'FRI'
		  => 'Freitag',
    'HINT_CFG_CRONJOB_ACTIVE'
      => '',
		'HINT_CFG_CRONJOB_KEY'
      => 'Um zu verhindern, dass Cronjobs durch einen einfachen Aufruf der cronjob.php ausgeführt werden, muss der angegebene Schlüssel als Parameter übergeben werden. Der Aufruf der Datei lautet cronjob.php?<b>key=SCHLÜSSEL</b>.',
		'HINT_CFG_CRONJOB_NAME_MINIMUM_LENGTH'
			=> 'Die minimale Länge des Cronjob Bezeichners.',
		'HINT_CFG_USE_SSL'
			=> 'Legen Sie fest, ob kitCronjob erforderliche Verbindungen über das Secure Socket Layer Protokoll (SSL) aufzubauen soll (1=JA, 0=Nein).',
    'HINT_CRONJOB'
		  => 'Legen Sie den oder die gewünschten Ausführungstermin(e) für diesen Cronjob fest. Wählen Sie aus jeder Spalte mindestens einen Wert aus. Um mehrere Werte in einer Spalte auszuwählen halten Sie die <kbd>STRG</kbd> Taste gedrückt und klicken die gewünschten Werte an.',
    'HINT_CRONJOB_ACTIVE'
      => 'Legen Sie fest, ob kitCronjob ausgeführt wird oder nicht. 1=Aktiv, 0=AUS',
		'HINT_CRONJOB_SAMPLES'
			=> 'Vordefinierte Cronjobs zur bequemen Auswahl oder als Beispiel für eigene Einstellungen.',
    'HINT_COMMAND'
      => '',
    'HINT_DAY'
      => '',
    'HINT_DESCRIPTION'
      => '',
    'HINT_HOUR'
      => '',
    'HINT_LAST_RUN'
      => '',
    'HINT_LAST_STATUS'
      => '',
    'HINT_MINUTE'
      => '',
    'HINT_MONTH'
      => '',
    'HINT_NAME'
      => '',
    'HINT_NEXT_RUN'
      => '',
    'HINT_PERIODIC'
      => '',
    'HINT_STATUS'
      => '',
    'HINT_TIMESTAMP'
      => '',
    'HINT_YEAR'
      => '',
		'JAN'
		  => 'Januar',
		'JUL'
		  => 'Juli',
		'JUN'
		  => 'Juni',
		'LABEL_CFG_CRONJOB_ACTIVE'
			=> 'Cronjob ausführen',
		'LABEL_CFG_CRONJOB_KEY'
			=> 'Cronjob Schlüssel',
		'LABEL_CFG_CRONJOB_NAME_MINIMUM_LENGTH'
			=> 'Bezeichner, min. Länge',
		'LABEL_CFG_USE_SSL'
			=> 'SSL verwenden',
    'Locked'
      => 'Gesperrt',
		'LOCKED'
		  => 'Gesperrt',
    'MAR'
		  => 'März',
		'MAY'
		  => 'Mai',
		'MON'
		  => 'Montag',
		'Month'
			=> 'Monat',
    'Name'
      => 'Bezeichner',
		'NOV'
		  => 'November',
		'OCT'
		  => 'Oktober',
    'OK'
      => 'OK',
    'Please create a new cronjob!'
      => 'Erstellen Sie einen neuen Cronjob!',
		'Please define a unique name for the cronjob!'
			=> 'Bitte legen Sie einen eindeutigen Bezeichner für den Cronjob fest!',
		'Please define the command to execute by the cronjob!'
			=> 'Bitte legen Sie den Befehl fest, der von dem Cronjob ausgeführt werden soll!',
		'SAT'
		  => 'Samstag',
		'SEP'
		  => 'September',
    'Settings'
      => 'Einstellungen',
		'SUN'
		  => 'Sonntag',
		'The cronjob name must be at minimum {{ length }} characters long.'
			=> 'Der Cronjob Bezeichner muß mindestens {{ length }} Zeichen lang sein!',
    '<p>The email address <b>{{ email }}</b> is not valid!</p>'
      => '<p>Die E-Mail Adresse <b>{{ email }}</b> ist nicht gültig!</p>',
    '<p>The setting for <b>{{ name }}</b> was changed.</p>'
      => 'Die Einstellung für <b>{{ name }}</b> wurde geändert.</p>',
		'THU'
		  => 'Donnerstag',
		'TUE'
		  => 'Dienstag',
    'Value'
      => 'Wert',
		'WED'
		  => 'Mittwoch',
		'Weekdays'
			=> 'Wochentage',
		
);