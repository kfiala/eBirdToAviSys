<?php
function fetch_checklists()
{
	global $myself;

	$success = true;

	$checklists = array();
	$errormsg = array();

	if (empty($_POST['checklists']))
	{
		$errormsg[] = "Please enter one or more checklist names in &ldquo;Checklist input&rdquo; and try again.";
		return $errormsg;
	}

	$rawInput = $_POST['checklists'];

	$_SESSION[APPNAME]['rawInput'] = $rawInput;

	$rawInput = preg_replace("/[,\r\n]/"," ",$rawInput);	// Change all commas or newlines to space
	$rawInput = preg_replace("/ +/"," ",$rawInput);			// Change multiple spaces to single space
	$rawInput = explode(' ',$rawInput);

	$submissionIDs = array_map('trim',$rawInput);

	$urlPath = 'https://ebird.org/view/checklist';
	for ($i=0; $i<count($submissionIDs); $i++)
	{
		$input = $submissionIDs[$i];
		$lastslash = strrpos($input,'/');
		if ($lastslash === false)
			$submissionIDs[$i] = $input;
		else
			$submissionIDs[$i] = substr($input,$lastslash+1);
	}

	$viewURL = 'https://ebird.org/ws2.0/product/checklist/view/';
	foreach ($submissionIDs as $submissionID)
	{
		if (!$submissionID)
			continue;

		$URL = $viewURL . $submissionID;
		$obj = curlCall($URL);

		if (isset($obj->errors))
		{
			if ($obj->errors[0]->title == 'Field subId of checklistBySubIdCmd: subId is invalid.')
				$errormsg[] = "&ldquo;$submissionID&rdquo; does not appear to be a valid eBird checklist. Please correct and retry.";
			else
				$errormsg[] = "An error occurred while attempting to fetch checklist $submissionID. Please correct and retry.";
			continue;
		}

		if (empty($obj) || empty($obj->obs))
		{
			$errormsg[] = "No observations were found for checklist $submissionID. Please try again without this checklist.";
			continue;
		}

		$checklistObject = new CheckList($obj,$submissionID);
		if (isset($checklistObject->error))
		{
			$errormsg[] = $checklistObject->errorText;
			continue;
		}

		$checklists[] = $checklistObject;
	}

	$_SESSION[APPNAME]['checklists'] = $checklists;

	if (isset($_POST['merged']))
	{
		$merged = $_POST['merged'];
		$_SESSION[APPNAME]['merged'] = $merged ? 1 : 0;
	}
	else if (isset($_SESSION[APPNAME]['merged']))
		$merged = $_SESSION[APPNAME]['merged'];
	else
		$merged = true;

	$locations = array();

	foreach($checklists as $checklist)
	{
		$location = validUTF8($checklist->location);

		$Avplace = $location;
		$Avlevel = " ";

		$locationIndex = $merged ? $location : $location . $checklist->effort;
		if (!isset($locations[$locationIndex]))
		{
			$locations[$locationIndex] = new eBirdLocation($location,$Avplace,$Avlevel,$checklist->country,$checklist->state);
			if (!$merged)
				$locations[$locationIndex]->addEffort($checklist->effort);
		}
	}
	if (!empty($errormsg))
		return $errormsg;
?>
<p>The following eBird locations have been found in your upload.
As necessary, change each location name to the corresponding AviSys place name.</p>
<p><strong>Important: The AviSys place names are case-sensitive!</strong></p>
<?php
	$i = 0;
	$heredoc = <<<HEREDOC
<form method="POST" action="$myself" style="width:55em">
HEREDOC;
	echo $heredoc;
	if ($merged)
	{
		$legendLabel = 'Location';
		$eachComment = "each comment for this location";
	}
	else
	{
		$legendLabel = 'Checklist';
		$eachComment = "each comment for this checklist";
	}
	foreach ($locations as $locationIndex => $ebirdloc)
	{
		$locnum = $i+1;
		$eBird = htmlspecialchars($ebirdloc->eBird);
		$AviSys = htmlspecialchars($ebirdloc->AviSys);
		$levtype = htmlspecialchars($ebirdloc->level);
		$country = htmlspecialchars($ebirdloc->country);
		$state = htmlspecialchars($ebirdloc->state);

		if ($merged)
			$effort = '';
		else
		{
			$effort = htmlspecialchars($ebirdloc->effort);
		}


		if (trim($levtype) == '') $levtype = "Site";	// Default
		if ($levtype == "Site")		$siteselected = "selected"; else 	$siteselected = "";
		if ($levtype == "City")		$cityselected = "selected"; else		$cityselected = "";
		if ($levtype == "County")	$countyselected = "selected"; else	$countyselected = "";
		if ($levtype == "State")	$stateselected = "selected"; else	$stateselected = "";
		if ($levtype == "Nation")	$nationselected = "selected"; else	$nationselected = "";

		$heredoc = <<<HEREDOC
<fieldset>
<legend>$legendLabel $locnum</legend>
<label style="width: 15em">eBird location: <span id="eBirdLocation$i">$eBird</span><br>AviSys place:
<input oninput="placeEdit('$i')" onblur="savePlace('$i')" name="place[$i]" id="place$i" type="text" value="$AviSys" style="width:26em" autofocus /></label>
<input name="location[$i]" type="hidden" value="$locationIndex" >
<label style="margin-left:1em">Type:
<select name="place_level[$i]" id="place_level$i" style="width:6em" onchange="place_sel($i)">
<option value="">Select:</option>
<option value="Site" $siteselected>Site</option>
<option value="City" $cityselected>City</option>
<option value="County" $countyselected>County</option>
<option value="State" $stateselected>State</option>
<option value="Nation" $nationselected>Nation</option>
</select></label>
<input type="hidden" name="ccode[$i]" value="$country">
<input type="hidden" name="scode[$i]" value="$state">
<span id=placewarn[$i] class="error" style="display:none;margin-left:30em">Please select the location type</span>
<span class="error" id="toolong$i" style="display:none;"></span><br>
$effort
<br><label>Global comment:
<input name="glocom[$i]" id="glocom$i" type="text" value="" style="margin-top:1em;width:44em" maxlength=80
placeholder="Optional: info to insert in $eachComment"></label>
<script>lookupPlace($i);placeToolong($i);</script>
</fieldset>
HEREDOC;
		echo $heredoc;
		$i++;
	}
?>
<br style="clear:both" >
<?php
echo '<input type="submit" style="width:7em" value="Download" id="subbut" name="locButton" onclick="return checkType();">';
echo "<input type=hidden name='merged' value='$merged'>";
?>
<input type="submit" style="width:7em" value="Cancel" id="canbut" name="cancelButton" />
<span id=donemsg style="display:none">
Processing complete, click Reset if you'd like a blank slate to do another.
<br><input type="submit" style="width:7em" value="Retry" name="retryButton">
Click Retry if you found errors that you need to correct.
</span>
</form>
<div id=advice>
<h2>How to use this form</h2>
<p>On this screen you see each eBird location that is in your input.
<?php include "download.php"; ?>
</div>
<?php
	return;
}

?>
