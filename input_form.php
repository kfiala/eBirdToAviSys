<?php
function input_form()
{
	global $myself;
?>
<p style="color:red;"><strong>Note:</strong>
To be current with eBird, install the <a href="http://avisys.info/update/">current Taxonomy Update for AviSys</a>.
</p>
<div>
</div>


<form method="POST" action="<?php echo $myself;?>" name="upform">
<fieldset style="max-width:40em;float:left;">
<legend>Checklist input</legend>


<div id=buttons>
<textarea id="maininput" name="checklists" cols="65" rows="6">
<?php
	if (isset($_SESSION[APPNAME]['rawInput']))
		echo $_SESSION[APPNAME]['rawInput'];

	$merged = isset($_SESSION[APPNAME]['merged']) ? $_SESSION[APPNAME]['merged'] : true;
	$mergedON = $merged ? 'checked' : '';
	$mergedOFF = $merged ? '' : 'checked';
?>
</textarea>
</div>







<div class="conspicuous">
<p id="patience" style="display:none;text-align:center">Fetching checklists from eBird... be patient</p>
</div>



<input type="submit" style="width:5.5em;" value="Go!" name="fetchButton"
	onclick="document.getElementById('patience').style.display = 'block'; return true;">
Click to fetch the specified checklist(s) from eBird.


</fieldset>

<fieldset style="float: left">
	<legend>Options</legend>
	Summarize by<br>

<label><input type="radio" name="merged" value="1" <?php echo $mergedON;?>/>Location</label>
<br>
<label><input type="radio" name="merged" value="0" <?php echo $mergedOFF;?> />Checklist</label>
<br><a href="#summarize"><span onmouseover="sumHI();">What's this?</span></a>
<script>function sumHI(){document.getElementById('summarize').style.color='red';}</script>
</fieldset>
</form>
<div style="display:inline-block;border:thin solid black;float:left;margin-left: 2em;padding:0 1em;width:15em">
	<p>Do you need to upload CSV files? If so, you will need use
		eBird to AviSys checklist import (Version 1).
		<a href="../ebirdtoavisysV1/">Click here</a></p>
</div>
<br style="clear: both">
<h2>What it is</h2>
<?php
	$heredoc = <<<HEREDOC
<p>eBird to AviSys checklist import will convert one or more eBird checklists into an AviSys stream file 
with which you can import the data into AviSys (Version 6).</p>
HEREDOC;
	echo $heredoc,PHP_EOL;
?>
<h2>What you do</h2>
<ol>
<li>In the &ldquo;Checklist input&rdquo; form above, list one or more checklist names and click &ldquo;Go!&rdquo;.
	eBird to AviSys checklist import (Version 2) will fetch the checklists directly from eBird;
	you no longer need to download them.
<p>	
Let's say you have a checklist <a href="https://ebird.org/view/checklist/S46116491" target="_blank">https://ebird.org/view/checklist/S46116491</a>.
	You can copy-and-paste just the "S46116491",
or you can copy-and-paste the whole "https://ebird.org/view/checklist/S46116491".
If you enter multiple checklists, enter them on separate lines, or else just separate them with spaces or commas.
For example you could enter<br><br>
	<kbd>
	S46116491<br>
	S21594122<br>
	S48759196<br>
	</kbd><br>
	or<br>
	<kbd>S46116491, S21594122, S48759196</kbd><br><br>
	or<br>
	<kbd>S46116491 S21594122 S48759196</kbd><br>
</p>
</li>
<li>On the next screen that you see, each eBird location that is in your input will be displayed.
If the corresponding AviSys place has a different name, you can enter the correct AviSys place name.
Then click the &ldquo;Download&rdquo; button that you will see.
<a href="howtodownload.html" target="_blank">More details on this screen</a> will be shown when you get there.
</li>
<li>Your AviSys stream file will be downloaded. Save it on your computer, then run Avisys to import it.</li>
<li>That's it!</li>
</ol>

<h3>What's a stream file?</h3>
<p>Read the tutorial on <a href="import.html" target="_blank">using AviSys stream files to import data</a> if you are not familiar with the process.</p>

<h2>About species names</h2>
<p>
If AviSys does not recognize a species name while importing a stream file, 
it will skip that sighting record. (It will tell you!)
You will need to be sure that you have AviSys up-to-date with current taxonomy.
</p>
<p>One prominent difference between eBird names and AviSys names is that eBird accepts certain names
with parenthetic qualifiers, e.g., &ldquo;Northern Flicker (Yellow-shafted)&rdquo;.
You don't need to worry about these cases!
eBird to AviSys checklist import will remove the parenthetic part of the name and insert it at the beginning of the AviSys comment.</p>
<p>Another difference is that eBird allows &ldquo;sp&rdquo; or &ldquo;slash&rdquo; entries (e.g. Downy/Hairy Woodpecker).
AviSys ignores these.
</p>

<h2>Warning about system-hidden checklists</h2>
<p>A checklist that is flagged for some reason with a comment similar to 
&ldquo;This checklist and its observations do not appear in public eBird outputs&rdquo;
cannot be imported with this version. You will need to use
<a href="../ebirdtoavisysV1/">Version 1</a></p>

<h2>Can I import from other sources?</h2>
<p>Yes, but not with this version of eBird to AviSys checklist import. You will need to use
<a href="../ebirdtoavisysV1/">Version 1</a> which supports csv files.</p>
<h3>What about eBird's &ldquo;Download My Data&rdquo;?</h3>
eBird provides a feature to <a href="http://ebird.org/ebird/downloadMyData" target="_blank">download all of your eBird data</a> in one file.
eBird to AviSys checklist import will process that file too!
But again, you will need to use <a href="../ebirdtoavisysV1/">Version 1</a>.

<h2 id="summarize">Summarize by location or checklist</h2>
<p>
Before you generate the AviSys stream file, 
eBird to AviSys checklist import gives you a summary of what is in the checklists that you specified.
In the summary, you can correct the AviSys place name, and you have the option to
enter a global comment that will be added to all observations for that location.
</p><p>
By default, the summary is by location. 
In other words, no matter how many checklists you select for a location, the
summary will only have a single entry for the location. This is
"Summarize by location".
</p><p>
Sometimes you might prefer to be able to enter a custom comment for each
checklist rather than for each location. In that case, select "Summarize
by checklist", and the summary will contain an entry for each checklist.
Each entry will display the time and effort data for the checklist, so
that you can recognize it.
</p><p>
Whichever type of summary you choose, the generated stream file will be
the same, except for anything that you enter in the Global comment field.
</p>


<h2>eBird locations vs. AviSys places</h2>
<p>In many cases, your eBird location names may not match up exactly with your AviSys place names,
and you will need to manually enter your corresponding AviSys place name for each eBird location.
As a convenience, eBird to AviSys checklist import remembers AviSys places from one session to the next.
Once you "train" it by entering the AviSys place name for an eBird location, the name pairing will be saved for future use.
The Places data is stored locally in your browser. This has a couple of consequences.</p>
<ul>
<li>If you use more than one browser, you have to "train" each one separately.</li>
<li>
If you decide to clear private data from your browser, the Places data
is subject to deletion. If it gets deleted, you will have to repeat the training.
<ul>
<li>In the Firefox "Clear Recent History" dialog, 
selecting "Offline Website Data" will clear your Places data and you will have to recreate it.</li>
<li>In the Chrome "Clear browsing data" dialog, 
selecting "Cookies and other site data" will clear your Places.</li>
<li>
In Microsoft browsers (Edge and IE) you can't clear the data
under "Clear browsing data", you have to get down into developer
tools, which you probably are not going to do.
</li>
</ul>
</li>
</ul>

<h2>Date</h2>
<p>AviSys can only record <a href="/2030.html">dates between Jan 1, 1930 and Dec 31, 2029</a>. 
Any dates older than 1930 will be recorded with a year of 1930.
Dates after 2030 will be recorded with a year 100 years earlier.
</p>

<?php
	if (file_exists('local_code.html'))
	{
		include 'local_code.html';
	}
?>
<?php
}
 ?>
