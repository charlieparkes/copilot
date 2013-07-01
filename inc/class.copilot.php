<?php

//Copyright 2013 Technical Solutions, LLC.
//Confidential & Proprietary Information.

Namespace CP ;

/**
* Responsible for compiling responses and interpreting submissions.
*
* This class is a singleton.
*/
class Copilot {

	public $log ;
	
	private $db_local ;
	private $data ;
	private $api ;

	static private $_instance = null;

	/**
	* Copilot may only be a singleton.
	*/
	public static function & Instance() {

		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}

	/**
	* CONSTRUCTOR
	*/
	public function __construct() {

		//Script timer.
		$this->mtime = microtime(); 
		$this->mtime = explode(" ",$this->mtime); 
		$this->mtime = $this->mtime[1] + $this->mtime[0]; 
		$this->starttime = $this->mtime; 
		
		// Core classes.
		$this->log = new Log() ;
		//$this->db_local = new DB($this->log, DB_HOST_LOCAL, DB_NAME_LOCAL, DB_USER_LOCAL, DB_PASS_LOCAL) ;
		$this->data = new Data($this->log) ;

		// API class.
		$this->api = new API($this->log) ;

	}

	public function ready() {

		$this->api->buildRoutes() ;
		$this->api->enableSlim() ;

		//Script timer end.
		$this->mtime = microtime(); 
		$this->mtime = explode(" ",$this->mtime); 
		$this->mtime = $this->mtime[1] + $this->mtime[0]; 
		$endtime = $this->mtime; 
		$totaltime = ($endtime - $this->starttime);
		define('SCRIPT_TIME', $totaltime) ;

		// Output
		if(DEV) {
			$this->log->add($this->getData(), CP_RESPONSE) ;
			require_once(SERVER_DOCRT.'/view/splash.php') ;
		} elseif(!DEV) {
			// return json
		}

	}

	/**
	* Function createRoute
	*
	* Public function which allows external methods to be bound to the API using api\addRoute().
	*
	* @param string $httpMethod contains the http method - i.e. get, post, put, delete.
	* @param string $requestRoute contains the url parameter which calls this route.
	* @param string $callbackMethod contains the call_user_method() compatible function name.
	*/
	public function createRoute($httpMethod, $requestRoute, $callbackMethod) {
		$this->api->addRoute($httpMethod, $requestRoute, $callbackMethod) ;
	}

	public function addData($name, $input) {
		$this->data->add($name, $input) ;
	}

	public function getData() {
		return $this->data->returnStream() ;
	}

	public function __clone() {
		trigger_error('Cloning instances of this class is forbidden.', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Unserializing instances of this class is forbidden.', E_USER_ERROR);
	}

}

?>
