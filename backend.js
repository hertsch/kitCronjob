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
		if (CONTROL.options[i].value=='MON') {
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
		if (CONTROL.options[i].value=='FEB') {
			CONTROL.options[i].selected = true;
		}
		else {
			CONTROL.options[i].selected = false;
		}
	}	
}
/*
function selectAll() {
						CONTROL = document.auswahlform.hour;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = true;
						}
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = true;
						}
						CONTROL = document.auswahlform.day;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = true;
						}
						CONTROL = document.auswahlform.wd;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = true;
						}
						CONTROL = document.auswahlform.mon;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = true;
						}
					}
					
                	function deselectAll() {
						CONTROL = document.auswahlform.hour;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = false;
						}
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = false;
						}
						CONTROL = document.auswahlform.day;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = false;
						}
						CONTROL = document.auswahlform.wd;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = false;
						}
						CONTROL = document.auswahlform.mon;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = false;
						}
					}

					function scriptA() {
						selectAll();
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							if(CONTROL.options[i].value==0 || CONTROL.options[i].value==15 || CONTROL.options[i].value==30 || CONTROL.options[i].value==45) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
					}
					function scriptB() {
						selectAll();
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							if(CONTROL.options[i].value==0 || CONTROL.options[i].value==30) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
					}
					function scriptC() {
						selectAll();
						CONTROL = document.auswahlform.hour;
						for(var i = 0;i < CONTROL.length;i++){
							if(i==12) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							if(CONTROL.options[i].value==30) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
					}
					function scriptD() {
						selectAll();
						CONTROL = document.auswahlform.wd;
						for(var i = 0;i < CONTROL.length;i++){
							if(i==0) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
						CONTROL = document.auswahlform.hour;
						for(var i = 0;i < CONTROL.length;i++){
							if(i==0) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							if(CONTROL.options[i].value==0) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
					}
					function scriptE() {
						selectAll();
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							if(CONTROL.options[i].value==0 || CONTROL.options[i].value==5 || CONTROL.options[i].value==10 || CONTROL.options[i].value==15 || CONTROL.options[i].value==20 || CONTROL.options[i].value==25 || CONTROL.options[i].value==30 || CONTROL.options[i].value==35 || CONTROL.options[i].value==40 || CONTROL.options[i].value==45 || CONTROL.options[i].value==50 || CONTROL.options[i].value==55) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
					}
					function scriptF() {
						deselectAll();
						CONTROL = document.auswahlform.minute;
						for(var i = 0;i < CONTROL.length;i++){
							if(CONTROL.options[i].value==0) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
						CONTROL = document.auswahlform.hour;
						for(var i = 0;i < CONTROL.length;i++){
							if(i==13) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
						CONTROL = document.auswahlform.wd;
						for(var i = 0;i < CONTROL.length;i++){
							CONTROL.options[i].selected = true;
						}
						CONTROL = document.auswahlform.day;
						for(var i = 0;i < CONTROL.length;i++){
							if(i==23) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
						CONTROL = document.auswahlform.mon;
						for(var i = 0;i < CONTROL.length;i++){
							if(i==11) {
								CONTROL.options[i].selected = true;
							}
							else {
								CONTROL.options[i].selected = false;
							}
						}
					}
*/