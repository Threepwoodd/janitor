<?php
/**
* This file contains the site backbone, the Page Class.
* 
* It controls (based on parameters)
* - segment
* - login
* - logoff
* - language
* - dev
*
* Functions:
*
*
*
*
*
*/

/**
* Include base functions and classes
*/

include_once("include/functions.inc.php");

include_once("class/system/message.class.php");
include_once("class/system/session.class.php");

/**
* Site backbone, the Page class
*/
class PageCore {

	public $url;


	// current action - used for access validation
	private $actions;


	// page output variables
	public $page_title;
	public $page_description;
	public $body_class;
	public $content_class;



	// DB variables
	private $db_host;
	private $db_username;
	private $db_password;

	// Mailer settings
	private $mail_host;
	private $mail_port;
	private $mail_username;
	private $mail_password;
	private $mail_smtpauth;
	private $mail_secure;
	private $mail_from_email;
	private $mail_from_name;


	/**
	* Get required page information
	*/
	function __construct() {

		// database connection
		@include_once("config/connect_db.php");

		// mailer connection
		@include_once("config/connect_mail.php");


		// shorthand for clean request uri
		$this->url = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);

		// check access
		$this->setActions(RESTParams());
		
//		$this->access();

		// login in progress
		if(getVar("login") == "true") {

			// TODO: add login

		}
		// logoff
		if(getVar("logoff") == "true") {
			$this->logOff();
		}
		// set segment
		if(getVar("segment")) {
			$this->segment(getVar("segment"));
		}
		// set language
		if(getVar("language")) {
			$this->language(getVar("language"));
		}
		// set country
		if(getVar("country")) {
			$this->language(getVar("country"));
		}
		// dev mode (dev can be 0)
		if(getVar("dev") !== false) {
			Session::value("dev", getVar("dev"));
		}

	}


	/**
	* Load external template
	*
	* @param string $template Path to template
	* DEPRECATED-param string $template_object Class object to use in template
	* DEPRECATED-param string $response_column Column type classname
	* DEPRECATED-param string $container_id Id of wrapping container
	* DEPRECATED-param string $target_id If template needs to link to other target
	* DEPRECATED-param string $silent Get template without getting message (default loud)
	*/
	function template($template) {

		if(file_exists(LOCAL_PATH."/templates/".$template)) {
			$file = LOCAL_PATH."/templates/".$template;
		}
		else if(file_exists(FRAMEWORK_PATH."/templates/".$template)) {
			$file = FRAMEWORK_PATH."/templates/".$template;
		}

		if(isset($file)) {
			include($file);
		}
	}

	/**
	* Get page title
	*
	* The page title is complex
	* You can set the title manually via Page::header in your controller
	* If you don't, I will look for, prioritized:
	* - An Item title
	* - Tags - and create a list of them
	* - A Navigation item title
	* - The fallback SITE_NAME
	*
	* @return String page title
	*/
	function pageTitle($value = false) {

		// set title
		if($value !== false) {
			$this->page_title = $value;
		}
		// get title
		else {
			// if title already set
			if($this->page_title) {
				return $this->page_title;
			}

			// else if($this->actions(-1) && class_exists("Item")) {
			//  
			// 	$IC = new Item();
			// 	$item = $IC->getCompleteItem($this->actions(-1));
			// 
			// 	// update page description if not already set (since we have a rather good option at hand)
			// 	if(!$this->page_description && isset($item["description"])) {
			// 		$this->pageDescription($item["description"]);
			// 	}
			// 
			// 	return $item["name"];
			// }

			// last resort - use constant
			return SITE_NAME;
		}
	}

	/**
	* Get page description
	* Mostly for page header
	*
	* The page description is complex
	* I will look for, prioritized:
	* - An Item description
	* - Tags - TODO
	* - A Navigation item description - TODO
	* - The fallback $this->title
	*
	* @uses Page::title
	* @uses Page::getObject()
	* @return String page description
	*/
	function pageDescription($value = false) {
		// set description
		if($value !== false) {
			$this->page_description = $value;
		}
		// get description
		else {
			// TODO: look for description on product views
			// if description already set
			if($this->page_description) {
				return $this->page_description;
			}
			// Default page description from config file if available
			else if(defined(DEFAULT_PAGE_DESCRIPTION)) {
				return DEFAULT_PAGE_DESCRIPTION;
			}
			// last resort - use page title
			else {
				return $this->pageTitle();
			}
		}
	}


	/**
	* Get body class
	* this can be sat via page->header
	* 
	* @return String body class
	*/
	function bodyClass($value = false) {
		// set
		if($value !== false) {
			$this->body_class = $value;
		}
		// get
		else {
			// if body_class already set
			if($this->body_class) {
				return $this->body_class;
			}
			else {
				return "";
			}
		}
	}


	/**
	* Get content class
	* this can be sat via page->header
	* 
	* @return String body class
	*/
	function contentClass($value = false) {
		// set
		if($value !== false) {
			$this->content_class = $value;
		}
		// get
		else {
			// if body_class already set
			if($this->content_class) {
				return $this->content_class;
			}
			else {
				return "";
			}
		}
	}



	/**
	* Add page header
	*
	* @return String HTML header
	*/

	function header($options = false) {

		$type = "www";

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "type"              : $type = $value; break;

					case "body_class"        : $this->bodyClass($value); break;
					case "page_title"        : $this->pageTitle($value); break;
					case "page_descriptiton" : $this->pageDescription($value); break;
					case "content_class"     : $this->contentClass($value); break;
				}
			}
		}
	
		// TODO: check for login and server admin header

		$this->template($type.".header.php");
	}

	/**
	* Add page footer
	*
	* @return String HTML footer
	*/
	function footer($options = false) {

		$type = "www";

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "type"              : $type = $value; break;
				}
			}
		}

		$this->template($type.".footer.php");
	}


	/**
	* Get/set current language
	*
	* Pass value to set language
	*/
	function language($value = false) {
		// set
		if($value !== false) {
			Session::value("language", $value);
		}
		// get
		else {
			if(!Session::value("language")) {
				Session::value("language", defined("DEFAULT_LANGUAGE_ISO") ? DEFAULT_LANGUAGE_ISO : "DA");
			}
			return Session::value("language");
		}
	}

	/**
	* Get/set current country
	*
	* Pass value to set country
	*/
	function country($value = false) {
		// set
		if($value !== false) {
			Session::value("country", $value);
		}
		// get
		else {
			if(!Session::value("country")) {
				Session::value("country", defined("DEFAULT_COUNTRY_ISO") ? DEFAULT_COUNTRY_ISO : "DK");
			}
			return Session::value("country");
		}
	}

	/**
	* Get/set current currency
	*
	* Pass value to set currency
	*/
	function currency($value = false) {
		// set
		if($value !== false) {
			Session::value("currency", $value);
		}
		// get
		else {
			if(!Session::value("currency")) {
				$currency_id = defined("DEFAULT_CURRENCY_ISO") ? DEFAULT_CURRENCY_ISO : "DKK";

				$query = new Query();
				if($query->sql("SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$currency_id."'")) {
					$currency = $query->result(0);
				}
//				print_r($currency);

				Session::value("currency", $currency);
			}
			return Session::value("currency");
		}
	}


	/**
	* Access device API and get info about current useragent
	*
	* @return Array Array containing device info, or fallback 
	*/
	// returns currently used browser info to be stored in session
	function segment($value = false) {
		// writeToFile("segment function:" . $value);

		if($value !== false) {
			Session::value("segment", $value);
		}
		else {
			if(!Session::value("segment")) {
				// writeToFile("request new segment:" . $value);

				$device_id = @file_get_contents("http://devices.dearapi.com/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
		//		$device_id = file_get_contents("http://devices.local/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
				$device = (array) simplexml_load_string($device_id);
//				print_r($device);

				if($device && isset($device["segment"])) {
					Session::value("segment", $device["segment"]);
				}
				else {
					// offline default value
					Session::value("segment", "desktop");
				}
			}

			return Session::value("segment");
		}

	}

	/**
	* Page actions, security check on controller action level
	*
	* This function is automatically called when controller is loaded
	* The actions are validated and made available to the controller if validation is ok
	*
	* If validation fails, user is redirected to login page
	*
	* The access grants are based on path fragments
	*
	* If a user tries to access /admin/cms/save/product 
	* the system will look for for this full path in access_item of the controller
	* if the full path does not exist, one fragment will be removed until a match is found
	* Thus testing, /admin/cms/save, /admin/cms, /admin, /
	* until a match is found. 
	*
	* If no match is found, no access is granted. Default restriction when access_item is not false!
	*
	* If a match is found, it will be tested in the access table against the current users group access.
	*/
	function setActions($actions=false) {

		// TODO: Security check on action - the only required accesscheck, bacause all requests load page and page checks makes this call
		
		// TODO: Should be re-written - was made in a rush!

		// get $access_item from controller
		global $access_item;

		// if controller has access_item setting, perform access validation
		if($access_item) {

			// dummy data, only works on local DB
			Session::value("user_id", 99);
			Session::value("user_group_id", 99);


			// any access restriction requires a user to be logged in
			// no need to do any validation if no user_id or user_group_id is found
			if(!Session::value("user_id") || !Session::value("user_group_id")) {
				header("Location: /login");
				exit();
			}


			// generate appropriate validation action string to check in database
			// implode actions, prepend / and remove trailing /
			if($actions) {
				$validation_action = preg_replace("/\/$/", "", "/".implode("/", $actions));
				$controller = str_replace($_SERVER["PATH_INFO"], "", $_SERVER["REQUEST_URI"]);
			}
			// otherwise assume /
			else {
				$validation_action = "/";
				$controller = preg_replace("/\/$/", "", $_SERVER["REQUEST_URI"]);
			}


			// look for matching access entry
			while(!isset($access_item[$validation_action]) && $validation_action && $validation_action != "/") {
				$validation_action = dirname($validation_action);
			}

			// no entry found - no access
			if(!isset($access_item[$validation_action])) {
				header("Location: /login");
				exit();
			}
			else {

				// matching access item requires access check
				if($access_item[$validation_action] !== false) {

//					print_r($_SERVER);
					$query = new Query();
					$sql = "SELECT * FROM ".SITE_DB.".user_access WHERE user_group_id = ".Session::value("user_group_id")." AND action = '".$controller.$validation_action."'";
//					print $sql;
					if(!$query->sql($sql)) {
						header("Location: /login");
						exit();
					}
				}
			}
		}

		// no access_item in controller - everything is allowed
		// OR validation passed
		$this->actions = $actions;
	}


	/**
	* Page actions, security check on page action level
	*
	* -param String $action action parameter to check for in status (status can be combined page,list)
	* -return bool Page status
	*/
	function actions($index=false) {

		// TODO: Security check on action - the only required accesscheck, bacause all requests load page and page checks makes this call

		// if($this->actions) {
// 
// 			// index less than zero, count from back
// 			if($index < 0) {
// 
// 				if(isset($this->actions[count($this->actions) + $index])) {
// 					return $this->actions[count($this->actions) + $index];
// 				}
// 
// 			}
// 			// return from the normal actions order
// 			else if($index !== false && isset($this->actions[$index])) {
// 
// 				return $this->actions[$index];
// 
// 			}
// 			// return actions array
// 			else {
				return $this->actions;
		// 	}
		// }
		// 
		// return false;
	}


	/**
	* Set page status
	*
	* @param string|bool $status Page status
	*/
	function setStatus($status){
		// if(!Secuity::hasAccess($status)) {
		// 	$this->throwOff($_SERVER["REQUEST_URI"]);
		// }
		// else {
			$this->status = $status;
		// }
	}


	/**
	* Simple logoff
	* Logoff user and redirect to login page
	*/
	function logOff() {
		$this->addLog("Logoff ". UT_USE);
		//$this->user_id = "";
		Session::reset();
		header("Location: /index.php");
		exit();
	}

	/**
	* Throw off if user is caught on page without permission
	*
	* @param String $url Optional url to forward to after login
	*/
	function throwOff($url=false) {

		// TODO: Compile more information and send in email
		$this->addLog("Throwoff - insufficient privileges:".$this->url." ". UT_USE);
		$this->mail(array(
			"subject" => "Throwoff - " . SITE_URL, 
			"message" => "insufficient privileges:".$this->url, 
			"template" => "system"
		));

		//$this->user_id = "";
		Session::resetLogin();
		if($url) {
			Session::setValue("LoginForward", $url);
		}
		print '<script type="text/javacript">location.href="?page_status=logoff"</script>';
//		header("Location: /index.php");
		exit();
	}








	/**
	* Create database connection
	*/
	function db_connection($settings) {

		$this->db_host = isset($settings["host"]) ? $settings["host"] : "";
		$this->db_username = isset($settings["username"]) ? $settings["username"] : "";
		$this->db_password = isset($settings["password"]) ? $settings["password"] : "";

		@mysql_pconnect($this->db_host, $this->db_username, $this->db_password) or header("Location: /404.php?error=DB");

		// correct the database connection setting
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		
		// TODO: implement mysqli variation - requires update of Query
		// $page->mysqli = new mysqli("localhost", "hvidevarehuset", "uads34HRsdYJ");
		// print_r($page->mysqli->query("SELECT * FROM hvidevarehuset.items"));
	}

	/**
	* Create mailer connection
	*/
	function mail_connection($settings) {

		$this->mail_host = isset($settings["host"]) ? $settings["host"] : "";
		$this->mail_username = isset($settings["username"]) ? $settings["username"] : "";
		$this->mail_password = isset($settings["password"]) ? $settings["password"] : "";
		$this->mail_port = isset($settings["port"]) ? $settings["port"] : "";
		$this->mail_secure = isset($settings["secure"]) ? $settings["secure"] : "";
		$this->mail_smtpauth = isset($settings["smtpauth"]) ? $settings["smtpauth"] : "";
		$this->mail_from_email = isset($settings["from_email"]) ? $settings["from_email"] : "";
		$this->mail_from_name = isset($settings["from_name"]) ? $settings["from_name"] : "";

	}


	/**
	* send mail
	*/
	// all parameters in array structure
	// object can be any type of object providing details for email template
	// TODO: add mail templates?

	function mail($_options = false) {

		$subject = "Mail from ".SITE_URL;
		$message = "";
		$recipients = false;
		$template = false;
		$object = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "recipients" : $recipients = $_value; break;
					case "template"   : $template   = $_value; break;
					case "object"     : $object     = $_value; break;
					case "message"    : $message    = $_value; break;
					case "subject"    : $subject    = $_value; break;
				}
			}
		}

		// if no recipients - send to ADMIN
		if(!$recipients && defined("ADMIN_MAIL")) {
			$recipients = ADMIN_MAIL;
		}
		// include template
		if($template) {
			// include formatting template
			@include("templates/mails/$template.php");
		}

		// only attmempt sending if recipient is specified
		if($message && $recipients) {
			require_once("include/phpmailer/class.phpmailer.php");

			$mail             = new PHPMailer();
			$mail->Subject    = $subject;

			//$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)

			$mail->CharSet    = "UTF-8";
			$mail->IsSMTP();

			$mail->SMTPAuth   = $this->mail_smtpauth;
			$mail->SMTPSecure = $this->mail_secure;
			$mail->Host       = $this->mail_host;
			$mail->Port       = $this->mail_port;
			$mail->Username   = $this->mail_username;
			$mail->Password   = $this->mail_password;

			$mail->SetFrom($this->mail_from_email, $this->mail_from_name);
			// split comma separated list
			if(!is_array($recipients) && preg_match("/,|;/", $recipients)) {
				$recipients = preg_split("/,|;/", $recipients);
			}
			// multiple entries
			if(is_array($recipients)) {
				foreach($recipients as $recipient) {
					$mail->AddAddress($recipient);
				}
			}
			else {
				$mail->AddAddress($recipients);
			}

			$mail->Body = $message;

			return $mail->Send();
		}

		return false;
	}


	/**
	* Add log entry.
	* Adds user id and user IP along with message and optional values.
	*
	* @param string $message Log message.
	* @param string $collection Log collection.
	*/
	function addLog($message, $collection="framework") {

		// TODO: add user_id

		$timestamp = time();
		$user_ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
		$user_id = "N/A";

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		// year-month as folder
		// day as file
		$log_position = LOG_FILE_PATH."/".$collection."/".date("Y/m", $timestamp);
		$log_cursor = LOG_FILE_PATH."/".$collection."/".date("Y/m/Y-m-d", $timestamp);
		FileSystem::makeDirRecursively($log_position);

		$fp = fopen($log_cursor, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);

	}
	
	
	/**
	* collect message for bundled notification
	* Set collection size in config
	*
	* Automatically formats collection from template (if available) before sending
	*/
	function collectNotification($message, $collection="framework") {

		$collection_path = LOG_FILE_PATH."/notifications/";
		FileSystem::makeDirRecursively($collection_path);


		// TODO: add user_id

		// notifications file
		$collection_file = $collection_path.$collection;


		$timestamp = time();
		$user_ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
		$user_id = "N/A";

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		$fp = fopen($collection_file, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);


		// existing notifications
		$notifications = array();
		if(file_exists($collection_file)) {
			$notifications = file($collection_file);
		}

		// send report and reset collection
		if(count($notifications) >= (defined("SITE_COLLECT_NOTIFICATIONS") ? SITE_COLLECT_NOTIFICATIONS : 10)) {

			$message = implode("\n", $notifications);

			// include formatting template
			@include("templates/mails/notifications/$collection.php");

			// send and reset collection
			if($this->mail(array(
				"subject" => "NOTIFICATION: $collection", 
				"message" => $message
			))) {
				$fp = fopen($collection_file, "w");
				fclose($fp);
			}
		}
	}

}

?>
