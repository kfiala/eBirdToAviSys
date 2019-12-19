<?php
function printError($message)
{
	echo PHP_EOL,"<p><span class=error>$message</span></p>",PHP_EOL;
}

function logger()
{
	$logmsg = date("Y-m-d H:i:s") . " " . str_pad($_SERVER["REMOTE_ADDR"],16) . $_SESSION[APPNAME]['REFERER'] . " ~ ";
	if (isset($_SERVER["HTTP_USER_AGENT"]))
		$logmsg .= $_SERVER["HTTP_USER_AGENT"];
	$logmsg .= "\n";

	$logfile = "logfile.txt";

	$fh = @fopen($logfile,"ab");
	if ($fh)
	{
		fwrite($fh,$logmsg);
		fclose($fh);
	}
}

function validUTF8($string)
{
	if ( mb_detect_encoding ( $string, "UTF-8", true ) )
		return $string;
	return mb_convert_encoding($string, "UTF-8", "Windows-1252");
}
?>