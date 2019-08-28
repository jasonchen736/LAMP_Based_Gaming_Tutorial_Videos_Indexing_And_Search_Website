function formatItem(row) {
	if (row[0] == 'format-1') {
		row[0] = row[1];
		return "ID: " + row[1] + " - " + row[2] + " - " + row[3];
	} else if (typeof(row[1]) != 'undefined') {
		return row[1];
	} else {
		return row[0];
	}
}
$(function() {
	$('.editMenuOption').mouseover(function() {
		$(this).addClass('editMenuOptionOver');
	}).mouseout(function() {
		$(this).removeClass('editMenuOptionOver');
	}).click(function() {
		$('.editMenuOption').removeClass('selected');
		$(this).addClass('selected');
		$('.propertyContainer').addClass('hidden');
		$('#' + $(this).attr('id') + 'Container').removeClass('hidden');
		$('#propertyMenuItem').val($(this).attr('id'));
	});
	$('#gameTitle').autocomplete('/admin/autocomplete', {extraParams: {type:'gameTitle'}, delay:0, minChars:1, formatItem:formatItem});
});