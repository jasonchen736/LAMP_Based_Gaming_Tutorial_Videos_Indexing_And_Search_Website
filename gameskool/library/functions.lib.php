<?

	/**
	 *  Return DEVENVIRONMENT constant
	 *  Args: none
	 *  Return: (boolean) true if dev environment
	 */
	function isDevEnvironment() {
		return DEVENVIRONMENT;
	} // function isDevEnvironment

	/**
	 *  Displays data with pre tags and header if debug mode is on and in dev environment
	 *    default display with print_r
	 *  Args: (mixed) debug data (str) header, output type
	 *  Return: none
	 */
	function debug($data, $header = '', $type = 'print_r', $class = 'debug') {
		if (isDevEnvironment() && systemSettings::get('DEBUG')) {
			switch ($class) {
				case 'error':
					$textColor = 'FFFFFF';
					$bgColor = 'FF0000';
					$borderColor = 'FFFFFF';
					break;
				case 'sql':
					$textColor = 'FFFFFF';
					$bgColor = '7784FF';
					$borderColor = 'FFFFFF';
					break;
				case 'debug':
				default:
					$textColor = '000000';
					$bgColor = 'E1E1E1';
					$borderColor = 'FFFFFF';
					break;
			}
			echo '<div style="color: #'.$textColor.';background-color: #'.$bgColor.'; border: 1px solid #'.$borderColor.'; padding: 10px; text-align: left">';
			if ($header) {
				echo '<b><u>'.strtoupper($header).'</u></b><br>';
				if ($type == 'echo') echo '<br>';
			}
			if ($type == 'echo') {
				echo $data;
			} else {
				echo '<pre>';
				print_r($data);
				echo '</pre>';
			}
			echo '</div>';
		}
	} // function debug

	/**
	 *  Strips html tags and characters pertaining to data type
	 *  Args: (str) value, (str) data type, (integer) max length
	 *  Return: (str) cleansed value
	 */
	function clean($value, $type = false, $maxLength = false) {
		// handle suffix type
		if (preg_match('/\-search$/', $type)) {
			$search = true;
			$type = preg_replace('/\-search$/', '', $type);
		} else {
			$search = false;
		}
		// set data type
		$html = false;
		$trim = true;
		switch ($type) {
			case 'address':
				$allow = '\w\d \.\-\'';
				break;
			case 'alpha':
				$allow = '\w';
				break;
			case 'alphanum':
				$allow = '\w\d';
				break;
			case 'alphanumspace':
				$allow = '\w\d ';
				break;
			case 'comment':
				$allow = '\w\d\*\. _\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				break;
			case 'contentName':
				$allow = '\w\d_';
				break;
			case 'date':
				$allow = '\d\-\/';
				break;
			case 'datetime':
				$allow = '\d\-:\/ ';
				break;
			case 'email':
				// email address
				$allow = '\w\d\.\-_@';
				break;
			case 'emailSubject':
				$allow = '{}$\w\d .\-_@\/';
				break;
			case 'filename':
				$allow = '\w\d\.\-_:\/\\\\';
				break;
			case 'functionName':
				$allow = 'a-zA-Z_\x7f-\xff';
				break;
			case 'gameTitle':
				$allow = '\w\d\*\. _\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				break;
			case 'html':
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				$html = '<a><p><div><table><td><thead><tbody><tr><strong><span><style><br><h1><h2><h3><h4><h5><h6><form><img><input><select><option><ul><ol><li><dt><dd><b><i><button><em><iframe><textarea><sub><sup><blockquote><hr>';
				break;
			case 'html-email':
				// email template content
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				$html = '<a><p><div><table><td><tr><strong><span><style><html><head><body><meta><title><br><h1><h2><h3><h4><h5><h6><form><img><input><select><option><ul><ol><li><dt><dd><b><i><button><em><iframe><textarea><sub><sup><blockquote><hr>';
				$trim = false;
				break;
			case 'integer':
				$allow = '\d\-';
				break;
			case 'metainfo':
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'\?\<\>,{}\^~`|\/\\\\';
				break;
			case 'name':
				$allow = '\w\d \.\-_';
				break;
			case 'password':
				$allow = '\w\d\*\. _\-\$&\(\)\[\]=+%#@!;:\'\?,{}\^~`|\/\\\\';
				break;
			case 'postComment':
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				$html = '<a><strong><b><u><i><br><sub><sup>';
				break;
			case 'postContent':
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				$html = '<a><p><table><td><tr><strong><em><u><i><span><br><h1><h2><h3><h4><h5><h6><ul><ol><li><sub><sup><address><pre><hr><blockquote><img>';
				break;
			case 'postTitle':
				$allow = '\w\d\*\. _\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				break;
			case 'regex':
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				break;
			case 'siteTag':
				$allow = '\w\d\*\.\s_\-\$&\(\)\[\]=+%#@!;:\'"\?\<\>,{}\^~`|\/\\\\';
				$html = true;
				break;
			case 'url':
				$allow = '\w\d\.\-_:\/\?=&;';
				break;
			case 'userName':
				$allow = '\w\d\.\-_';
				break;
			case 'word':
				$allow = '\w ';
				break;
			case 'decimal':
			case 'double':
			case 'float':
			case 'money':
				$allow = '\d\.\-';
				break;
			case 'clean':
			default:
				$allow = '\w\d \.\-_\'@';
				break;
		}
		// append suffix attributes
		if ($search) {
			$allow .= '\* ';
		}
		// strip html tags
		if (!$html) {
			$value = strip_tags($value);
		} elseif ($html !== true) {
			$value = strip_tags($value, $html);
		}
		// clean value
		$value = preg_replace('/[^'.$allow.']/', '', $value);
		if ($trim) {
			$value = trim($value);
		}
		// enforce max length
		if ($maxLength !== false) {
			if (strlen($value) > $maxLength) {
				$value = substr($value, 0, $maxLength);
			}
		}
		return $value;
	} // function clean

	/**
	 *  Array walk function for method clean
	 *  Args: (mixed) array parameter value, (str) key, (str) data type
	 *  Returns: none
	 */
	function cleanWalk(&$value, $key, $type) {
		$value = clean($value, $type);
	} // function cleanWalk

	/**
	 *  Process string to friendly url format
	 *  Args: (str) string
	 *  Return: (str) friendly url formatted string
	 */
	function friendlyURL($string) {
		return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($string)), '_');
	} // function friendlyURL

	/**
	 *  Prepares value for database query
	 *  Args: (str) value
	 *  Return: (str) quote escaped value
	 */
	function prep($str) {
		if (get_magic_quotes_gpc()) {
			return mysql_real_escape_string(stripslashes($str));
		} else {
			return mysql_real_escape_string($str);
		}
	} // function prep

	/**
	 *  Retrieves a request field value
	 *  Args: (str) request field, (str) clean type
	 *  Return: (str) request field value
	 */
	function getRequest($field, $type = false) {
		if (isset($_REQUEST[$field])) {
			if (!$type) {
				return $_REQUEST[$field];
			} else {
				return clean($_REQUEST[$field], $type);
			}
		}
		return NULL;
	} // function getRequest

	/**
	 *  Retrieves a post field value
	 *  Args: (str) post field, (str) clean type
	 *  Return: (str) post field value
	 */
	function getPost($field, $type = false) {
		if (isset($_POST[$field])) {
			if (!$type) {
				return $_POST[$field];
			} else {
				return clean($_POST[$field], $type);
			}
		}
		return NULL;
	} // function getPost

	/**
	 *  Retrieves a get field value
	 *  Args: (str) get field, (str) clean type
	 *  Return: (str) get field value
	 */
	function getGet($field, $type = false) {
		if (isset($_GET[$field])) {
			if (!$type) {
				return $_GET[$field];
			} else {
				return clean($_GET[$field], $type);
			}
		}
		return NULL;
	} // function getGet

	/**
	 *  Retrieves a cookie field value
	 *  Args: (str) cookie field, (str) clean type
	 *  Return: (str) cookie field value
	 */
	function getCookie($field, $type = false) {
		if (isset($_COOKIE[$field])) {
			if (!$type) {
				return $_COOKIE[$field];
			} else {
				return clean($_COOKIE[$field], $type);
			}
		}
		return NULL;
	} // function getCookie

	/**
	 *  Retrieves a session field value
	 *  Args: (str) session field, (str) clean type
	 *  Return: (str) session field value
	 */
	function getSession($field, $type = false) {
		if (isset($_SESSION[$field])) {
			if (!$type) {
				return $_SESSION[$field];
			} else {
				return clean($_SESSION[$field], $type);
			}
		}
		return NULL;
	} // function getSession

	/**
	 *  Redirects to page passed
	 *  Args: (str) page url
	 *  Returns: none
	 */
	function redirect($page) {
		header("Location: ".$page);
		exit();
	} // function redirect

	/**
	 *  Ensure argument is an array
	 *  Args: (array) array
	 *  Return: none
	 */
	function assertArray(&$array) {
		if (!is_array($array)) {
			$array = array();
		}
	} // function assertArray

	/**
	 *  Adds identifier to array only once, will not add if already existing
	 *  Args: (array) array to edit (str) value to add
	 *  Return: none
	 */
	function addToArray(&$array, $value) {
		if (!is_array($array)) $array = array();
		if (!in_array($value, $array)) $array[] = $value;
	} // function addToArray

	/**
	 *  Removes index/value from array if existing
	 *  Args: (array) array edit (str) value to remove
	 *  Return: none
	 */
	function removeFromArray(&$array, $value) {
		if (!is_array($array)) $array = array();
		if (in_array($value, $array)) {
			$key = array_search($value, $array);
			unset($array[$key]);
			if (!is_array($array)) $array = array();
		}
	} // function removeFromArray

	/**
	 *  Validate argument for american date/time format
	 *  Args: (str) date, (boolean) validate time format
	 *  Return: (boolean) valid
	 */
	function validDate($date, $time = false) {
		if (!$time) {
			if (preg_match('/^\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4}$/', $date)) {
				return true;
			}
		} else {
			if (preg_match('/^\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4} \d{2}:\d{2}:\d{2}$/', $date)) {
				return true;
			}
		}
		return false;
	} // function validDate

	/**
	 *  Validate argument for sql date/time format
	 *  Args: (str) date, (boolean) validate time format
	 *  Return: (boolean) valid
	 */
	function validSqlDate($date, $time = false) {
		if (!$time) {
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
				return true;
			}
		} else {
			if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
				return true;
			}
		}
		return false;
	} // function validSqlDate

	/**
	 *  Converts any format date/time to sql format Y-m-d HH:MM:SS
	 *  Args: (str) any format date, (boolean) return time as well
	 *  Return: (str) sql formatted date/time
	 */
	function dateToSql($date, $time = false) {
		if (!validSqlDate($date, $time)) {
			if (!$time) {
				return date('Y-m-d', strtotime($date));
			} else {
				return date('Y-m-d H:i:s', strtotime($date));
			}
		} else {
			return $date;
		}
	} // function dateToSql

	/**
	 *  Confirms that a number is set and is a number
	 *  Args: (mixed) number, (str) numeric data type
	 *  Return: (boolean) valid/invalid number
	 */
	function validNumber($number, $type = 'double') {
		switch ($type) {
			case 'phone':
				$regex = '/^[\d]{10,11}$/';
				break;
			case 'zip':
				$regex = '/^[0-9]{5}([- ]?[0-9]{4})?$/';
				break;
			case 'integer':
			case 'int':
				$regex = '/^[\d\-]+$/';
				break;
			case 'double':
			case 'float':
			default:
				$regex = '/^[\d\.\-]+$/';
				break;
		}
		if (isset($number) && preg_match($regex, $number)) {
			return true;
		}
		return false;
	} // function validNumber

	/**
	 *  Validates email address
	 *  Args: (str) email
	 *  Return: (boolean) valid/invalid
	 */
	function validEmail($email) {
		if (!systemSettings::get('OFFLINE')) {
			list($userName, $mailDomain) = split("@", $email);
			if (!checkdnsrr($mailDomain, "MX")) {
				return false;
			}
		}
		if (preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $email)) {
			return true;
		} else {
			return false;
		}
	} // function validEmail

	/**
	 *  Execute a query with global database object and return result object
	 *  Args: (str) query
	 *  Returns: (result) result object
	 */
	function query($query) {
		$dbh = database::getInstance();
		$result = $dbh->query($query);
		return $result;
	} // function query

	/**
	 *  Generate a random password
	 *  Args: (int) length of password, (int) password strength
	 *  Returns: (str) password
	 */
	function generatePassword($length = 9, $strength = 4) {
		$requireCaps = false;
		$requireNums = false;
		$requireOther = false;
		$availableChars = array();
		$chars = 'abcdefghijklmnopqrstuvwxyz';
		$availableChars[1] = 'chars';
		if ($strength >= 1) {
			$caps = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$availableChars[2] = 'caps';
		}
		if ($strength >= 2) {
			$caps = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$requireCaps = true;
			$availableChars[2] = 'caps';
		}
		if ($strength >= 3) {
			$nums = '0123456789';
			$requireNums = true;
			$availableChars[3] = 'nums';
		}
		if ($strength >= 4) {
			$others = '@#$%!&-_;';
			$requireOther = true;
			$availableChars[4] = 'others';
		}
		$passwordIndex = array();
		$passwordIndex[] = 'chars';
		if ($requireCaps) {
			$passwordIndex[] = 'caps';
		}
		if ($requireNums) {
			$passwordIndex[] = 'nums';
		}
		if ($requireOther) {
			$passwordIndex[] = 'others';
		}
		$indexLength = $length - count($passwordIndex);
		$numAvail = count($availableChars);
		for ($i = 0; $i < $indexLength; $i++) {
			$passwordIndex[] = $availableChars[rand(1, $numAvail)];
		}
		shuffle($passwordIndex);
		$password = '';
		foreach ($passwordIndex as $charset) {
			$password .= ${$charset}[(rand() % strlen($$charset))];
		}
		return $password;
	} // function generatePassword

	/**
	 *  Return random hex string
	 *  Args: (int) salt length
	 *  Returns: (str) salt
	 */
	function getSalt($saltLength) {
		$chars = '0123456789abcdef';
		$length = strlen($chars) - 1;
		$salt = '';
		for ($i = 0; $i < $saltLength; $i++) {
			$salt .= $chars[rand(0, $length)];
		}
		return $salt;
	} // function getSalt

	/**
	 *  Return password hash
	 *  Args: (str) password, (str) salt
	 *  Returns: (str) hash
	 */
	function generatePasswordHash($password, $salt = false) {
		if (empty($salt)) {
			$salt = getSalt(6);
		}
		$sprinkle = substr($salt, 1, -1);
		$hash = sha1($sprinkle.md5($password).$sprinkle).$salt;
		return $hash;
	} // function generatePasswordHash

	/**
	 *  Return internal url with search params
	 *  Args: (str) url
	 *  Returns: (str) url with search params appended
	 */
	function appendSearchParams($url) {
		$url = preg_replace('/\/search\/([^\/]*)/', '', $url);
		if (empty($url)) {
			if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
				$url = $_SERVER['HTTP_REFERER'];
			} else {
				$url = 'http://'.$_SERVER['HTTP_HOST'];
			}
		}
		if (!preg_match('/^https?:\/\//', $url)) {
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.preg_replace('/^\//', '', $url);
		} elseif (preg_match('/^http/', $url) && !preg_match('/^https?:\/\/'.$_SERVER['HTTP_HOST'].'/', $url)) {
			$url = 'http://'.$_SERVER['HTTP_HOST'];
		}
		$home = 'home/';
		if ($search = getRequest('search')) {
			$search = 'search/'.$search;
		}
		if (!preg_match('/\/$/', $url)) {
			$search = '/'.$search;
			$home = '/home';
		}
		if (preg_match('/^https?:\/\/'.$_SERVER['HTTP_HOST'].'\/?$/', $url) && !empty($search) && $search != '/') {
			return $url.$home.$search;
		} else {
			return $url.$search;
		}
	} // function appendSearchParams

	/**
	 *  Retrieve url headers with curl
	 *  Args: (str) url
	 *  Return: (array) headers
	 */
	function getHeadersCurl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		$response = curl_exec($ch);
		$response = split("\n", $response);
		return $response;
	} // function getHeadersCurl

?>
