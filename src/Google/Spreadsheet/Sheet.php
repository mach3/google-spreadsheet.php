<?php

/**
 * Google_Spreadsheet_Sheet
 * ------------------------
 * @class Instance represents Google Spreadsheet's sheet
 */

class Google_Spreadsheet_Sheet {

	private $meta = null; // Meta info of the sheet
	private $client = null; // Google_Spreadsheet_Client instance
	private $link = array(); // Collection of links

	public $fields = null; // Fields of table
	public $items = null; // Data of table

	/**
	 * Constructor
	 *
	 * @param {Array} $meta
	 * @param {Google_Spreadsheet_Client} $client
	 */
	public function __construct($meta, $client){
		$this->meta = $meta;
		$this->client = $client;

		foreach($this->meta["link"] as $link){
			switch(true){
				case strstr($link["rel"], "#cellsfeed"):
					$this->link["cellsfeed"] = $link["href"] . "?alt=json"; break;
				case strstr($link["rel"], "#listfeed"):
					$this->link["listfeed"] = $link["href"] . "?alt=json"; break;
				default: break;
			}
		}

		$this->fetch();
	}

	/**
	 * Fetch the table data
	 *
	 * @param {Boolean} $force ... Ignore cache data or not
	 * @return {Google_Spreadsheet_Sheet} ... This
	 */
	public function fetch($force = false){
		$data = $this->client->request($this->link["cellsfeed"], "GET", array(), null, $force);
		$this->process($data["feed"]["entry"]);
		return $this;
	}

	/**
	 * Select rows by condition
	 *
	 * @param {Closure|Array} $condition
	 * @return {Array}
	 */
	public function select($condition = null){
		if(is_callable($condition)){
			return array_filter($this->items, $condition);
		}
		if(is_array($condition)){
			$result = array();
			foreach($this->items as $row){
				$invalid = false;
				foreach($condition as $key => $value){
					if($row[$key] !== $value){ $invalid = true; }
				}
				if($invalid){ continue; }
				array_push($result, $row);
			}
			return $result;
		}
		return $this->items;
	}

	/**
	 * Update the value of column
	 * @param {Integer} $row
	 * @param {Integer|String} $col ... Column number or field's name
	 * @param {String} $value
	 * @return {Google_Spreadsheet_Sheet} ... This
	 */
	public function update($row, $col, $value){
		$col = is_string($col) ? array_search($col, array_values($this->fields), true) + 1 : $col;
		$body = sprintf(
			'<entry xmlns="http://www.w3.org/2005/Atom" xmlns:gs="http://schemas.google.com/spreadsheets/2006">
            <gs:cell row="%u" col="%u" inputValue="%s"/>
			</entry>',
			$row, $col, htmlspecialchars($value)
		);
		$this->client->request(
			$this->link["cellsfeed"],
			"POST",
			array("Content-Type" => "application/atom+xml"),
			$body
		);
		return $this;
	}

	/**
	 * Insert a row to the table
	 * @param {Array} $vars
	 * @return {Google_Spreadsheet_Sheet} ... This
	 */
	public function insert($vars){
		$body = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:gsx="http://schemas.google.com/spreadsheets/2006/extended">';
		foreach($this->fields as $c => $key){
			if(! array_key_exists($key, $vars)){ continue; }
			$value = htmlspecialchars($vars[$key]);
			$body .= "<gsx:{$key}>{$value}</gsx:{$key}>";
		}
		$body .= "</entry>";
		$this->client->request(
			$this->link["listfeed"],
			"POST",
			array("Content-Type" => "application/atom+xml"),
			$body
		);
		return $this;
	}

	/**
	 * Process the entry data fetched from cellfeed API
	 * Update its `items` property
	 *
	 * @param {Array} $entry
	 */
	private function process($entry){
		$this->fields = array();
		$this->items = array();

		foreach($entry as $col){
			preg_match("/^([A-Z]+)(\d+)$/", $col["title"]["\$t"], $m);
			$content = $col["content"]["\$t"];
			$r = (int) $m[2];
			$c = $m[1];
			if($r === 1){
				$this->fields[$c] = $content;
				continue;
			}
			if(array_key_exists($r, $this->fields)){
				$this->items[$r] = array_key_exists($r, $this->items) ? $this->items[$r] : array();
				$this->items[$r][$this->fields[$c]] = $content;
			}
		}
	}

}

