<?php

/**
 * Google_Spreadsheet
 *
 * @class Process Google Spreadsheet
 */
class Google_Spreadsheet {

	/**
	 * Get Google_Spreadsheet_Client instance
	 * @param {String|Array} $keys ... Path to json file or array
	 */
	static public function getClient($keys = null){
		return new Google_Spreadsheet_Client($keys);
	}
}