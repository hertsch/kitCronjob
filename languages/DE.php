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
      => 'Über...',
    'Active'
      => 'Aktiv',
    'At minimum please set a value for hour, minute, day, weekday and month for definition of the cronjob!'
      => 'Bitte legen Sie einen Wert für die Stunde, Minute, den Tag, Wochtag und Monat für die Definition eines Cronjob fest!',
    'Command'
      => 'Befehl',
    'Cronjob edit'
      => 'Cronjob bearbeiten',
    'Cronjob ID'
      => 'Cronjob ID',
    'Cronjob list'
      => 'Cronjob Liste',
    'Cronjob name'
      => 'Cronjob Bezeichner',
    'Cronjob preselect'
      => 'Cronjob Vorauswahl',
    'Cronjob status'
      => 'Cronjob Status',
    'Cronjobs'
      => 'Cronjobs',
    'Days'
      => 'Tage',
    'Days of month'
      => 'Tage im Monat',
    'Days of week'
      => 'Wochentage',
    'Define cronjob'
      => 'Cronjob definieren',
    'Deleted'
      => 'Gelöscht',
    'Description'
      => 'Beschreibung',
    'Error executing the template <b>{{ template }}</b>: {{ error }}'
      => 'Fehler bei der Ausführung des Template <b>{{ template }}</b>: {{ error }}',
    'Error message'
      => 'Fehlermeldung',
    'Error: The field <b>{{ field }}</b> does not exists!'
      => 'Das Feld <b>{{ field }}</b> existiert nicht!',
    "Error: The field <b>{{ field }}</b> seems not of type <b>ENUM()</b>, can't read any values!"
      => 'Das Feld <b>{{ field }}</b> scheint nicht vom Typ <b>ENUM()</b> zu sein, kann keine Werte einlesen!',
    'Error: The record with the <b>ID {{ id }}</b> does not exists!'
      => 'Der Datensatz mit der <b>ID {{ id }}</b> existiert nicht!',
    "Execute cronjob at february, 7 at 12:20 o'clock"
      => 'Cronjob am 7. Februar um 12:20 Uhr ausführen',
    "Execute cronjob daily at 06:30 o'clock."
      => 'Cronjob täglich um 06:30 Uhr ausführen',
    'Execute cronjob each 5 minutes'
      => 'Cronjob alle 5 Minuten ausführen',
    'Execute cronjob each 15 minutes'
      => 'Cronjob alle 15 Minuten ausführen',
    'Execute cronjob each 30 minutes'
      => 'Cronjob alle 30 Minuten ausführen',
    "Execute cronjob each monday at 23:15 o'clock"
      => 'Cronjob jeden Montag um 23:15 Uhr ausführen',
    "Here you see the status of the last executed cronjobs."
      => 'In der Liste sehen Sie den Status der zuletzt ausgeführten Cronjobs.',
    'HINT_COMMAND'
      => 'Befehl, der von Cronjob ausgeführt werden soll. Geben Sie eine vollständige URL mit allen erforderlichen Parametern an.',
    'HINT_CRONJOB'
      => 'Legen Sie fest, wann der Cronjob ausgeführt werden soll',
    'HINT_CRONJOB_SAMPLES'
      => 'Wählen Sie ein Cronjob Beispiel als Grundlage für Ihre eigenen Einstellungen aus.',
    'HINT_DESCRIPTION'
      => 'Legen Sie eine beliebige Beschreibung für den Cronjob fest.',
    'HINT_LAST_RUN'
      => 'Letzte Ausführung des Cronjob',
    'HINT_LAST_STATUS'
      => 'Status bei der letzten Ausführung des Cronjob',
    'HINT_NAME'
      => 'Legen Sie einen eindeutigen Bezeichner für den Cronjob fest.',
    'HINT_NEXT_RUN'
      => '',
    'HINT_STATUS'
      => 'Der Status dieses Cronjob',
    'Hours'
      => 'Stunden',
    'ID'
      => 'ID',
    'It seems that kitCronjob was never executed.<br />To get kitCronjob running please use a service like <b>cronjob.de</b> or use a cronjob service of your provider.<br />Configure the service out to call each 5 minutes kitCronjob with the following URL:<br><b>{{ url }}</b>'
      => 'Es sieht so aus, als ob kitCronjob noch nie ausgeführt wurde.<br />Um kitCronjob regelmäßig auszuführen nutzen Sie bitte einen Dienst wie <b>cronjob.de</b> oder einen Cronjob Service Ihres Providers.<br />Richten Sie den Dienst so ein, dass er alle 5 Minuten kitCronjob mit der folgenden URL aufruft:<br /><b>{{ url }}</b>',
    'Last run'
      => 'Letzte Ausführung',
    'Last status'
      => 'Letzter Status',
    'Locked'
      => 'Gesperrt',
    'Message'
      => 'Mitteilung',
    'Minutes'
      => 'Minuten',
    'Months'
      => 'Monate',
    'Name'
      => 'Bezeichner',
    'Next run'
      => 'Nächste Ausführung',
    'OK'
      => 'OK',
    'phone'
      => 'Telefon',
    "Please create a new cronjob like you need it."
      => 'Bitte erstellen Sie einen neuen Cronjob mit den Einstellungen die Sie benötigen.',
    'Please define a unique name for the cronjob!'
      => 'Bitte legen Sie einen eindeutigen Namen für den Cronjob fest!',
    'Please define the command to execute by the cronjob!'
      => 'Bitte legen Sie den Befehl fest, der von dem Cronjob ausgeführt werden soll!',
    'Please edit the cronjob with the <strong>ID {{ id }}</strong> like you need it.'
      => 'Bearbeiten Sie den Cronjob mit der <strong>ID {{ id }}</strong> nach Ihren Wünschen.',
    'Please help to improve open source software and report this problem to the <a href="https://phpmanufaktur.de/support" target="_blank">Addons Support Group</a>.'
      => 'Bitte helfen Sie mit diese Open Source Software zu verbessern und melden Sie dieses Problem der <a href="https://phpmanufaktur.de/support" target="_blank">Addons Support Gruppe</a>.',
    'Please select the cronjob you wish to edit.'
      => 'Bitte wählen Sie den Cronjob aus, den Sie bearbeiten möchten.',
    'Protocol'
      => 'Protokoll',
    'Settings'
      => 'Einstellungen',
    'Status'
      => 'Status',
    'The cronjob name {{ name }} is not unique, please select another name!'
      => 'Der Cronjob Bezeichner {{ name }} ist nicht eindeutig, bitte legen Sie einen anderen Bezeichner fest!',
    'The cronjob name must be at minimum {{ length }} characters long.'
      => 'Der Cronjob Bezeichner muss mindestens {{ length }} Zeichen lang sein.',
    'The cronjob with the ID {{ id }} does not exists!'
      => 'Der Cronjob mit der ID {{ id }} existiert nicht!',
    'The cronjob with the ID {{ id }} was successfull inserted'
      => 'Der Cronjob mit der ID {{ id }} wurde erfolgreich hinzugefügt.',
    'The cronjob with the ID {{ id }} was successfull updated.'
      => 'Der Cronjob mit der ID {{ id }} wurde erfolgreich aktualisiert.',
    'There exists no array with default values for the field {{ field }}.'
      => 'Es existiert kein Array mit Vorgabewerten für das Feld {{ field }}.',
    'There is no active cronjob. Please create a new cronjob using the tab "<b>Cronjob edit</b>".'
      => 'Es existiert kein aktiver Cronjob. Bitte erstellen Sie einen neuen Cronjob, wählen Sie dazu den Reiter "<b>Cronjob bearbeiten</b>" aus.',
    'Timestamp'
      => 'Zeitstempel',
    'Weekdays'
      => 'Wochentage',
    );
