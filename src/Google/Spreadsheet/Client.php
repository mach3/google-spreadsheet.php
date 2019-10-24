<?php

/**
 * Google_Spreadsheet_Client
 * 
 * @class Client for Google Spreadsheet (Sheets API v4)
 */

class Google_Spreadsheet_Client {

  private $client = null;

  /**
   * @constructor
   * @param string|array $key
   */
  public function __construct ($key = null) {
    if ($key) {
      $this->client = new Google_Client();
      $this->client->setAuthConfig($key);
      $this->client->setScopes(array(
        Google_Service_Sheets::SPREADSHEETS
      ));
    }
  }

  /**
   * Generate File instance
   * 
   * @param string $id
   */
  public function file ($id) {
    return new Google_Spreadsheet_File($id, $this->client);
  }

}
