<?php

/**
 * Google_Spreadsheet_File
 * 
 * @class Spreadsheet File Object
 */

class Google_Spreadsheet_File {
  
  private $client = null;
  private $id = null;

  /**
   * @constructor
   * @param string $id
   * @param Google_Client $client
   */
  public function __construct ($id, $client) {
    $this->id = $id;
    $this->client = $client;
  }

  /**
   * Generate Sheet instance by sheet's name
   * 
   * @param string $name
   */
  public function sheet ($name) {
    return new Google_Spreadsheet_Sheet($name, $this->id, $this->client);
  }

}
