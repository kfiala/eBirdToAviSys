<?php
function generate_stream()
{
	global $myself, $maskList, $speciesLookup;

	$errormsg = array();

	$notes = array();

	$locationData = array();

	$checklists = $_SESSION[APPNAME]['checklists'];

	$place = $_POST['place'];
	$location = $_POST['location'];
	$place_level = $_POST['place_level'];
	$ccode = $_POST['ccode'];
	$scode = $_POST['scode'];
	$glocom = $_POST['glocom'];
	$nsites = min(count($place),count($location),count($place_level),count($ccode),count($glocom));
	$merged = $_SESSION[APPNAME]['merged'];

	$stream = array();

	for ($i=0; $i<$nsites; $i++)
	{
		$ccode[$i] = strtoupper($ccode[$i]);
		$locationData[$location[$i]] = 
			new eBirdLocation($location[$i],$place[$i],$place_level[$i],$ccode[$i],$scode[$i],$glocom[$i]);
		if (!isset($maskList[$ccode[$i]]))
			$errormsg[] = "{$ccode[$i]} is not a valid country code (place {$place[$i]})";
	}

	if (!empty($errormsg))
		return $errormsg;

	foreach ($checklists as $checklist)
	{
		$locationName = validUTF8($checklist->location);

		if ($merged)
			$locationIndex = $locationName;
		else
			$locationIndex = $locationName . $checklist->effort;
		$location = $locationData[$locationIndex];

		foreach ($checklist->obs as $sighting)
		{
			if (isset($sighting->comments))
				$comments = $sighting->comments;
			else
				$comments = '';
			if ($location->comment)
				$comments = "$location->comment $comments";
			if (strlen($comments) > 80)
			{
				$fn = new FieldNote($comments,$speciesLookup[$sighting->speciesCode],$location->AviSys,$sighting->obsDt);
				$notes[] = $fn;
				$fnid = $fn->id;
			}
			else $fnid = 0;

			$species = $speciesLookup[$sighting->speciesCode];
			$lparen = strpos($species,'(',0);
			if ($lparen)
			{
				$qualifier = trim(substr($species,$lparen));
				$species = trim(substr($species,0,$lparen));
				$comments = "$qualifier $comments";
			}

			$number = $sighting->howManyStr;
			if ($number == "X")
				$number = 1;

			$stream[] = new StreamEntry($species,
				$checklist->obsDt,
				$location->AviSys,
				$location->level,
				$number,
				$location->country, 
				$location->state,
				$comments,
				$fnid
				);
		}

	}

	if (!empty($errormsg))
		return $errormsg;

	$streamfile = 'eBird_AviSys';

	$workname = session_id();

	$str_file = "$workname.str";
	$handle = fopen($str_file,"w");
	foreach(	$stream as $data )
		fwrite($handle,$data->toStream());
	fclose($handle);

	if (count($notes))
	{
		$notes_file = "$workname.fnr";
		$handle = fopen($notes_file,"wb");
		foreach( $notes as $data )
			fwrite($handle,$data->toStream());
		fclose($handle);
		$zip = new ZipArchive();
		$zipfile = "$workname.zip";
		if ($zip->open($zipfile,ZipArchive::CREATE) !== TRUE)
			die("cannot open $zipfile");
		$zip->addFile($str_file,"$streamfile.str");
		$zip->addFile($notes_file,"$streamfile.fnr");
		$zip->close();

		$filesize = filesize($zipfile);
		header("Content-type: application/zip");
		header("Content-Length: $filesize");
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$streamfile.'.zip"');
		readfile($zipfile);
		unlink($zipfile);
		unlink($notes_file);
	}
	else
	{
		$filesize = filesize($str_file);
		header("Content-type: application/octet-stream");
		header("Content-Length: $filesize");
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$streamfile.'.str"');
		readfile($str_file);
	}
//	cleanWork();
	unlink($str_file);
	return $errormsg;	// $errormsg will be empty here
}
?>
