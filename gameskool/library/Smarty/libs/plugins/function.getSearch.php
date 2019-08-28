<?
/**
 * Return search parameters query string
 *
 * Args: (array) parameters, (object) smarty object
 * Return: (str) search parameter
 */
function smarty_function_getSearch($params, &$smarty) {
	if (is_null($smarty->_search)) {
		$smarty->_search = urlencode(htmlentities(getRequest('search')));
	}
	if (!empty($smarty->_search)) {
		if (isset($params['valueOnly'])) {
			return $smarty->_search;
		} else {
			return '/search/'.$smarty->_search;
		}
	} else {
		return '';
	}
} // function smarty_function_getSearch

?>