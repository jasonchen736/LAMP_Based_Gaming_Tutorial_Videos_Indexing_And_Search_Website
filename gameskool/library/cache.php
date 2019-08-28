<?

	class cache extends Memcache {
		private static $instance = NULL;
		private $namespace;

		/**
		 *  Retrieve singleton cache object
		 *  Args: none
		 *  Return: none
		 */
		public static function getInstance() {
			if (!self::$instance) {
				self::$instance = new cache;
			}
			return self::$instance;
		} // function getInstance

		/**
		 *  Connect and set namespace
		 *  Args: none
		 *  Return: none
		 */
		public function __construct() {
			$this->connect(systemSettings::get('MEMCACHEHOST'), systemSettings::get('MEMCACHEPORT'));
			$this->namespace = systemSettings::get('MEMCACHENAMESPACE').'_';
		} // function __construct

		/**
		 *  Set override for namespacing
		 *  Args: (str) key, (mixed) value, (int) compression flag, (int) expire
		 *  Return: (boolean) success
		 */
		public function set($key, $value, $flag = MEMCACHE_COMPRESSED, $expire = 0) {
			return parent::set($this->namespace.$key, $value, $flag, $expire);
		} // function set

		/**
		 *  Get override for namespacing
		 *  Args: (mixed) key as str or array, (mixed) compression flag as str or array
		 *  Return: (mixed) value or false
		 */
		public function get($key, $flags = MEMCACHE_COMPRESSED) {
			return parent::get($this->namespace.$key, $flags);
		} // function get
	} // class cache

?>
