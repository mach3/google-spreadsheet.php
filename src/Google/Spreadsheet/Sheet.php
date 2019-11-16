<?php

/**
 * Google_Spreadsheet_Sheet
 * 
 * @class Fetch or process data in spreadsheet
 */

class Google_Spreadsheet_Sheet {

  private $client = null;
  private $id = null;
  private $name = null;
  private $sheet = null;
  private $values = null;

  public $header = null;
  public $items = null;

  private $options = array(
    'cache' => false,
    'cache_dir' => 'cache',
    'cache_expires' => 600
  );

  /**
   * @constructor
   * @param string $name
   * @param string $id
   * @param Google_Client $client
   */
  public function __construct ($name, $id, $client) {
    $this->client = $client;
    $this->id = $id;
    $this->name = $name;
    $this->sheet = new Google_Service_Sheets($this->client);
  }

  /**
   * Configure options
   * 
   * @param array $options
   * @return $this
   */
  public function config ($options) {
    foreach ($options as $key => $value) {
      if (array_key_exists($key, $this->options)) {
        $this->options[$key] = $value;
      }
    }
    return $this;
  }

  /**
   * Fetch data from Sheets API or cache
   * - Automatically parse the data
   * - When load data from remote, save cache
   * 
   * @param boolean $force
   * @return $this
   */
  public function fetch ($force = false) {
    if ($force || !$this->getCache(true)) {
      $res = $this->sheet->spreadsheets_values->get($this->id, $this->name);
      if ($res && is_object($res) && is_array($res->values)) {
        $this->values = $res->values;
        $this->parse();
        if ($this->options['cache']) {
          $this->saveCache();
        }
      }
    }
    else {
      $cache = $this->getCache();
      $this->values = $cache['values'];
      $this->parse();
    }
    return $this;
  }

  /**
   * Parse multidimensional array data from spreadsheet to associative array
   * 
   * @return $this
   */
  public function parse () {
    $values = $this->values;
    $header = array();
    $items = array();
    foreach ($values as $i => $row) {
      if (!$i) {
        $header = $row;
        continue;
      }
      $items[$i] = array();
      foreach ($header as $j => $key) {
        $items[$i][$key] = array_key_exists($j, $row) ? $row[$j] : '';
      }
    }
    $this->header = $header;
    $this->items = $items;
    return $this;
  }

  /**
   * Select row by condition
   * The condition has to be array or function
   * 
   * @param array|function $condition
   * @return array $result
   */
  public function select ($condition) {
    $result = null;
    if (is_callable($condition)) {
      $result = array_filter($this->items, $condition);
    }
    else if (is_array($condition)) {
      $result = array_filter($this->items, function ($row) use ($condition) {
        $valid = true;
        foreach ($condition as $key => $value) {
          if ($row[$key] !== $value) {
            $valid = false;
          }
        }
        return $valid;
      });
    }
    return $result;
  }

  /**
   * Insert a new row to spreadsheet
   * Forcely fetch up-to-date data from remote before inserting
   * 
   * @param array $vars
   * @return Google_Service_Sheets_AppendValuesResponse $response
   */
  public function insert ($vars) {
    $this->fetch(true);
    $vars = (array) $vars;
    $values = array();
    $values[] = array();
    foreach ($this->header as $key) {
      array_push($values[0], in_array($key, array_keys($vars)) ? (string) $vars[$key] : '');
    }
    $body = new Google_Service_Sheets_ValueRange(array('values' => $values));
    $params = array('valueInputOption' => 'USER_ENTERED');
    return $this->sheet->spreadsheets_values->append($this->id, $this->name, $body, $params);
  }

  /**
   * Update values by condition
   * Forcely fetch up-to-date data from remote before updating
   * 
   * @param array $vars
   * @param array|function $condition
   * @return Google_Service_Sheets_AppendValuesResponse $response
   */
  public function update ($vars, $condition) {
    $this->fetch(true);
    $rows = array_keys($this->select($condition));
    $data = array();
    foreach ($vars as $key => $value) {
      $c = array_search($key, $this->header);
      if (false === $c) continue;
      $col = $this->getColumnLetter($c + 1);
      foreach ($rows as $r) {
        $r += 1;
        $data[] = new Google_Service_Sheets_ValueRange(array(
          'range' => $this->name . "!${col}${r}",
          'values' => array(
            array($value)
          )
        ));
      }
    }
    if (count($data)) {
      $body = new Google_Service_Sheets_BatchUpdateValuesRequest(array(
        'valueInputOption' => 'USER_ENTERED',
        'data' => $data
      ));
      return $this->sheet->spreadsheets_values->batchUpdate($this->id, $body);
    }
    return null;
  }

  /**
   * Get column letter (A1 notation) from number
   * 
   * @param integer $index
   * @return string $result
   */
  private function getColumnLetter ($index) {
    $s = array();
    for ($i = $index; $i > 0; $i = intval(($i) / 26)) {
      array_push($s, chr(65 + (($i - 1) % 26)));
    }
    return implode('', array_reverse($s));
  }

  /**
   * Get cache file path
   * 
   * @return string $path
   */
  private function getCachePath () {
    return implode('/', array(
      $this->options['cache_dir'],
      $this->id,
      urlencode($this->name)
    ));
  }

  /**
   * Get cache data
   * Or test whether cache file exists and is alive
   * 
   * @param boolean $test
   * @return array|boolean $result
   */
  private function getCache ($test = false) {
    $file = $this->getCachePath();
    $hasCache = $this->options['cache']
      && file_exists($file)
      && (time() - filemtime($file)) < $this->options['cache_expires'];
    if ($test) {
      return $hasCache;
    } elseif ($hasCache) {
      return unserialize(file_get_contents($file));
    }
    return null;
  }

  /**
   * Save current data to cache file
   */
  private function saveCache () {
    $dir = implode('/', array($this->options['cache_dir'], $this->id));
    if (!file_exists($dir)) {
      mkdir($dir);
    }
    return file_put_contents($this->getCachePath(), serialize(array(
      'values' => $this->values
    )));
  }

}
