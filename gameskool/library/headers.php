<?

	class headers {
		/**
		 * Send no cache headers
		 * Args: none
		 * Return: none
		 */
		public static function sendNoCacheHeaders() {
			header("Expires: Thu, 15 May 2008 00:00:00 GMT");
			header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Cache-Control: max-age=0", false);
			header("Pragma: no-cache");
		} // function sendNoCacheHeaders

		/**
		 * Send headers for exporting to excel
		 * Args: none
		 * Return: none
		 */
		public static function sendExportHeaders($filename) {
			header("Expires: Thu, 15 May 2008 00:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Cache-Control: max-age=0", false);
			header("Pragma: no-cache");
			header("Content-type: application/vnd.ms-excel");
			header('Content-Disposition: attachment; filename = "'.$filename.'"');
		} // function sendAdminHeaders

		/**
		 * Send headers for downloading file
		 * Args: none
		 * Return: none
		 */
		public static function sendDownloadHeaders($filename) {
			header("Expires: Thu, 15 May 2008 00:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Cache-Control: max-age=0", false);
			header("Pragma: no-cache");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header('Content-Disposition: attachment; filename = "'.$filename.'"');
			header("Content-Transfer-Encoding: binary");
		} // function sendAdminHeaders
	} // class headers

?>