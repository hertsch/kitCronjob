/**
 * kitCronjob
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitCronjob
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

function selectAll() {
	var CONTROL;
	CONTROL = document.cronjob_edit.cronjob_hour;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = true;
	}
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = true;
	}
	CONTROL = document.cronjob_edit.cronjob_day_of_month;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = true;
	}
	CONTROL = document.cronjob_edit.cronjob_day_of_week;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = true;
	}
	CONTROL = document.cronjob_edit.cronjob_month;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = true;
	}	
} // selectAll()

function deselectAll() {
	var CONTROL;
	CONTROL = document.cronjob_edit.cronjob_hour;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = false;
	}
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = false;
	}
	CONTROL = document.cronjob_edit.cronjob_day_of_month;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = false;
	}
	CONTROL = document.cronjob_edit.cronjob_day_of_week;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = false;
	}
	CONTROL = document.cronjob_edit.cronjob_month;
	for (var i = 0;i < CONTROL.length;i++) {
		CONTROL.options[i].selected = false;
	}	
} // deselectAll()

function select_each_5_minutes() {
	selectAll();
}

function select_each_15_minutes() {
	var CONTROL;
	selectAll();
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==0 || CONTROL.options[i].value==15 || 
				CONTROL.options[i].value==30 || CONTROL.options[i].value==45) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
}

function select_each_30_minutes() {
	var CONTROL;
	selectAll();
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==0 || CONTROL.options[i].value==30) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
}

function select_daily_0630() {
	var CONTROL;
	selectAll();
	CONTROL = document.cronjob_edit.cronjob_hour;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==6) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==30) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
} 

function select_each_monday_2315() {
	var CONTROL;
	selectAll();
	CONTROL = document.cronjob_edit.cronjob_hour;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==23) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==15) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
	CONTROL = document.cronjob_edit.cronjob_day_of_week;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value=='Monday') {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
}

function select_february_07_1220() {
	var CONTROL;
	selectAll();
	CONTROL = document.cronjob_edit.cronjob_hour;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==12) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
	CONTROL = document.cronjob_edit.cronjob_minute;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==20) { 
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
	CONTROL = document.cronjob_edit.cronjob_day_of_month;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value==7) {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}
	CONTROL = document.cronjob_edit.cronjob_month;
	for (var i = 0; i < CONTROL.length; i++) {
		if (CONTROL.options[i].value=='February') {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}	
}
