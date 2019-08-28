<?

	$captchaConfig = array(
		'TTF_folder' => systemSettings::get('LIBRARYPATH').'fonts/',
		'chars' => 5,
		'minsize' => 20,
		'maxsize' => 30,
		'maxrotation' => 25,
		'noise' => true
	);
	$captcha = new captcha($captchaConfig);
	$captcha->generateCaptcha();

?>