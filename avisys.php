<?php
require_once('maskList.php');

class eBirdLocation
{
	var $eBird, $AviSys, $level, $country, $comment;

	function __construct($eBirdName, $AviSysName, $level, $country, $state, $comment="")
	{
		$this->eBird = $eBirdName;
		$this->AviSys = $AviSysName;
		$this->level = $level;
		$this->country = $country;
		$this->state = $state;
		$this->comment = $comment;
	}

	function addEffort($effort)
	{
		$this->effort = $effort;
	}
}

class StreamEntry
{
	var $species_code, $field_note, $dec_date, $place_level, $country_code, $state_code, $continentMask;
	var $comment, $species_name, $place, $count;

	function __construct($species_name, $date, $place, $place_level, $count=1, $country_code="US", $state_code = '', $comment="", $field_note=0)
	{
		global $maskList;

		if ( mb_detect_encoding($place, "UTF-8", true) ) // Encoding must be Windows-1252 for AviSys
			$place = mb_convert_encoding($place, "Windows-1252", "UTF-8");

		if ( mb_detect_encoding($comment, "UTF-8", true) ) // Encoding must be Windows-1252 for AviSys
			$comment = mb_convert_encoding($comment, "Windows-1252", "UTF-8");

		if ( mb_detect_encoding($field_note, "UTF-8", true) ) // Encoding must be Windows-1252 for AviSys
			$field_note = mb_convert_encoding($field_note, "Windows-1252", "UTF-8");

		$this->species_name=trim($species_name);
		$this->place=$place;	// was: trim($place); but AviSys supports trailing blanks in place names
		$this->place_level = trim($place_level);
		$this->count = trim($count);

		$country_code = strtoupper(trim($country_code));
		$state_code = strtoupper(trim($state_code));
		if ($country_code == 'US')
		{
			if ($state_code == 'AK')
				$country_code = 'AK';	// Special case for Alaska
			if ($state_code == 'HI')
				$country_code = 'UH';	// Special case for Hawaii
		}


		if (strlen($country_code) > 3)
			$country_code = substr(trim($country_code),0,3);	// 3 not 2 in case it is 'RSW' or 'RSE'
		$country_code = rtrim($country_code,'-');				// In case, e.g., US-NC was reduced to US-
		$this->country_code = $country_code;

		$this->comment = trim($comment);
		$this->field_note = trim($field_note);

		$this->species_code = 1000;	// Arbitrary value; AviSys does not use the value.

		if (isset($maskList[$country_code]))
		{
			$this->continentMask = $maskList[$country_code];
			if ($country_code == 'AK')
				$this->country_code = 'US';
			else if ($country_code == 'RSW' || $country_code == 'RSE')
				$this->country_code = 'RS';
		}
		else
		{
			$this->continentMask = '0000000000';
		}

/*	From the strtotime documentation:
Dates in the m/d/y or d-m-y formats are disambiguated by looking at the separator between the various components: if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed.

Unfortunately eBird uses the American style with dash separators sometimes, so I have to change dashes to slashes.
*/
		if (strlen($date) == 10)
		{
			$newdate = preg_replace("/(\d{2})-(\d{2})-(\d{4})/","$1/$2/$3",$date);
			if ($newdate != $date)
				$date = $newdate;
		}

		/* I've seen a case where a user had dates in the format yyyy.mm.dd, so fix that too,
			by changing to yyyy-mm-dd. */
		if (strlen($date) == 10)
		{
			$newdate = preg_replace("/(\d{4})\.(\d{2})\.(\d{2})/","$1-$2-$3",$date);
			if ($newdate != $date)
				$date = $newdate;
		}		

		$date = strtotime($date);
		$year = date("Y",$date) - 1930;
		$daystring = date("md",$date);
		$date = $year . $daystring;

		if ($year < 0)
			$year = 0;
		else if ($year >= 100)
			$year = $year % 100;

		$this->dec_date = $date;

		if ($this->species_name) $this->species_name = substr($this->species_name,0,36);
		if ($this->place) $this->place = substr($this->place,0,30);
		if ($this->comment) $this->comment = substr($this->comment,0,80);
// The place number needs to be set within the range for the level, but the exact value is not used.
// I set the value to the highest number for the level.
		switch($this->place_level)
		{
			case "Site":	$this->place_level =  450; break;
			case "City":	$this->place_level =  900; break;
			case "County":	$this->place_level = 1350; break;
			case "State":	$this->place_level = 1800; break;
			case "Nation":	$this->place_level = 2250; break;
			default:	$this->place_level = 0;
		}
	}

/*
unsigned little-endian long:	"V":		0
unsigned little-endian short:	"v":		species code
unsigned little-endian short:	"v":		field note id
unsigned little-endian short:	"v":		0
										"V":		date
unsigned little-endian short: "v": 		place level

unsigned char						"C":		2 (country code length)
SPACE-padded string				"A2":		country code
hex string, high nibble first	"H10":	continent mask
unsigned little-endian long:	"V":		0

unsigned char						"C":		comment length
SPACE-padded string				"A80":	comment (80 chars)

unsigned little-endian short:	"v":		count
unsigned char						"C":		species name length
SPACE-padded string				"A36":	species name (36 chars)
unsigned char						"C":		place name length
SPACE-padded string				"A30":	place name (30 chars)
SPACE-padded string				"A4":		"END!"
*/
	function toStream()
	{
		$stream = pack("VvvvVvCA2H10VCA80vCA36CA30A4",
			0,$this->species_code,$this->field_note,0,$this->dec_date,$this->place_level,
			2, $this->country_code, $this->continentMask, 0, strlen($this->comment),
			str_pad($this->comment,80), $this->count,
			strlen($this->species_name), str_pad($this->species_name,36), strlen($this->place), str_pad($this->place,30),
			"END!");
		return $stream;
	}

}
class FieldNote
{
	var $id, $comment,$species,$place,$date;

