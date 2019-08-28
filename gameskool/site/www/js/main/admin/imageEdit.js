var fieldNum = 1;
$(function() {
	$('#additionalImage').click(function() {
		fieldNum = fieldNum + 1;
		$('#imageFields').append('<tr class="imageFields' + fieldNum + '"><td class="top">Upload As:</td><td class="top"><input type="text" name="uploadName[' + fieldNum + ']" value="" /><a href="#" class="remove" onClick="removeImageFields(' + fieldNum + ')">Remove</a></td></tr><tr class="imageFields' + fieldNum + '"><td>File:</td><td><input type="file" name="uploadImage' + fieldNum + '" value="" /></td></tr>');
	});
});
function removeImageFields(fieldNumber) {
	$('.imageFields' + fieldNumber).remove();
	return false;
}