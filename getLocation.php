<?php
require_once 'curlCall.php';
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))	// Direct http entry, not via include
{
	$loc = $_GET['loc'];

	echo '<p>'.getLocation($loc).'</p>';
}

function getLocation($location)
{
	$locInfo = curlCall("https://ebird.org/ws2.0/ref/region/info/$location");
	$name = $locInfo->result;
	return $name;
}
?>