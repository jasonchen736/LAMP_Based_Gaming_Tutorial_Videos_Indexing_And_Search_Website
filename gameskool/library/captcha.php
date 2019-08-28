<?

	// modified from hn_captcha from http://hn273.users.phpclasses.org/browse/package/1569.html
	class captcha {
		// captcha key session var
		public static $sessionKey = '_captcha';
		// maximum time allowed for captcha validation
		public static $captchaTimeframe = 300;
		public $TTF_folder = '';
		public $TTF_RANGE  = array(
			'actionj.ttf',
			'bboron.ttf',
			'epilog.ttf',
			'lexo.ttf',
			'tomnr.ttf'
		);
		// holds the current selected TrueTypeFont
		private $TTF_file;
		// integer: number of chars to use for ID
		public $chars = 6;
		// integer: minimal size of chars
		public $minsize = 20;
		// integer: maximal size of chars
		public $maxsize = 40;
		// integer: define the maximal angle for char-rotation, good results are between 0 and 30
		public $maxrotation = 25;
		// boolean: TRUE = noisy chars | FALSE = grid
		public $noise = TRUE;
		// this will multiplyed with number of chars
		private $noisefactor = 9;
		// number of background-noise-characters
		private $nb_noise;
		// width of picture
		private $lx;
		// height of picture
		private $ly;
		// image quality
		private $jpegquality = 80;
		// colors
		private $r;
		private $g;
		private $b;
		
		/**
		 *  Extracts the config array and generate needed params.
		 *  Args: (array) config array
		 *  Return: none
		 */
		public function __construct($config) {
			foreach ($config as $k => $v) {
				$this->$k = $v;
			}
			if($this->minsize > $this->maxsize) {
				$temp = $this->minsize;
				$this->minsize = $this->maxsize;
				$this->maxsize = $temp;
			}		
			$this->change_TTF();
			$this->nb_noise = $this->noise ? ($this->chars * $this->noisefactor) : 0;
			$this->lx = ($this->chars + 1) * (int) (($this->maxsize + $this->minsize) / 1.5);
			$this->ly = (int) (2.4 * $this->maxsize);
		} // function __construct

		/**
		 *  Generate and output captcha image with image headers
		 *  Args: none
		 *  Return: none
		 */
		public function generateCaptcha() {
			$image = imagecreatetruecolor($this->lx, $this->ly);
			// Set Backgroundcolor
			$this->random_color(224, 255);
			$back = @imagecolorallocate($image, $this->r, $this->g, $this->b);
			@ImageFilledRectangle($image, 0, 0, $this->lx, $this->ly, $back);
			// allocates the 216 websafe color palette to the image
			$this->makeWebsafeColors($image);
			// fill with noise or grid
			if($this->nb_noise > 0) {
				// random characters in background with random position, angle, color
				for($i=0; $i < $this->nb_noise; $i++) {
					srand((double)microtime()*1000000);
					$size = intval(rand((int)($this->minsize / 2.3), (int) ($this->maxsize / 1.7)));
					srand((double)microtime()*1000000);
					$angle = intval(rand(0, 360));
					srand((double)microtime()*1000000);
					$x = intval(rand(0, $this->lx));
					srand((double)microtime()*1000000);
					$y = intval(rand(0, (int) ($this->ly - ($size / 5))));
					$this->random_color(160, 224);
					$color = imagecolorallocate($image, $this->r, $this->g, $this->b);
					srand((double)microtime()*1000000);
					$text = chr(intval(rand(45,250)));
					@ImageTTFText($image, $size, $angle, $x, $y, $color, $this->change_TTF(), $text);
				}
			} else {
				// generate grid
				for($i=0; $i < $this->lx; $i += (int) ($this->minsize / 1.5)) {
					$this->random_color(160, 224);
					$color = imagecolorallocate($image, $this->r, $this->g, $this->b);
					@imageline($image, $i, 0, $i, $this->ly, $color);
				}
				for($i=0 ; $i < $this->ly; $i += (int) ($this->minsize / 1.8)) {
					$this->random_color(160, 224);
					$color = imagecolorallocate($image, $this->r, $this->g, $this->b);
					@imageline($image, 0, $i, $this->lx, $i, $color);
				}
			}
			// generate Text
			$key = $this->generate_key();
			for($i = 0, $x = intval(rand($this->minsize, $this->maxsize)); $i < $this->chars; $i++) {
				$text = strtoupper(substr($key, $i, 1));
				srand((double)microtime()*1000000);
				$angle = intval(rand(($this->maxrotation * -1), $this->maxrotation));
				srand((double)microtime()*1000000);
				$size = intval(rand($this->minsize, $this->maxsize));
				srand((double)microtime()*1000000);
				$y = intval(rand((int) ($size * 1.5), (int) ($this->ly - ($size / 7))));
				$this->random_color(0, 127);
				$color = imagecolorallocate($image, $this->r, $this->g, $this->b);
				$this->random_color(0, 127);
				$shadow = imagecolorallocate($image, $this->r + 127, $this->g + 127, $this->b + 127);
				@ImageTTFText($image, $size, $angle, $x + (int) ($size / 15), $y, $shadow, $this->change_TTF(), $text);
				@ImageTTFText($image, $size, $angle, $x, $y - (int) ($size / 15), $color, $this->TTF_file, $text);
				$x += (int) ($size + ($this->minsize / 5));
			}
			header('Content-Type: image/jpeg');
			@ImageJPEG($image, NULL, $this->jpegquality);
		} // function generateCaptcha

		/**
		 *  Convert for web safe colors
		 *  Args: (image resource) image resource
		 *  Return: none
		 */
		private function makeWebsafeColors(&$image) {
			for($r = 0; $r <= 255; $r += 51) {
				for($g = 0; $g <= 255; $g += 51) {
					for($b = 0; $b <= 255; $b += 51) {
						$color = imagecolorallocate($image, $r, $g, $b);
					}
				}
			}
		} // function makeWebsafeColors
		
		/**
		 *  Generate and set random colors
		 *  Args: (int) min color range, (int) max color range
		 *  Return: none
		 */
		private function random_color($min, $max) {
			srand((double)microtime() * 1000000);
			$this->r = intval(rand($min, $max));
			srand((double)microtime() * 1000000);
			$this->g = intval(rand($min, $max));
			srand((double)microtime() * 1000000);
			$this->b = intval(rand($min, $max));
		} // function random_color
		
		/**
		 *  Change font
		 *  Args: none
		 *  Return: (str) font file
		 */
		private function change_TTF() {
			srand((float)microtime() * 10000000);
			$key = array_rand($this->TTF_RANGE);
			$this->TTF_file = $this->TTF_folder.$this->TTF_RANGE[$key];
			return $this->TTF_file;
		} // function change_TTF

		/**
		 *  Generate captcha key, set to session with timestamp
		 *  Args: none
		 *  Return: (str) captcha key
		 */
		private function generate_key() {
			$chars = 'abcdefghijkmnpqrstuvwxyz23456789';
			$key = '';
			for ($i = 0; $i < $this->chars; $i++) {
				$key .= $chars[(rand() % strlen($chars))];
			}
			$_SESSION[self::$sessionKey] = $key.'|'.time();
			return $key;
		} // function generate_key

		/**
		 *  Validate captcha key, must be within allowed timeframe
		 *  Args: (str) key to validate
		 *  Return: (boolean) valid
		 */
		public static function validateCaptcha($key) {
			$key = strtolower($key);
			$sessionKey = getSession(self::$sessionKey);
			unset($_SESSION[self::$sessionKey]);
			if ($sessionKey) {
				list($sessionKey, $timeSet) = explode('|', $sessionKey);
				if ($key == $sessionKey && time() < $timeSet + self::$captchaTimeframe) {
					return true;
				}
			}
			return false;
		} // function validateCaptcha
	} // class captcha

?>