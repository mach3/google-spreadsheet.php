<?php

/**
 * Google_Spreadsheet
 * 
 * @class Process Google Spreadsheet
 */

class Google_Spreadsheet {

  /**
   * Get client instance
   * 
   * @param string|array $key
   * @return Google_Spreadsheet_Client $client
   */
  static function getClient ($key = null) {
    return new Google_Spreadsheet_Client($key);
  }

}