	function __construct($comment,$species,$place,$date)
	{
		$file = dirname(__FILE__).'/fncounter.txt';
		if (file_exists($file))
			$fp = fopen($file,"r+");
		else
		{
			$fp = fopen($file,"w");
			$counter = 0;
		}
		if (flock($fp, LOCK_EX))
		{
			if (!isset($counter))
			{
				$counter = (int)fgets($fp);
				fseek($fp,0);
			}
			if ($counter < 2147483647)
				$counter++;
			else
				$counter = 0;
			fwrite($fp,"$counter\n");
			fflush($fp);            // flush output before releasing the lock
			flock($fp, LOCK_UN);    // release the lock
		}
		fclose($fp);

		$this->id = $counter;
		$this->comment = $comment;
		$this->species = $species;
		$this->place = $place;
		$this->date = $date;
	}

	function toStream()
	{
		$MAX_NOTE_LINES=60;

		$comment = wordwrap($this->comment,72);
		$line = explode("\n",$comment);
		for ($i=0; $i<count($line); $i++)
		{
			$line[$i] = trim($line[$i]);
			$line[$i] = pack("CA124",strlen($line[$i]),$line[$i]);
		}
		$stream = pack("VV",0,$this->id);

		$header = "{$this->species} :: {$this->place} :: {$this->date}";
		if (strlen($header) <= 72)
			$header = str_pad($header, 72, " ", STR_PAD_BOTH);
		$headerLine[] = pack("CA124",strlen($header),$header);

		$line = array_merge($headerLine,$line);
		$lines = min(count($line),60);
		for ($i=0; $i<$lines; $i++)
			$stream .= $line[$i];
		$nullline = pack("CA124",0," ");
		for ($i=$lines; $i < $MAX_NOTE_LINES; $i++)
			$stream .= $nullline;
		return $stream;
	}
}

?>
