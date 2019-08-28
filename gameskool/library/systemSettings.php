<?

	class systemSettings {
		private static $systemSettings = array(
			'SITENAME' => array('configID' => 'site_name', 'default' => false, 'set' => false),
			'SITEURL' => array('configID' => 'site_url', 'default' => false, 'set' => false),
			'METADESCRIPTION' => array('configID' => 'meta_description', 'default' => '', 'set' => false),
			'METAKEYWORDS' => array('configID' => 'meta_keywords', 'default' => '', 'set' => false),
			'MAINADDRESS1' => array('configID' => 'main_address1', 'default' => false, 'set' => false),
			'MAINADDRESS2' => array('configID' => 'main_address2', 'default' => false, 'set' => false),
			'MAINADDRESS3' => array('configID' => 'main_address3', 'default' => false, 'set' => false),
			'MAINCITY' => array('configID' => 'main_city', 'default' => false, 'set' => false),
			'MAINSTATE' => array('configID' => 'main_state', 'default' => false, 'set' => false),
			'MAINPOSTAL' => array('configID' => 'main_postal', 'default' => false, 'set' => false),
			'MAINPHONE' => array('configID' => 'main_phone', 'default' => false, 'set' => false),
			'MAINFAX' => array('configID' => 'main_fax', 'default' => false, 'set' => false),
			'DATABASE' => array('configID' => 'database', 'default' => '', 'set' => false),
			'LIBRARYPATH' => array('configID' => 'library_path', 'default' => '/library/', 'set' => false),
			'TEMPLATEDIR' => array('configID' => 'template_dir', 'default' => '/templates/', 'set' => false),
			'IMAGEDIR' => array('configID' => 'image_dir', 'default' => '/www/images/', 'set' => false),
			'SOURCEDIR' => array('configID' => 'source_dir', 'default' => 'main', 'set' => false),
			'DEBUG' => array('configID' => 'debug', 'default' => false, 'set' => false),
			'OFFLINE' => array('configID' => 'offline', 'default' => false, 'set' => false),
			'FORCEDEVELOPMENTENVIRONMENT' => array('configID' => 'force_development_environment', 'default' => false, 'set' => false),
			'SESSIONDURATION' => array('configID' => 'session_duration', 'default' => 1800, 'set' => false),
			'COOKIEPREFIX' => array('configID' => 'cookie_prefix', 'default' => '', 'set' => false),
			'COOKIEDOMAIN' => array('configID' => 'cookie_domain', 'default' => '', 'set' => false),
			'COOKIEDURATION' => array('configID' => 'cookie_duration', 'default' => 86400, 'set' => false),
			'MAILPROTOCOL' => array('configID' => 'mail_protocol', 'default' => 'nativemail', 'set' => false),
			'MAILSERVER' => array('configID' => 'mail_server', 'default' => '', 'set' => false),
			'MAILPORT' => array('configID' => 'mail_port', 'default' => 25, 'set' => false),
			'MAILAUTHENTICATION' => array('configID' => 'mail_authentication', 'default' => false, 'set' => false),
			'MAILUSER' => array('configID' => 'mail_user', 'default' => '', 'set' => false),
			'MAILPASSWORD' => array('configID' => 'mail_password', 'default' => '', 'set' => false),
			'SPHINXPATHINDEXER' => array('configID' => 'sphinx_path_indexer', 'default' => '/usr/local/bin/indexer', 'set' => false),
			'SPHINXPATHCONFIG' => array('configID' => 'sphinx_path_config', 'default' => '/etc/sphinx.conf', 'set' => false),
			'SPHINXHOST' => array('configID' => 'sphinx_host', 'default' => 'localhost', 'set' => false),
			'SPHINXPORT' => array('configID' => 'sphinx_port', 'default' => 3312, 'set' => false),
			'SPHINXTIMEOUT' => array('configID' => 'sphinx_timeout', 'default' => 1, 'set' => false),
			'POSTINDEX' => array('configID' => 'post_index', 'default' => 'postIndex', 'set' => false),
			'POSTDELTAINDEX' => array('configID' => 'post_delta_index', 'default' => 'postIndexDelta', 'set' => false),
			'GAMETITLEINDEX' => array('configID' => 'game_title_index', 'default' => 'gameTitleIndex', 'set' => false),
			'MEMCACHEHOST' => array('configID' => 'memcache_host', 'default' => 'localhost', 'set' => false),
			'MEMCACHEPORT' => array('configID' => 'memcache_port', 'default' => '11211', 'set' => false),
			'MEMCACHENAMESPACE' => array('configID' => 'memcache_namespace', 'default' => '', 'set' => false),
			'FBAPIKEY' => array('configID' => 'fb_api_key', 'default' => '', 'set' => false),
			'FBSECRET' => array('configID' => 'fb_secret', 'default' => '', 'set' => false)
		);

		private static $mailProtocols = array(
			'nativemail' => 'Native Mail',
			'sendmail' => 'SendMail',
			'smtp' => 'SMTP'
		);

		// directory of the system backend
		private static $systemRoot;
		// directory of the site front end
		private static $siteRoot;
		// last two sections of the host name (host: www.example.com -> siteDomain: .example.com)
		private static $siteDomain;
		// contents of site_conf
		private static $site_conf;

		/**
		 *  Constructor
		 *  Args: none
		 *  Return: none
		 */
		public function __construct() {
		} // function __construct

		/**
		 *  Retrieve a system setting
		 *  Args: (str) system setting
		 *  Return: (mixed) system setting value
		 */
		public static function get($setting) {
			if (array_key_exists($setting, self::$systemSettings)) {
				return self::$systemSettings[$setting]['set'];
			}
			return NULL;
		} // function get

		/**
		 *  Read configuration file, initialize settings
		 *  Args: none
		 *  Return: none
		 */
		public static function configure() {
			self::$siteRoot = preg_replace('/(.*)\/.*$/', '$1', $_SERVER['DOCUMENT_ROOT']);
			self::$systemRoot = preg_replace('/(.*)\/.*$/', '$1', self::$siteRoot);
			$hostParts = explode('.', $_SERVER['HTTP_HOST']);
			$numParts = count($hostParts);
			self::$siteDomain = '.'.$hostParts[$numParts - 1].'.'.$hostParts[$numParts - 2];
			self::$systemSettings['COOKIEPREFIX']['default'] = $_SERVER['HTTP_HOST'];
			self::$systemSettings['COOKIEDOMAIN']['default'] = self::$siteDomain;
			self::readConfig();
			self::establishSystemSettings();
			self::establishDevEnvironment();
		} // function configure

		/**
		 *  Read configuration file into internal var
		 *  Args: none
		 *  Return: (boolean) success
		 */
		private static function readConfig() {
			require_once self::$siteRoot.'/site_conf';
			self::$site_conf = $_siteConfig;
		} // function readConfig

		/**
		 *  Parse and set all system settings
		 *  Args: none
		 *  Return: none
		 */
		private static function establishSystemSettings() {
			foreach (self::$systemSettings as $key => &$val) {
				if (self::$site_conf[$val['configID']] !== 'default') {
					$val['set'] = self::$site_conf[$val['configID']];
				} else {
					$val['set'] = self::getSettingDefault($key, $val['default']);
				}	
			}
		} // function establishSystemSettings

		/**
		 *  Get setting default, perform necessary preprocessing to specific settings
		 *  Args: (str) name of setting, (mixed) default value
		 *  Return: (mixed) default value
		 */
		private static function getSettingDefault($constant, $default) {
			switch ($constant) {
				case 'TEMPLATEDIR':
				case 'IMAGEDIR':
					$value = self::$siteRoot.$default;
					break;
				case 'LIBRARYPATH':
					$value = self::$systemRoot.$default;
					break;
				default:
					$value = $default;
					break;
			}
			return $value;
		} // function getSettingDefault

		/**
		 *  Define DEVENVIRONMENT constant
		 *  Args: none
		 *  Return: none
		 */
		private static function establishDevEnvironment() {
			if (self::get('FORCEDEVELOPMENTENVIRONMENT')) {
				define('DEVENVIRONMENT', true);
			} else {
				define('DEVENVIRONMENT', false);
			}
		} // function establishDevEnvironment

		/**
		 *  Return debug mode
		 *  Args: none
		 *  Return: (boolean) debug mode
		 */
		public static function isDev() {
			return isDevEnvironment() && systemSettings::get('DEBUG');
		} // function isDev
	} // class systemSettings

?>
