<?

	class youtubeAPI extends feedAPI {
		protected $feedURL = 'http://gdata.youtube.com/feeds/api/videos?q=[query]&orderby=[orderby]&v=2&time=[time]&start-index=[start-index]&max-results=50&format=5';
		protected $parameters = array(
			'query' => false,
			'time' => 'today',
			'orderby' => 'relevance',
			'start-index' => 1,
		);
		protected $options = array(
			'time' => array(
				'today' => 'today',
				'this_week' => 'this_week',
				'this_month' => 'this_month',
				'all_time' => 'all_time'
			)
		);

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
							if ($element != 'image') {
								$currentEntry[$element] = (string) $node;
							} else {
								$currentEntry[$element] = (string) ${!${false} = $node[0]->attributes()}['url'];;
							}
						} else {
							$currentEntry[$element] = '';
						}
					}
					preg_match('/video:(.*)$/', $currentEntry['identifier'], $matches);
					if (isset($matches[1]) && !empty($matches[1])) {
						$currentEntry['identifier'] = $matches[1];
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
				}
				return $entries;
			}
			return false;
		} // function parseResults
	} // class youtubeAPI

?>
