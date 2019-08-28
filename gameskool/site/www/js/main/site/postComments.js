$(document).ready(function() {
	if (typeof(comment) != 'undefined') {
			$('#commentText' + comment).focus();
			window.scrollBy(0, 200);
	}
});