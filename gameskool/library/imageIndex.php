<?

	/**
	 *  imageIndex object represents an image directory with its contents indexed in the database
	 *    The save and update methods are overridden to upload an image as well as write to the db
	 *    Images are always converted to gif
	 */
	class imageIndex extends activeRecord {
		// active record table
		protected $table;
		// existing auto increment field
		protected $autoincrement = 'imageID';
		// array unique id fields
		protected $idFields = array(
			'imageID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'imageID'      => array('imageID', 'integer', 0, 10, 'Image ID'),
			'image'        => array('image', 'filename', 1, 45, 'Image'),
			'size'         => array('size', 'integer', 1, 10, 'Size'),
			'width'        => array('width', 'integer', 1, 4, 'Width'),
			'height'       => array('height', 'integer', 1, 4, 'Height'),
			'dateAdded'    => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
			'lastModified' => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);
		// images folder
		protected $imageFolder;
		// images directory
		protected $imageDir;

		/**
		 *  Override: set image directory
		 *  Args: none
		 *  Return: none
		 */
		public function initialize() {
			$this->imageDir = systemSettings::get('IMAGEDIR');
			if (!preg_match('/\/$/', $this->imageDir)) {
				$this->imageDir .= '/';
			}
			$this->imageDir = $this->imageDir.$this->imageFolder;
			if (!preg_match('/\/$/', $this->imageDir)) {
				$this->imageDir .= '/';
			}
		} // function initialize

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('imageID', NULL, false);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
			$this->set('lastModified', 'NOW()', false);
			$this->enclose('lastModified', false);
		} // function assertSaveDefaults

		/**
		 *  Set defaults for updating
		 *  Args: none
		 *  Return: none
		 */
		public function assertUpdateDefaults() {
			$this->set('lastModified', 'NOW()', false);
			$this->enclose('lastModified', false);
		} // function assertUpdateDefaults

		/**
		 *  Parent function override, check for duplicate record
		 *  Args: none
		 *  Return: (boolean) duplicate record found
		 */
		public function isDuplicate() {
			$sql = "SELECT `imageID` FROM `".$this->table."` WHERE `".$this->fields['image'][0]."` = '".prep($this->get('image'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				if ($row['imageID'] != $this->get('imageID')) {
					return true;
				}
			}
			return false;
		} // function isDuplicate

		/**
		 *  Upload an image
		 *  Args: (str) image file
		 *  Return: (boolean) success
		 */
		protected function uploadImage($file) {
			if (isset($_FILES[$file]) && $_FILES[$file]['name'] && $_FILES[$file]['error'] == 0) {
				$image = new image($file);
				// always convert to jpeg
				$image->convertImage('jpeg');
				if ($image->copyImage($this->imageDir, $this->get('image'))) {
					$this->set('size', $image->get('size'));
					$this->set('width', $image->get('width'));
					$this->set('height', $image->get('height'));
					return true;
				} else {
					trigger_error('Unable to upload image ('.$this->get('image').' from '.$file.' to '.$this->imageDir.')', E_USER_NOTICE);
				}
			}
			return false;
		} // function uploadImage

		/**
		 *  Rename an image
		 *  Args: (str) image file, (str) new name
		 *  Return: (boolean) success
		 */
		protected function renameImage($original, $new) {
			$image = new image($original, 'file', $this->imageDir);
			if ($image->exists()) {
				return $image->renameImage($this->imageDir, $new);
			}
			return false;
		} // function renameImage

		/**
		 *  Remove an image
		 *  Args: (str) image file
		 *  Return: (boolean) success
		 */
		protected function removeImage($file) {
			if (file_exists($this->imageDir.$file)) {
				if (unlink($this->imageDir.$file)) {
					return true;
				}
			}
			return false;
		} // function removeImage

		/**
		 *  Save an image index record and create its image file
		 *  Args: (str) $_FILES index of uploaded image
		 *  Return: (boolean) success
		 */
		public function save($file) {
			if (!$this->isDuplicate()) {
				if ($this->uploadImage($file)) {
					if (parent::save()) {
						return true;
					} else {
						$this->removeImage($this->get('image'));
					}
				}
				$this->addError('There was an error uploading image '.$this->get('image'));
			} else {
				$this->addError('Image '.$this->get('image').' already exists', 'duplicate');
			}
			return false;
		} // function save

		/**
		 *  Rename an image index record and its image file
		 *  Args: (str) new name
		 *  Return: (boolean) success
		 */
		public function rename($name) {
			if ($this->exists()) {
				$original = $this->get('image');
				$name = clean($name, $this->fields['image'][1]);
				if ($name && $name != $original) {
					$this->set('image', $name);
					if (!$this->isDuplicate()) {
						if ($this->renameImage($original, $name)) {
							if ($this->update()) {
								return true;
							} else {
								// could not update the database, revert
								if (!$this->renameImage($name, $original)) {
									trigger_error('Unable to revert image ('.$name.') to original ('.$original.') at '.$this->imageDir, E_USER_NOTICE);
								}
								$this->addError('An error occurred while renaming the image', 'rename');
								$this->set('image', $original);
							}
						} else {
							trigger_error('Unable to rename image ('.$original.' at '.$this->imageDir.')', E_USER_NOTICE);
							$this->addError('An error occurred while renaming the image', 'rename');
							$this->set('image', $original);
						}
					} else {
						$this->addError('Image '.$this->get('image').' already exists', 'duplicate');
						$this->set('image', $original);
					}
				}
			}
			return false;
		} // function rename

		/**
		 *  Peplace an image index image file
		 *  Args: (str) $_FILES index of uploaded image
		 *  Return: (boolean) success
		 */
		public function replace($file) {
			if ($this->exists()) {
				if (!$this->isDuplicate()) {
					if ($this->uploadImage($file)) {
						if ($this->update()) {
							return true;
						} else {
							// update fail
							trigger_error('Image index update error, unable to update on replace ('.$this->get('image').' at '.$this->imageDir.')', E_USER_NOTICE);
						}
					}
					$this->addError('An error occurred while replacing the image');
				} else {
					$this->addError('Image '.$this->get('image').' already exists', 'duplicate');
				}
			}
			return false;
		} // function replace

		/**
		 *  Delete an image index record and remove its image file
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function delete() {
			if ($this->exists()) {
				if ($this->removeImage($this->get('image'))) {
					$id = $this->getID();
					$identifier = array();
					foreach ($this->idFields as $key => $val) {
						$identifier[] = "`".$this->fields[$val][0]."` = '".prep($id[$key])."'";
					}
					$sql = "DELETE FROM `".$this->table."` WHERE ".implode(' AND ', $identifier);
					$result = $this->dbh->query($sql);
					if ($result->rowCount > 0) {
						return true;
					}
				}
			}
			return false;
		} // function delete
	} // class content

?>