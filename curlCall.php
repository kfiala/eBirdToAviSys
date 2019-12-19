<?php
function curlCall($URL)
{
	require 'curlConfig.php';

	$ch = curl_init($URL);
	if (!$ch)
	{
		$e = new Exception;
		$traceback = var_export($e->getTraceAsString(), true);

		$IP = $_SERVER["REMOTE_ADDR"];
		$URL = urlencode($URL);
		error_log ( "curl failure:\n$traceback\n From $IP: URL=$URL", 1, $error_email );
		die ("<p>Sorry, unable to fetch data. eBird may be down.</p>");
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-eBirdApiToken: $apiKey"));
	curl_setopt($ch, CURLOPT_HEADER, 0);

//	echo "<p>Calling curl on $URL</p>";

	$json = curl_exec($ch);

	if ($json === false)
	{
		$ce = curl_error($ch);
		curl_close($ch);

		$e = new Exception;
		$traceback = var_export($e->getTraceAsString(), true);
		$IP = $_SERVER["REMOTE_ADDR"];
		$URL = urlencode($URL);
		error_log ( "json failure: $ce\n$traceback\n From $IP: URL=$URL", 1, $error_email );

		die("<p>Sorry, unable to fetch data: $ce. eBird may be down.</p>");
	}
	curl_close($ch);
	return json_decode($json);
}
?>