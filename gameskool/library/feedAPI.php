<?

	class feedAPI {
		protected $feedURL = false;
		protected $parameters = array();
		protected $options = array();
		protected $requiredTerms = false;
		protected $rejectTerms = false;
		protected $response = false;
		protected $replace = array();
		protected $paths = array(
			'entry'       => false,
			'gameTitle'   => false,
			'identifier'  => false,
			'postTitle'   => false,
			'url'         => false,
			'image'       => false,
			'description' => false,
			'content'     => false
		);

		/**
		 *  Retrieve active feeds to run
		 *  Args: none
		 *  Return: (array) feeds
		 */
		public static function retrieveFeeds() {
			$intervals = array('daily');
			$day = date('N');
			if ($day == '6') {
				$intervals[] = 'weekly';
			}
			$week = date('W');
			if ($week % 2 == 0) {
				$intervals[] = 'bi weekly';
			}
			$day_of_month = date('j');
			if ($day_of_month == 1) {
				$intervals[] = 'monthly';
			}
			$month = date('n');
			if ($month % 2 == 0) {
				$intervals[] = 'bi monthly';
			}
			if ($month % 6 == 0) {
				$intervals[] = '6 months';
			}
			$sql = "SELECT * 
				FROM `feeds` 
				WHERE `status` = 'active' 
				AND `interval` IN ('".implode("', '", $intervals)."') 
				ORDER BY `priority` ASC";
			$result = query($sql);
			return $result->fetchAll();
		} // function retrieveFeeds

		/**
		 *  Execute and post from feeds
		 *  Args: (array) feeds, (str) mode
		 *  Return: (int) results posted
		 */
		public static function runFeeds($feeds, $mode = false) {
			switch ($mode) {
				case 'importYoutube':
					$status = 'active';
					break;
				default:
					$status = 'disabled';
					break;
			}
			$posted = 0;
			$games = array();
			foreach ($feeds as $feed) {
				switch ($feed['source']) {
					case 'Youtube':
						$api = new youtubeAPI;
						break;
					default:
						$api = new feedAPI;
						break;
				}
				if ($feed['url']) {
					$api->setURL($feed['url']);
				}
				if ($feed['parameters']) {
					$parameters = explode(',', $feed['parameters']);
					foreach ($parameters as $parameter) {
						list($field, $value) = split('->', $parameter);
						$api->setParameter($field, $value);
					}
				}
				if ($feed['replace']) {
					$replacements = explode(',', $feed['replace']);
					foreach ($replacements as $replacement) {
						list($search, $replace) = split('->', $replacement);
						$api->setReplace($search, $replace);
					}
				}
				$api->setPath('entry', $feed['entryPath']);
				$api->setPath('gameTitle', $feed['gameTitlePath']);
				$api->setPath('identifier', $feed['identifierPath']);
				$api->setPath('postTitle', $feed['postTitlePath']);
				$api->setPath('url', $feed['urlPath']);
				$api->setPath('image', $feed['imagePath']);
				$api->setPath('description', $feed['descriptionPath']);
				$api->setPath('content', $feed['contentPath']);
				$api->setRequiredTerms($feed['require']);
				$api->setRejectTerms($feed['reject']);
				if ($api->query()) {
					if ($entries = $api->parseResults()) {
						foreach ($entries as $entry) {
							if ($feed['gameTitleID']) {
								$gameTitleID = $feed['gameTitleID'];
							} elseif (isset($games[$entry['gameTitle']])) {
								$gameTitleID = $games[$entry['gameTitle']];
							} else {
								$game = preg_replace('/\s+/', ' ', clean($entry['gameTitle'], 'gameTitle'));
								$gameURL = friendlyURL($game);
								$gameTitleID = gameTitlesController::getTitleID($gameURL);
								if (!$gameTitleID) {
									$gameTitle = new gameTitle;
									$gameTitle->set('gameTitle', $game);
									$gameTitle->set('gameTitleURL', $gameURL);
									$gameTitle->set('gameTitleKey', strtolower($game));
									if ($gameTitle->save()) {
										$gameTitleID = $gameTitle->get('gameTitleID');
									}
								}
								$games[$entry['gameTitle']] = $gameTitleID;
							}
							if ($gameTitleID) {
								$post = new post;
								$post->set('type', $feed['postType']);
								$post->setTypeRequirements();
								$post->set('source', $feed['source']);
								$post->set('identifier', $entry['identifier']);
								$post->set('gameTitleID', $gameTitleID);
								$post->set('postTitle', preg_replace('/\s+/', ' ', $entry['postTitle']));
								$post->set('postTitleURL', friendlyURL($post->get('postTitle')));
								$post->set('url', $entry['url']);
								$post->set('image', $entry['image']);
								$post->set('description', $entry['description']);
								$post->set('content', $entry['content']);
								$post->set('status', $status);
								if ($feed['poster'] && $feed['posterID']) {
									$post->setRecordEditor($feed['poster'], $feed['posterID']);
								}
								if ($post->assertValidData() && !$post->isDuplicate()) {
									$post->assertTypeValues();
									if ($post->save()) {
										++$posted;
									}
								}
							}
						}
					}
				}
			}
			return $posted;
		} // function runFeeds

		/**
		 *  Costruct
		 *  Args: none
		 *  Return: none
		 */
		public function __construct() {
		} // function __construct

		/**
		 *  Retrieve options
		 *  Args: (str) field
		 *  Return: (array) options
		 */
		public function getOptions($field) {
			if (isset($this->options[$field])) {
				return $this->options[$field];
			}
			return false;
		} // function getOptions

		/**
		 *  Make a curl web request to url
		 *  Args: (str) post info url
		 *  Return: (str) curl response
		 */
		private function issueRequest($url) {
			$ch = curl_init($url);
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		} // function issueRequest

		/**
		 *  Set feed url
		 *  Args: (str) url
		 *  Return: none
		 */
		public function setURL($url) {
			$this->feedURL = $url;
		} // function setURL

		/**
		 *  Set parameter
		 *  Args: (str) parameter, (str) value
		 *  Return: none
		 */
		public function setParameter($parameter, $value) {
			if (isset($this->parameters[$parameter])) {
				if (isset($this->options[$parameter])) {
					if ($this->options[$parameter][$value]) {
						$this->parameters[$parameter] = $this->options[$parameter][$value];
					}
				} else {
					$this->parameters[$parameter] = $value;
				}
			}
		} // function setParameter

		/**
		 *  Set xml string replacements
		 *  Args: (str) search, (str) replace
		 *  Return: none
		 */
		public function setReplace($search, $replace) {
			$this->replace[$search] = $replace;
		} // function setReplace

		/**
		 *  Terms required to be present for video to qualify for category match
		 *  Args: (str) comma separated terms
		 *  Return: none
		 */
		public function setRequiredTerms($terms) {
			if (!empty($terms)) {
				$terms = explode(',', $terms);
				foreach ($terms as $term) {
					$this->requiredTerms[] = trim($term);
				}
			}
		} // function setRequiredTerms

		/**
		 *  Terms for rejecting a video
		 *  Args: (str) comma separated terms
		 *  Return: none
		 */
		public function setRejectTerms($terms) {
			if (!empty($terms)) {
				$terms = explode(',', $terms);
				foreach ($terms as $term) {
					$this->rejectTerms[] = trim($term);
				}
			}
		} // function setRejectTerms

		/**
		 *  Set xml path for element
		 *  Args: (str) path name, (str) path
		 *  Return: none
		 */
		public function setPath($pathName, $path) {
			if (isset($this->paths[$pathName])) {
				$this->paths[$pathName] = $path;
			}
		} // function setPath

		/**
		 *  Query and process results to xml
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function query() {
			$url = $this->feedURL;
			foreach ($this->parameters as $parameter => $value) {
				$url = str_replace('['.$parameter.']', urlencode($value), $url);
			}
			$response = $this->issueRequest($url);
			if (!empty($response)) {
				foreach ($this->replace as $value => $replace) {
					$response = str_replace($value, $replace, $response);
				}
				$this->response = simplexml_load_string($response);
				return !empty($this->response);
			}
			return false;
		} // function query

		/**
		 *  Parse and retrieve post information from query result
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function parseResults() {
			if ($this->response) {
				$entries = array();
				$requiredRegex = !empty($this->requiredTerms) ? implode('|', $this->requiredTerms) : false;
				$rejectRegex = !empty($this->rejectTerms) ? implode('|', $this->rejectTerms) : false;
				$paths = $this->paths;
				unset($paths['entry']);
				foreach ($this->response->{$this->paths['entry']} as $entry) {
					$currentEntry = array();
					foreach ($paths as $element => $path) {
						if ($path) {
							$node = $entry;
							$nodes = explode('->', $path);
							foreach ($nodes as $location) {
								$node = $node->{$location};
							}
							$currentEntry[$element] = (string) $node;
						} else {
							$currentEntry[$element] = '';
						}
					}
					$allContent = implode(' ', $currentEntry);
					if ($requiredRegex) {
						preg_match('/'.$requiredRegex.'/i', $allContent, $require);
					} else {
						$require = true;
					}
					if ($rejectRegex) {
						preg_match('/'.$rejectRegex.'/i', $allContent, $reject);
					} else {
						$reject = false;
					}
					if (!empty($require) && empty($reject)) {
						$currentEntry['postTitle'] = substr($currentEntry['postTitle'], 0, 255);
						$currentEntry['description'] = substr($currentEntry['description'], 0, 255);
						$entries[] = $currentEntry;
					}
				}
				return $entries;
			}
			return false;
		} // function parseResults
	} // class feedAPI

?>
