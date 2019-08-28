$(document).ready(function() {
	$('#openid_btns > div > a > input').hide();
	$('#openid_input_area').hide();
	$('#openid_btns > div > a').click(function() {
		$('div.openid_selected').removeClass('openid_selected');
		$(this).parent().addClass('openid_selected').find('input').attr('checked', 'checked');
		if ($(this).hasClass('submit')) {
			$('#openid_input_area').hide();
		} else {
			$('#openid_input_area').show();
			if ($(this).hasClass('openid')) {
				$('#openid_identifier').val('http://');
			} else {
				$('#openid_identifier').val('');
			}
		}
	});
	$('a.submit').click(function() {
		if ($('input[name=provider]:checked').val() == 'facebook') {
			window.location.replace('/user/fbconnect');
		} else {
			$('#openid_form').submit();
		}
	});
});
