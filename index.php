<?php
require_once './avisys.php';
require_once './CheckList.php';
session_start();
require_once './getTaxonomy.php';
require_once './getLocation.php';
require_once 'input_form.php';
require_once './fetch_checklists.php';
require_once './generate_stream.php';
require_once './curlCall.php';
require_once './utilities.php';

const APPNAME = 'ebirdtoavisys';

$speciesLookup = getTaxonomy();

// Global variables.
$myself = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION[APPNAME]['REFERER']))
	$_SESSION[APPNAME]['REFERER'] = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");

$errormsg = array();
/* Quick exit with download */
if (isset($_POST['locButton']))
{
	$place_level = isset($_POST['place_level']) ? $_POST['place_level'] : "";

	$noplace = false;
	foreach ($place_level as $pl)
		if (!$pl) $noplace = true;
	if ($noplace)
		$errormsg[] = "Error: You must set the AviSys place type.";

	if (empty($errormsg))
	{
		$errormsg = generate_stream();
		logger();
		if (empty($errormsg))
			exit;
	}
}

?>
<!DOCTYPE HTML>
<html lang="en">

<head>
<title>eBird to AviSys checklist import</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src='ebtoav.js'></script>
<link rel="stylesheet" type="text/css" href="eBird.css">
<meta name="description" content="eBird to AviSys checklist import will convert eBird checklist files (in csv format) into an AviSys stream file with which you can import the data into AviSys." />
<meta property="og:image" content="http://avisys.info/images/ebirdtoavisys.png"/>
<meta property="og:image:width" content="215"/>
<meta property="og:image:height" content="200"/>
<meta property="og:title" content="eBird to AviSys checklist import"/>
<meta property="og:url" content="http://avisys.info/ebirdtoavisys/"/>
<meta property="og:site_name" content="eBird to AviSys checklist import"/>
<meta property="og:type" content="website"/>
<meta property="og:description" content="This site provides an easy way to import checklists from eBird into AviSys."/>

   </head>

<body>
<h1>eBird to AviSys checklist import (Version 2)</h1>

<noscript>
<p><span class=error>
Notice: javascript is disabled in your browser.
This page is minimally usable without javascript, but some features require that you
enable javascript, or use a different browser that has javascript enabled.
See <a href="http://enable-javascript.com/" target="_blank">How to enable JavaScript</a>.</span>
</p>
</noscript>

<?php
$writeTest = 'test.file.txt';
$checkWrite = touch($writeTest);
if (!$checkWrite)
{
	die('Error: The server does not have permission to write files.');
}
else
	unlink($writeTest);

if (isset($_POST['fetchButton']))
{
	$errormsg = fetch_checklists();
	if (!empty($errormsg))
	{
		foreach($errormsg as $emsg)
			printError($emsg);
		input_form();
	}
}
else
{
	foreach($errormsg as $emsg)
		printError($emsg);
	if (isset($_POST['cancelButton']) && $_POST['cancelButton'] == 'Reset')
		unset($_SESSION[APPNAME]['rawInput']);
	input_form();
}

?>
</body>
</html>
