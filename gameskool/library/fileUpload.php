<?

	class fileUpload {
		// file data
		private $fileData = false;

		/**
		 *  Populate file information either from file upload or local source
		 *  Args: (str) file name or index of $_FILES upload, (str) file source
		 *  Args: (str) local source directory
		 *  Return: none
		 */
		public function __construct($file, $source = 'upload', $dir = false) {
			if (!$dir) {
				$dir = '';
			}
			switch ($source) {
				case 'param':
					$this->fileData = $file;
					break;
				case 'file':
					if (file_exists($dir.$file)) {
						$this->fileData = array();
						$this->fileData['name'] = $file;
						$this->fileData['type'] = NULL;
						$this->fileData['tmp_name'] = $dir.$file;
						$this->fileData['error'] = 0;
						$this->fileData['size'] = filesize($dir.$file);
					}
					break;
				case 'upload':
				default:
					if (isset($_FILES[$file])) {
						$this->fileData = $_FILES[$file];
					}
					break;
			}
		} // function __construct
		
		/**
		 *  Return requested file data from file array
		 *  Args: (str) data request
		 *  Return: (str) file data
		 */
		public function getFileData($request) {
			if (isset($this->fileData[$request])) {
				return $this->fileData[$request];
			} else {
				return false;
			}
		} // function getFileData

		/**
		 *  Copy an uploaded file from tmp to destination
		 *  Args: (str) destination directory, (str) file name
		 *  Return: (boolean) success
		 */
		public function copyFile($destination, $name = false) {
			if ($this->fileData) {
				if (!$name) {
					$name = $this->fileData['name'];
				}
				if (!preg_match('/\/$/', $destination)) {
					$destination .= '/';
				}
				if (copy($this->fileData['tmp_name'], $destination.$name)) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} // function copyFile
	} // class fileUpload

?>