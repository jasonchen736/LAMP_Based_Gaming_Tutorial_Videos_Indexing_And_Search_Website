<?

	if (userCore::isLoggedIn()) {
		redirect('/user');
	}

	$actions = array(
		'verify',
		'authorize'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		redirect('/user');
	}

	/**
	 *  Make authorization request to open id
	 *  Args: none
	 *  Return: none
	 */
	function verify() {
		$openid_identifier = getPost('openid_identifier');
		$provider = getPost('provider');
		switch ($provider) {
			case 'facebook':
				redirect('/user/fbconnect');
				break;
			case 'google':
				$url = 'https://www.google.com/accounts/o8/id';
				break;
			case 'yahoo':
				$url = 'http://yahoo.com';
				break;
			case 'aol':
				$url = 'http://openid.aol.com/'.$openid_identifier;
				break;
			case 'openid':
				$url = $openid_identifier;
				break;
			case 'myopenid':
				$url = 'http://'.$openid_identifier.'.myopenid.com';
				break;
			case 'livejournal':
				$url = 'http://'.$openid_identifier.'.livejournal.com';
				break;
			case 'flickr':
				$url = 'http://flickr.com/'.$openid_identifier;
				break;
			case 'technorati':
				$url = 'http://technorati.com/people/technorati/'.$openid_identifier;
				break;
			case 'wordpress':
				$url = 'http://'.$openid_identifier.'.wordpress.com';
				break;
			case 'blogger':
				$url = 'http://'.$openid_identifier.'.blogspot.com';
				break;
			case 'verisign':
				$url = 'http://'.$openid_identifier.'.pip.verisignlabs.com';
				break;
			case 'vidoop':
				$url = 'http://'.$openid_identifier.'.myvidoop.com';
				break;
			case 'verisign':
				$url = 'http://'.$openid_identifier.'.pip.verisignlabs.com';
				break;
			case 'claimid':
				$url = 'http://claimid.com/'.$openid_identifier;
				break;
			default:
				$url = NULL;
				break;
		}
		$url = preg_replace('/\/$/', '', $url);
		require_once 'Auth/OpenID/Consumer.php';
		require_once 'Auth/OpenID/MemcachedStore.php';
		$cache = cache::getInstance();
		$store = new Auth_OpenID_MemcachedStore($cache);
		$consumer = new Auth_OpenID_Consumer($store);
		$auth = $consumer->begin($url);
		if ($auth) {
			$siteURL = systemSettings::get('SITEURL');
			if (!preg_match('/^http/', $siteURL)) {
				$siteURL = 'http://'.$siteURL;
			}
			$url = $auth->redirectURL($siteURL, $siteURL.'/user/openid/action/authorize');
		} else {
			addError('c\'mon now, you submitted an unrecognized id or provider');
			$url = '/user/login';
		}
		$_SESSION['auth'] = array(
			'provider' => $provider,
			'time' => time()
		);
		redirect($url);
	} // function verify

	/**
	 *  Receive and handle open id authorization response
	 *  Args: none
	 *  Return: none
	 */
	function authorize() {
		// retrieve truncated query string from clean url rewrite
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
			preg_match('/^\/([^\/\?]+)\/([^\/\?]+)\/(.+)$/', $uri, $match);
			if (isset($match[3]) && preg_match('/^action\/authorize\?/', $match[3])) {
				$queryString = preg_replace('/^action\/authorize\?/', '', $match[3]);
				preg_match('/openid\.return_to=(http.+%3Fjanrain_nonce[^&]+)/', $queryString, $returnMatch);
				if (isset($returnMatch[1])) {
					$returnTo = urldecode($returnMatch[1]);
				} else {
					$returnTo = 'http://'.$_SERVER['HTTP_HOST'].'/user/openid/action/authorize';
				}
				$_SERVER['QUERY_STRING'] = $queryString;
			}
		}
		require_once 'Auth/OpenID/Consumer.php';
		require_once 'Auth/OpenID/MemcachedStore.php';
		$cache = cache::getInstance();
		$store = new Auth_OpenID_MemcachedStore($cache);
		$consumer = new Auth_OpenID_Consumer($store);
		$response = $consumer->complete($returnTo);
		$unsetAuth = true;
		if ($response->status == Auth_OpenID_SUCCESS) {
			$segments = parse_url(preg_replace('/\/$/', '', $response->identity_url));
			$uid = isset($segments['host']) ? $segments['host'] : '';
			$uid .= isset($segments['path']) ? $segments['path'] : '';
			$uid .= isset($segments['query']) ? '?'.$segments['query'] : '';
			// check for and attempt to login by external id
			if (userCore::externalLogin($uid)) {
				// existing user
				$user = userCore::get('user');
				if (isset($user['loginPage']) && $user['loginPage']) {
					$url = $user['loginPage'];
				} else {
					$url = '/';
				}
			} elseif (haveErrors()) {
				// there was an error with the login
				$url = '/user/login';
			} elseif (isset($_SESSION['auth'])) {
				// non existing, redirect to setup
				addSuccess('awesome! you\'ve logged in for the first time');
				addSuccess('please take a moment to set up your profile by choosing a username');
				addSuccess('you can also choose to link your profile to your email address (don\'t worry, we wont email you... unless you want us to)');
				$url = '/user/setup';
				$_SESSION['auth']['externalID'] = $uid;
				$unsetAuth = false;
			} else {
				// there was an error with the auth
				addError('there was a problem with your login, try it again');
				$url = '/user/login';
			}
		} else {
			// failed
			addError("sorry, your login failed, check your info and try again");
			$url = '/user/login';
		}
		if ($unsetAuth && isset($_SESSION['auth'])) {
			unset($_SESSION['auth']);
		}
		redirect($url);
	} // function authorize

?>
