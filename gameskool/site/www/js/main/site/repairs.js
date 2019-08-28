$(function() {
	$('select[name=console]').change(function() {
		updateConsole();
	});
	$('select#systemProblemID').change(function() {
		estimateCost();
	});
	updateConsole();
});
function updateConsole() {
	var console = $('select[name=console]').val();
	$('.systemProblemOption').hide();
	$('.system_' + console).show();
	$('.system_0').show();
	if (!$('#systemProblemID option:selected').hasClass('system_' + console)) {
		$('#systemProblemOther').attr('selected', 'selected');
	}
	estimateCost();
};
function estimateCost() {
	$('.systemProblemCost').hide();
	$('#cost_' + $('#systemProblemID option:selected').attr('id')).show();
}
