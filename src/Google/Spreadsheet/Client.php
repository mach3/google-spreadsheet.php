<?php

/**
 * Google_Spreadsheet_Client
 * -------------------------
 * @class Client to authenticate and send request to Google service
 */

class Google_Spreadsheet_Client {

    private $client = null; // Google_Client instance

    /**
     * Options:
     * - session_key   {String}  ... Session key for access token
     * - cache         {Boolean} ... Save cache for GET request or not
     * - cache_dir     {String}  ... Directory path to save cache data
     * - cache_expires {Integer} ... Cache lifetime
     */
    private $options = array(
        "session_key" => "__google_service_token__",
        "cache" => false,
        "cache_dir" => "cache",
        "cache_expires" => 3600
    );

    /**
     * @constructor
     * @param {String|Array} $keys ... Path to json file or array
     */
    public function __construct($keys = null){
        if(! session_id()){
            session_start();
        }
        if($keys){
            $this->auth($keys);
        }
    }

    /**
     * Authenticate connection
     *
     * @param {String|Array} $keys ... Path to json file or array
     * @return {Google_Spreadsheet_Client} ... This
     */
    public function auth($keys){
        if(gettype($keys) === "string"){
            $keys = json_decode(file_get_contents($keys));
        }
        $this->client = new Google_Client();
        $cred = new Google_Auth_AssertionCredentials(
            $keys->client_email,
            array(Google_Service_Drive::DRIVE),
            $keys->private_key
        );
        $this->client->setAssertionCredentials($cred);
        return $this;
    }

    /**
     * Configure options
     *
     * @param {String|Array} $key|$options
     * @param {Mixed} $value
     * @return {Mixed}
     */
    public function config(/* $key [,$value] */){
        $args = func_get_args();
        if(! count($args)){
            return $this->options;
        }
        $type = gettype($args[0]);
        if($type === "array"){
            foreach($args[0] as $key => $value){
                $this->config($key, $value);
            }
        }
        else if($type === "string"){
            if(count($args) === 1){
                return array_key_exists($args[0], $this->options) ?
                    $this->options[$args[0]] : null;
            }
            $this->options[$args[0]] = $args[1];
        }
        return $this;
    }

    /**
     * Get access token from client connection
     *
     * @return {String} ... Access token
     */
    public function getAccessToken(){
        $session_key = $this->config("session_key");
        $token = array_key_exists($this->options["session_key"], $_SESSION) ? 
            $_SESSION[$session_key] : null;
        if($token){
            // expired ?
            $vars = json_decode($token);
            $token = time() >= ($vars->expires_in + $vars->created) ? null : $token;
        }
        if(! $token){
            $this->client->getAuth()->refreshTokenWithAssertion();
            $token = $this->client->getAccessToken();
            $_SESSION[$session_key] = $token;
        }
        return json_decode($token)->access_token;
    }

    /**
     * Send request to google service, return response
     * @param {String} $url ... URL to request
     * @param {String} $method ... GET or POST
     * @param {Array} $header ... Additional headers
     * @param {String} $postBody ... Body for POST request
     * @param {Boolean} $force ... Ignore cache
     */
    public function request($url, $method = "GET", $headers = array(), $postBody = null, $force = false){
        $cache = $this->config("cache");
        $feed = ($cache && $method === "GET" && ! $force) ? $this->cache($url) : null;

        if(! $feed){
            $headers = array_merge(array(
                "Authorization" => sprintf("Bearer %s", $this->getAccessToken())
            ), $headers);
            $req = new Google_Http_Request($url, $method, $headers, $postBody);
            $curl = new Google_IO_Curl($this->client);
            $res = $curl->executeRequest($req);
            $feed = $res[0];
            if($cache){
                $this->cache($url, $feed);
            }
        }
        return json_decode($feed, true);
    }

    /**
     * Save or get data from cache data
     * @param {String} $url ... URL to request
     * @param {String} $content ... Data to save
     * @return {Null|String} ... Content data
     */
    private function cache($url, $content = null){
        $path = $this->config("cache_dir") . "/" . urlencode($url);
        if($content !== null){
            return file_put_contents($path, $content);
        }
        if(file_exists($path) && time() < (filemtime($path) + $this->config("cache_expires"))){
            return file_get_contents($path);
        }
        return null;
    }

    /**
     * Get Google_Spreadsheet_File instance by id
     * @param {String} $file_id
     */
    public function file($file_id){
        return new Google_Spreadsheet_File($file_id, $this);
    }
}
