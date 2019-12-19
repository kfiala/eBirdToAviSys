<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))	// Direct http entry, not via include
{
	echo '<pre>';
	print_r(getTaxonomy());
}

function getTaxonomy()
{
	$versions = curlCall('https://ebird.org/ws2.0/ref/taxonomy/versions');
	if (!$versions)
		die("Required eBird services appear to be down.");

	foreach($versions as $tx)
	{
		if ($tx->latest)
		{
			$currentVersion = $tx->authorityVer;
			break;
		}
	}

	if (!isset($currentVersion))
		die("No current taxonomy found!");

	$taxonomyFile = "taxonomy.$currentVersion.txt";
	if (file_exists($taxonomyFile))
		$taxonomy = unserialize(file_get_contents($taxonomyFile));
	else
	{
		$taxonomy = downloadTaxonomy();	
		file_put_contents($taxonomyFile,serialize($taxonomy),LOCK_EX);
	}
	
	return $taxonomy;
}

function downloadTaxonomy()
{
	$fullTaxonomy = curlCall('https://ebird.org/ws2.0/ref/taxonomy/ebird?locale=en&fmt=json');
	$taxonomy = array();
	foreach($fullTaxonomy as $entry)
	{
		$taxonomy[$entry->speciesCode] = $entry->comName;
	}
	return $taxonomy;
/*
{"sciName":"Protonotaria citrea",
"comName":"Prothonotary Warbler",
"speciesCode":"prowar",
"category":"species",
"taxonOrder":31641.0,
"bandingCodes":["PROW"],
"comNameCodes":["PRWA"],
"sciNameCodes":["PRCI"],
"order":"Passeriformes",
"familyComName":"New World Warblers",
"familySciName":"Parulidae"},
*/
}
?>