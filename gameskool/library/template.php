<?

	require_once 'Smarty/libs/Smarty.class.php';

	class template extends Smarty {
		private $_registeredResources = array(
			'email' => false,
			'content' => false
		);
		// header variables
		// assert priority placement order by specifying indexes
		private $_meta = array(
			'description' => false,
			'keywords' => false
		);
		private $_styles = array();
		private $_scripts = array();
		private $_siteTags = array(
			'header' => array(),
			'footer' => array()
		);
		// store retrieved email templates for global access
		public static $_emails = array();
		// store search params
		public static $_search = NULL;

		/**
		 *  Initiate smart and register paths
		 *  Args: (array) resource paths
		 *  Return: none
		 */
		public function __construct() {
			$this->Smarty();
			$this->initialize();
		} // function __construct

		/**
		 *  Set up resources that can be accessed while templating
		 *  Args: none
		 *  Return: none
		 */
		public function initialize() {
			$this->template_dir = systemSettings::get('TEMPLATEDIR').'source/'.systemSettings::get('SOURCEDIR');
			$this->compile_dir  = systemSettings::get('TEMPLATEDIR').'compiled';
			$this->cache_dir    = systemSettings::get('TEMPLATEDIR').'cache';
			$this->config_dir   = systemSettings::get('TEMPLATEDIR').'configs';
		} // function initialize

		/**
		 *  Set header data, then executes & displays the template results (override)
		 *  Args: (str) resource name, (str) cache id, (str) compile id
		 *  Return: none
		 */
		public function display($resource_name, $cache_id = null, $compile_id = null) {
			$this->setHeaderData();
			parent::display($resource_name, $cache_id, $compile_id);
		} // function display

		/**
		 *  Add meta data
		 *  Args: (str) meta data, (str) index
		 *  Return: none
		 */
		public function addMeta($meta, $index = false) {
			if ($index) {
				$this->_meta[$index] = $meta;
			} else {
				$this->_meta[] = $meta;
			}
		} // function addMeta

		/**
		 *  Add a script
		 *  Args: (str) html head script, (str) index
		 *  Return: none
		 */
		public function addScript($script, $index = false) {
			if ($index) {
				$this->_scripts[$index] = $script;
			} else {
				$this->_scripts[] = $script;
			}
		} // function addScript

		/**
		 *  Add a style
		 *  Args: (str) html head style, (str) index
		 *  Return: none
		 */
		public function addStyle($style, $index = false) {
			if ($index) {
				$this->_styles[$index] = $style;
			} else {
				$this->_styles[] = $style;
			}
		} // function addStyle

		/**
		 *  Assign that performs additional output escape
		 *  Args: (str) smarty assigned name, (mixed) value
		 *  Return: none
		 */
		public function assignClean($name, $value) {
			if (is_array($value)) {
				array_walk_recursive($value, 'htmlentitiesWalk');
			} else {
				$value = htmlentities($value);
			}
			$this->assign($name, $value);
		} // function assignClean

		/**
		 *  Rebuilds encoded htmlentities from a field assigned by $this->assignClean()
		 *  Sub array fields can be targetted with the syntax ">"
		 *    eg: "field1 > field2" will target $this->_tpl_vars['field1']['field2']
		 *  Args: (str) smarty assigned name, (mixed) array of entities to rebuild or string "all"
		 *  Return: none
		 */
		public function rebuildEntities($field, $entities) {
			$rebuildPatterns = array(
				'href' => array('/&lt;a href=&quot;(.*)&quot;&gt;(.*)&lt;\/a&gt;/', '<a href="\1">\2</a>')
			);
			$path = explode(' > ', $field);
			$id = '$this->_tpl_vars[\''.implode("']['", $path).'\']';
			eval('$set = isset('.$id.');');
			if ($set) {
				eval('$value = '.$id.';');
				if ($entities == 'all') {
					if (is_array($value)) {
						array_walk_recursive($value, 'htmlentitydecodeWalk');
					} else {
						$value = html_entity_decode($value);
					}
				} else {
					foreach ($entities as $entity) {
						if (array_key_exists($entity, $rebuildPatterns)) {
							$match = $rebuildPatterns[$entity][0];
							$replace = $rebuildPatterns[$entity][1];
							if (is_array($value)) {
								array_walk_recursive($value, 'rebuildEntitiesWalk', array($match, $replace));
							} else {
								$value = preg_replace($match, $replace, $value);
							}
						}
					}
				}
				eval($id.' = $value;');
			}
		} // function rebuildEntities

		/**
		 *  Assigns message arrays from systemMessages
		 *  Args: (boolean) clear all messages
		 *  Return: none
		 */
		public function getSystemMessages($clear = true) {
			$this->assignClean('haveMessages', haveMessages() || haveSuccess() || haveErrors());
			$this->assignClean('errorMessages', getErrors());
			$this->assignClean('successMessages', getSuccess());
			$this->assignClean('generalMessages', getMessages());
			$this->assignClean('errorFields', getErrorFields());
			$this->rebuildEntities('errorMessages', array('href'));
			$this->rebuildEntities('successMessages', array('href'));
			$this->rebuildEntities('generalMessages', array('href'));
			if ($clear) {
				clearAllMessages();
			}
		} // function getSystemMessages

		/**
		 *  Assign meta, script, styles, and site tags variables
		 *  Args: none
		 *  Return: none
		 */
		public function setHeaderData() {
			if (!isset($this->_meta['description']) || !$this->_meta['description']) {
				$this->addMeta('<meta name="description" content="'.systemSettings::get('METADESCRIPTION').'" />', 'description');
			}
			if (!isset($this->_meta['keywords']) || !$this->_meta['keywords']) {
				$this->addMeta('<meta name="keywords" content="'.systemSettings::get('METAKEYWORDS').'" />', 'keywords');
			}
			$this->assign('_META', $this->_meta);
			$this->assign('_STYLES', $this->_styles);
			$this->assign('_SCRIPTS', $this->_scripts);
			siteTagsController::retrieveSiteTagsByMatch();
			$this->_siteTags['header'] = siteTagsController::appendSiteTags($this->_siteTags['header'], 'header');
			$this->_siteTags['footer'] = siteTagsController::appendSiteTags($this->_siteTags['footer'], 'footer');
			$this->assign('_SITETAGS', $this->_siteTags);
		} // function setHeaderData

		/**
		 *  Retrieve and assign systems data access object
		 *  Args: none
		 *  Return: none
		 */
		public function setSystemDataGateway() {
			$systemSettings = new systemSettings;
			$this->assign('_SYSTEM', $systemSettings);
		} // function setSystemDataGateway

		/**
		 *  Register template resource for retrieving email templates
		 *    Resource call argument uses the format "email:email-name:email-field"
		 *    ex: $template->display('email:receipt:subject')
		 *  Args: none
		 *  Return: none
		 */
		public function registerEmailResource() {
			if (!$this->_registeredResources['email']) {
				$this->register_resource(
					'email',
					array(
						'get_template_email',
						'get_timestamp_email',
						'get_secure_email',
						'get_trusted_email'
					)
				);
				$this->_registeredResources['email'] = true;
			}
		} // function registerEmailResource

		/**
		 *  Return mailer object for specified email
		 *  Args: (str) email to retrieve
		 *  Return: (mailer) mailer object
		 */
		public function getMailer($email) {
			$this->registerEmailResource();
			$mailer = new mailer;
			$mailer->setMessage('subject', $this->fetch('email:'.$email.':subject'));
			$mailer->setMessage('from', $this->fetch('email:'.$email.':from'));
			$mailer->setMessage('html', $this->fetch('email:'.$email.':html'));
			$mailer->setMessage('text', $this->fetch('email:'.$email.':text'));
			return $mailer;
		} // function getMailer

		/**
		 *  Register template resource for retrieving content pages
		 *    Resource call argument uses the format "content:content name"
		 *    ex: $template->display('content:about')
		 *  Args: none
		 *  Return: none
		 */
		public function registerContentResource() {
			if (!$this->_registeredResources['content']) {
				$this->register_resource(
					'content',
					array(
						'get_template_content',
						'get_timestamp_content',
						'get_secure_content',
						'get_trusted_content'
					)
				);
				$this->_registeredResources['content'] = true;
			}
		} // function registerContentResource
	} // class template

	/**
	 *  Smarty resource get template function - retrieve email template using email name
	 *    Argument template name uses the format "email-name:email-field"
	 *    ex: receipt:subject, receipt:html, receipt:text
	 *  Args: (str) template name, (str) template source, (smarty) smarty object
	 *  Return: (boolean) success
	 */
	function get_template_email($tpl_name, &$tpl_source, &$smarty_obj) {
		list($email, $field) = explode(':', $tpl_name);
		if (isset(template::$_emails[$email])) {
			$tpl_source = template::$_emails[$email][$field];
			return true;
		} else {
			$email = clean($email);
			if ($email) {
				$sql = "SELECT `a`.`name`, `a`.`subject`, `a`.`fromEmail`, 
							`a`.`html` AS `bodyHTML`, `a`.`text` AS `bodyText`, 
							`b`.`html` AS `headerHTML`, `b`.`text` AS `headerText`, 
							`c`.`html` AS `footerHTML`, `c`.`text` AS `footerText` 
						FROM `emails` `a` 
						LEFT JOIN `emailSections` `b` ON (`a`.`headerID` = `b`.`emailSectionID`) 
						LEFT JOIN `emailSections` `c` ON (`a`.`footerID` = `c`.`emailSectionID`) 
						WHERE `a`.`name` = '".prep($email)."'";
				$result = query($sql);
				if ($result->rowCount > 0) {
					$row = $result->fetchRow();
					template::$_emails[$row['name']] = array();
					template::$_emails[$row['name']]['subject'] = $row['subject'];
					template::$_emails[$row['name']]['from'] = $row['fromEmail'];
					template::$_emails[$row['name']]['text'] = $row['headerText'].$row['bodyText'].$row['footerText'];
					template::$_emails[$row['name']]['html'] = $row['headerHTML'].$row['bodyHTML'].$row['footerHTML'];
					$tpl_source = template::$_emails[$row['name']][$field];
					return true;
				}
			}
		}
		return false;
	} // function get_template_email

	/**
	 *  Smarty resource get timestamp function
	 *  Args: (str) template name, (str) template timestamp, (smarty) smarty object
	 *  Return: (boolean) success
	 */
	function get_timestamp_email($tpl_name, &$tpl_timestamp, &$smarty_obj) {
		$tpl_timestamp = time();
		return true;
	} // function get_timestamp_email

	/**
	 *  Smarty resource get secure function
	 *  Args: (str) template name, (smarty) smarty object
	 *  Return: none
	 */
	function get_secure_email($tpl_name, &$smarty_obj) {
		// assume all templates are secure
		return true;
	} // function get_secure_email

	/**
	 *  Smarty resource get trusted function
	 *  Args: (str) template name, (smarty) smarty object
	 *  Return: none
	 */
	function get_trusted_email($tpl_name, &$smarty_obj) {
		// not used for templates
	} // function get_trusted_email

	/**
	 *  Smarty resource get template function - retrieve content as template using content name
	 *  Args: (str) template name, (str) template source, (smarty) smarty object
	 *  Return: (boolean) success
	 */
	function get_template_content($tpl_name, &$tpl_source, &$smarty_obj) {
		$result = query("SELECT `content` FROM `content` WHERE `name` = '".prep(clean($tpl_name))."' LIMIT 1");
		if ($result->rowCount > 0) {
			$row = $result->fetchRow();
			$tpl_source = $row['content'];
			return true;
		}
		return false;
	} // function get_template_content

	/**
	 *  Smarty resource get timestamp function
	 *  Args: (str) template name, (str) template timestamp, (smarty) smarty object
	 *  Return: (boolean) success
	 */
	function get_timestamp_content($tpl_name, &$tpl_timestamp, &$smarty_obj) {
		$tpl_timestamp = time();
		return true;
	} // function get_timestamp_content

	/**
	 *  Smarty resource get secure function
	 *  Args: (str) template name, (smarty) smarty object
	 *  Return: none
	 */
	function get_secure_content($tpl_name, &$smarty_obj) {
		// assume all templates are secure
		return true;
	} // function get_secure_content

	/**
	 *  Smarty resource get trusted function
	 *  Args: (str) template name, (smarty) smarty object
	 *  Return: none
	 */
	function get_trusted_content($tpl_name, &$smarty_obj) {
		// not used for templates
	} // function get_trusted_content

	/**
	 *  Array walk function for encoding html special chars
	 *  Args: (mixed) array parameter value, (str) key
	 *  Returns: none
	 */
	function htmlentitiesWalk(&$item, $key) {
		$item = htmlentities($item);
	} // function htmlentitiesWalk

	/**
	 *  Array walk function for decoding html special chars
	 *  Args: (mixed) array parameter value, (str) key
	 *  Returns: none
	 */
	function htmlentitydecodeWalk(&$item, $key) {
		$item = html_entity_decode($item);
	} // function htmlentitydecodeWalk

	/**
	 *  Array walk function for rebuilding html special chars
	 *  Args: (mixed) array parameter value, (str) key, (array) match and replace regex
	 *  Returns: none
	 */
	function rebuildEntitiesWalk(&$item, $key, $regex) {
		$match = $regex[0];
		$replace = $regex[1];
		$item = preg_replace($match, $replace, $item);
	} // function htmlentitiesWalk

?>
