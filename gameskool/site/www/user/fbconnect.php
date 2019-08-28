<?

	if (userCore::isLoggedIn()) {
		redirect('/user');
	}

	require_once 'fbconnect/facebook.php';

	$unsetAuth = true;
	$fb = new Facebook(systemSettings::get('FBAPIKEY'), systemSettings::get('FBSECRET'));
	if ($uid = $fb->require_login()) {
		$uid = 'fb-'.$uid;
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
		} else {
			// non existing, redirect to setup
			addSuccess('awesome! you\'ve logged in for the first time');
			addSuccess('please take a moment to set up your profile by choosing a username');
			addSuccess('you can also choose to link your profile to your email address (don\'t worry, we wont email you... unless you want us to)');
			$url = '/user/setup';
			$_SESSION['auth'] = array(
				'provider' => 'facebook',
				'time' => time(),
				'externalID' => $uid
			);
			$unsetAuth = false;
		}
	} else {
		// failed
		addError("sorry, your login failed, check your info and try again");
		$url = '/user/login';
	}
	if ($unsetAuth && isset($_SESSION['auth'])) {
		unset($_SESSION['auth']);
	}
	$fb->clear_cookie_state();
	redirect($url);

?>
