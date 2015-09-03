<?php

/**
 * Google_Spreadsheet_File
 * -----------------------
 * @class Instance represents Google Spreadsheet's file
 */

class Google_Spreadsheet_File {

    private $client = null; // Google_Spreadsheet_Client
    private $id = null; // ID name for file

    // Collection of links
    private $link = array(
        "sheets" => "https://spreadsheets.google.com/feeds/worksheets/%s/private/full?alt=json"
    );

    /**
     * Constructor
     *
     * @param {String} $id
     * @param {Google_Spreadsheet_Client} $client
     */
    public function __construct($id, $client){
        $this->id = $id;
        $this->client = $client;
    }

    /**
     * Get sheets list
     *
     * @return {Array} ... Sheets list of the file
     */
    public function sheets(){
        $data = $this->client->request(sprintf($this->link["sheets"], $this->id));
        return $data ? $data["feed"]["entry"] : null;
    }

    /**
     * Get Google_Spreadsheet_Sheet instance by sheet's title
     *
     * @param {String} $title
     * @return {Google_Spreadsheet_Sheet}
     */
    public function sheet($title){
        $sheet = null;
        foreach($this->sheets() as $item){
            if($title === $item["title"]["\$t"]){
                $sheet = $item;
                break;
            }
        }
        return new Google_Spreadsheet_Sheet($sheet, $this->client);
    }
}
