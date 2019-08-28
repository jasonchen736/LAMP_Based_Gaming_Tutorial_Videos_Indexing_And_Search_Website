$(document).ready(function() {
	$.ajax({
		url: '/process/postView',
		type: 'POST',
		data: 'postID=' + postID,
		dataType: 'html',
		success: function() {
		},
		error: function(data, textStatus) {
		}
	});
});