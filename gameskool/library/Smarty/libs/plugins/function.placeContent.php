<?
/**
 *  Place site content
 */
function smarty_function_placeContent($params, &$smarty) {
	$article = clean($params['name'], 'contentName');
	$smarty->registerContentResource();
	$contentBody = $smarty->fetch('content:'.$article);
	echo $contentBody;
} // function smarty_function_placeContent

?>