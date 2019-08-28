<?

	/**
	 *  This class provides an interface to access system messages and errored fields
	 *  This class is essentially a wrapper for the following session arrays:
	 *    main messages array
	 *      $_SESSION['_systemMessages']
	 *    messages generated at run time
	 *      $_SESSION['_systemMessages']['errorMessages']
	 *      $_SESSION['_systemMessages']['successMessages']
	 *      $_SESSION['_systemMessages']['generalMessages']
	 *    errored field names
	 *      $_SESSION['_systemMessages']['errorFields']
	 */
	class systemMessages {
		/**
		 *  Initialize message arrays
		 *  Args: none
		 *  Return: none
		 */
		public static function initialize() {
			$_SESSION['_systemMessages'] = array();
			$_SESSION['_systemMessages']['errorMessages'] = array();
			$_SESSION['_systemMessages']['successMessages'] = array();
			$_SESSION['_systemMessages']['generalMessages'] = array();
			$_SESSION['_systemMessages']['errorFields'] = array();
		} // function initialize

		/**
		 *  Returns true if requested message array is not empty
		 *  Args: (str) type of message
		 *  Return: (boolean) error array is not empty
		 */
		public static function haveMessages($type) {
			if (isset($_SESSION['_systemMessages'])) {
				switch ($type) {
					case 'error':
						if (!empty($_SESSION['_systemMessages']['errorMessages'])) {
							return true;
						}
						break;
					case 'success':
						if (!empty($_SESSION['_systemMessages']['successMessages'])) {
							return true;
						}
						break;
					case 'general':
						if (!empty($_SESSION['_systemMessages']['generalMessages'])) {
							return true;
						}
						break;
					default:
						break;
				}
			}
			return false;
		} // function haveErrors

		/**
		 *  Retrieve messages of requested type
		 *  Args: (string) type of message
		 *  Return: (array) requested messages array
		 */
		public static function getMessages($type) {
			$messages = array();
			if (isset($_SESSION['_systemMessages'])) {
				switch ($type) {
					case 'error':
						$messages = $_SESSION['_systemMessages']['errorMessages'];
						break;
					case 'success':
						$messages = $_SESSION['_systemMessages']['successMessages'];
						break;
					case 'general':
						$messages = $_SESSION['_systemMessages']['generalMessages'];
						break;
					default:
						break;
				}
			}
			return $messages;
		} // function getMessages

		/**
		 *  Clear the requested messages array
		 *  Args: (string) type of message
		 *  Return: none
		 */
		public static function clearMessages($type) {
			if (isset($_SESSION['_systemMessages'])) {
				switch ($type) {
					case 'error':
						$_SESSION['_systemMessages']['errorMessages'] = array();
						break;
					case 'success':
						$_SESSION['_systemMessages']['successMessages'] = array();
						break;
					case 'general':
						$_SESSION['_systemMessages']['generalMessages'] = array();
						break;
					default:
						break;
				}
				if (empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['errorFields'])) {
					unset($_SESSION['_systemMessages']);
				}
			}
		} // function clearMessages

		/**
		 *  Add a message to the requested messages array
		 *  Args: (string) type of message, (string) message
		 *  Return: none
		 */
		public static function addMessage($type, $message) {
			if (!isset($_SESSION['_systemMessages'])) {
				self::initialize();
			}
			switch($type) {
				case 'error':
					addToArray($_SESSION['_systemMessages']['errorMessages'], (string) $message);
					break;
				case 'success':
					addToArray($_SESSION['_systemMessages']['successMessages'], (string) $message);
					break;
				case 'general':
					addToArray($_SESSION['_systemMessages']['generalMessages'], (string) $message);
					break;
				default:
					break;
			}
		} // function addMessage

		/**
		 *  Remove a message from the requested messages array
		 *  Args: (string) type of message, (string) message
		 *  Return: none
		 */
		public static function removeMessage($type, $message) {
			if (isset($_SESSION['_systemMessages'])) {
				switch($type) {
					case 'error':
						removeFromArray($_SESSION['_systemMessages']['errorMessages'], (string) $message);
						break;
					case 'success':
						removeFromArray($_SESSION['_systemMessages']['successMessages'], (string) $message);
						break;
					case 'general':
						removeFromArray($_SESSION['_systemMessages']['generalMessages'], (string) $message);
						break;
					default:
						break;
				}
				if (empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['errorFields'])) {
					unset($_SESSION['_systemMessages']);
				}
			}
		} // function removeMessage

		/**
		 *  Returns whether the error field var is empty
		 *  Args: none
		 *  Return: (boolean) true if !empty
		 */
		public static function haveErrorFields() {
			return isset($_SESSION['_systemMessages']) && !empty($_SESSION['_systemMessages']['errorFields']);
		} // function haveErrorFields

		/**
		 *  Return the error field array
		 *  Args: none
		 *  Return: (array) error fields
		 */
		public static function getErrorFields() {
			$fields = array();
			if (isset($_SESSION['_systemMessages'])) {
				$fields = array_unique($_SESSION['_systemMessages']['errorFields']);
			}
			return $fields;
		} // function getErrorFields

		/**
		 *  Clear the error fields array
		 *  Args: none
		 *  Return: none
		 */
		public static function clearErrorFields() {
			if (isset($_SESSION['_systemMessages'])) {
				$_SESSION['_systemMessages']['errorFields'] = array();
				if (empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['successMessages']) && 
					empty($_SESSION['_systemMessages']['errorFields'])) {
					unset($_SESSION['_systemMessages']);
				}
			}
		} // function clearErrorFields

		/**
		 *  Add an error field
		 *  Args: (str) field name
		 *  Return: none
		 */
		public static function addErrorField($field) {
			if (!isset($_SESSION['_systemMessages'])) {
				self::initialize();
			}
			addToArray($_SESSION['_systemMessages']['errorFields'], (string) $field);
		} // function addErrorField

		/**
		 *  Remove an error field
		 *  Args: (str) field name
		 *  Return: none
		 */
		public static function removeErrorField($field) {
			if (isset($_SESSION['_systemMessages'])) {
				removeFromArray($_SESSION['_systemMessages']['errorFields'], (string) $field);
			}
		} // function removeErrorField
	} // class systemMessages

	/**
	 *  Wrapper for systemMessages::haveMessages('error')
	 *  Args: none
	 *  Return: (boolean) have error messages
	 */
	function haveErrors() {
		return systemMessages::haveMessages('error');
	} // function haveErrors

	/**
	 *  Wrapper for systemMessages::getMessages('error')
	 *  Args: none
	 *  Return: (array) error messages array
	 */
	function getErrors() {
		return systemMessages::getMessages('error');
	} // function getErrors

	/**
	 *  Wrapper for systemMessages::clearMessages('error')
	 *  Args: none
	 *  Return: none
	 */
	function clearErrors() {
		systemMessages::clearMessages('error');
	} // function clearErrors

	/**
	 *  Wrapper for systemMessages:addMessage('error', $message)
	 *  Args: (string) error message
	 *  Return: none
	 */
	function addError($message) {
		systemMessages::addMessage('error', $message);
	} // function addError

	/**
	 *  Wrapper for systemMessages::removeMessage('error', $message)
	 *  Args: (string) error message
	 *  Return: none
	 */
	function removeError($message) {
		systemMessages::removeMessage('error', $message);
	} // function removeError

	/**
	 *  Wrapper for systemMessages::haveMessages('success')
	 *  Args: none
	 *  Return: (boolean) have success messages
	 */
	function haveSuccess() {
		return systemMessages::haveMessages('success');
	} // function haveSuccess

	/**
	 *  Wrapper for systemMessages::getMessages('success')
	 *  Args: none
	 *  Return: (array) success messages array
	 */
	function getSuccess() {
		return systemMessages::getMessages('success');
	} // function getSuccess

	/**
	 *  Wrapper for systemMessages::clearMessages('success')
	 *  Args: none
	 *  Return: none
	 */
	function clearSuccess() {
		systemMessages::clearMessages('success');
	} // function clearSuccess

	/**
	 *  Wrapper for systemMessages:addMessage('success', $message)
	 *  Args: (string) success message
	 *  Return: none
	 */
	function addSuccess($message) {
		systemMessages::addMessage('success', $message);
	} // function addSuccess

	/**
	 *  Wrapper for systemMessages::removeMessage('success', $message)
	 *  Args: (string) success message
	 *  Return: none
	 */
	function removeSuccess($message) {
		systemMessages::removeMessage('success', $message);
	} // function removeSucces

	/**
	 *  Wrapper for systemMessages::haveMessages('general')
	 *  Args: none
	 *  Return: (boolean) have genteral messages
	 */
	function haveMessages() {
		return systemMessages::haveMessages('general');
	} // function haveMessages

	/**
	 *  Wrapper for systemMessages::getMessages('general')
	 *  Args: none
	 *  Return: (array) general messages array
	 */
	function getMessages() {
		return systemMessages::getMessages('general');
	} // function getMessages

	/**
	 *  Wrapper for systemMessages::clearMessages('general')
	 *  Args: none
	 *  Return: none
	 */
	function clearMessages() {
		systemMessages::clearMessages('general');
	} // function clearMessages

	/**
	 *  Wrapper for systemMessages:addMessage('general', $message)
	 *  Args: (string) general message
	 *  Return: none
	 */
	function addMessage($message) {
		systemMessages::addMessage('general', $message);
	} // function addMessage

	/**
	 *  Wrapper for systemMessages::removeMessage('general', $message)
	 *  Args: (string) general message
	 *  Return: none
	 */
	function removeMessage($message) {
		systemMessages::removeMessage('general', $message);
	} // function removeMessage

	/**
	 *  Wrapper for systemMessages::haveErrorFields()
	 *  Args: none
	 *  Return: (boolean) true if !empty
	 */
	function haveErrorFields() {
		return systemMessages::haveErrorFields();
	} // function haveErrorFields

	/**
	 *  Wrapper for systemMessages::getErrorFields()
	 *  Args: none
	 *  Return: (array) error fields
	 */
	function getErrorFields() {
		return systemMessages::getErrorFields();
	} // function getErrorFields

	/**
	 *  Wrapper for systemMessages::clearErrorFields()
	 *  Args: none
	 *  Return: none
	 */
	function clearErrorFields() {
		systemMessages::clearErrorFields();
	} // function clearErrorFields

	/**
	 *  Wrapper for systemMessages::addErrorField($field)
	 *  Args: (str) field name
	 *  Return: none
	 */
	function addErrorField($field) {
		systemMessages::addErrorField($field);
	} // function addErrorField

	/**
	 *  Wrapper for systemMessages::removeErrorField($field)
	 *  Args: (str) field name
	 *  Return: none
	 */
	function removeErrorField($field) {
		systemMessages::removeErrorField($field);
	} // function removeErrorField

	/**
	 *  Clears all error, success, general messages as well as error fields
	 *  Args: none
	 *  Return: none
	 */
	function clearAllMessages() {
		clearErrors();
		clearSuccess();
		clearMessages();
		clearErrorFields();
	} // function clearAllMessages

?>