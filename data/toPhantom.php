<?php
putenv("PATH=.:/usr/local/bin");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

if(isset($_POST['script'])) {$script = $_POST['script'];} else {die;}
if(isset($_POST['HTML5URL'])) {$HTML5URL = $_POST['HTML5URL'];} else {$HTML5URL = false;}

$modded = false;

/*if (stripos($script, 'http://')) {
	$script = str_replace('http', 'https', $script);
	$modded = true;
}*/

if ($HTML5URL) {
	exec('phantomjs --ignore-ssl-errors=true --web-security=no /var/www/adtechs/html/tools/checkScript/data/htmlSniff.js "https://adtechnology.axelspringer.com/tools/checkScript/data/html5temp/'.$HTML5URL.'/index.html?clicktag=http%3A%2F%2Fwww.mitesturl.de;&target=_adtech" 2>&1', $result, $error);
} else {
	$file = fopen("checkScript.html", 'w+');
	fwrite($file, "<!DOCTYPE html><html><head><script type='text/javascript' href='https://www.asadcdn.com/adlib/pages/0_default.js'></script></head><body>".$script."</body></html>");
	fclose($file);
	exec('phantomjs --ignore-ssl-errors=true --web-security=no /var/www/adtechs/html/tools/checkScript/data/sizeSniff.js "https://adtechnology.axelspringer.com/tools/checkScript/data/checkScript.html" 2>&1', $result, $error);
}

if ($modded == false) {
	echo "";
}
echo "<div id='resultWrapper'><div class='left'>";
echo "<div><div class='weight'><span>Auswertung der Analyse:</span><br/><br/>";
echo "Der Redirect hat ein Gesamtgewicht inklusive aller nachgeladener Skripte von ".$result[0]."</div>";

/////////// KlickTag Ergebnis
if ($result[2] != "redirect") {
	echo "<div class='clicktag'><span>ClickTag Test:</span><br/><br/>";
	if ($result[2] == "false") {
		echo "<div class='error'>Es wurde kein Container mit der ID 'clicktag' gefunden!</div>";
	} else {
		$clicktag = json_decode($result[2]);
		$urlClass = ($clicktag['clicktagHref'] === false)?"error":"success";
		$targetClass = ($clicktag['clicktagTarget'] === false)?"error":"success";
		echo "<div class='success'>Es wurde ein Container mit der ID 'clicktag' gefunden.</div><div class=".$urlClass.">URL-Test wurde ";
		if ($clicktag['clicktagHref'] === false) {echo "nicht ";}
		echo "bestanden.</div><div class=".$targetClass.">TargetTest wurde ";
		if ($clicktag['clicktagTarget'] === false) {echo "nicht ";}
		echo "bestanden.</div>";
	}
	echo "</div>";
}


///////////// Secure-Check Ergebnis
echo "<div class='ssl'><span>SSL Check:</span><br/><br/>";
if ($result[3] == "false") {
	echo "<div class='error'>Es wurden HTTP-URLs gefunden, der Tag scheint nicht SSL-fähig.</div>";
} else if ($result[3] == "true") {
	echo "<div class='success'>Es wurden keine HTTP-URLs gefunden, der Tag ist SSL-fähig.</div>";
} else if ($result[3] == "error") {
       	echo "<div class='error'>Es wurden keine URLs gefunden, der Tag scheint fehlerhaft.</div>";
} else {
	echo "<div class='warning'>Es wurde weder HTTP noch HTTPS gefunden, der Tag scheint SSL fähig.</div>";
}
echo "</div>";

if ($result[4] != "[]") {
	echo "<div class='errors'><span>Ausgabe Javascript Errors</span><br/><br/>";
	print_r(json_decode($result[4]));
	echo "</div>";
}

if ($result[5] != "[]") {
	echo "<div class='uncoughtErrors'><span>Ausgabe Test-Log-Array</span><br/><br/><pre>";
	print_r(json_decode($result[5]));
	echo "</pre></div>";
}

echo "</div></div>";

echo "<div class='right'><div class='elements'><span>Folgende Elemente wurden geladen:</span><ul>";

$entries = json_decode($result[1]);

$x1 = 0;
foreach ($entries as $temp) {
	echo "<table><colgroup><col style='width: 950px;'></col><col style='width: 80px;'></col><col style='width: 80px;'></col></colgroup>";
	echo "<tr style='background: #000; color: #fff; text-align: center;'><td>URL</td><td>SIZE</td><td>STARTTIME</td><td>ENDTIME</td><td>DURATION</td><td>SUBLOAD</td></tr>";
	foreach ($temp as $x) {
	    $x1++;
        $dt = DateTime::createFromFormat("U.u", intval($x->start) / 1000);
        $dt->setTimeZone(new DateTimeZone("Europe/Berlin"));
        $dateString = strval($dt->format('H:i:s.u'));
        $starttime = substr($dateString, 0, -3);
        $dt = DateTime::createFromFormat("U.u", intval($x->end) / 1000);
        $dt->setTimeZone(new DateTimeZone("Europe/Berlin"));
        $dateString = strval($dt->format('H:i:s.u'));
        $endtime = substr($dateString, 0, -3);
		echo "<tr><td style='word-break: break-all;'>".
		    "<a href='".$x->url."'>".$x->url."</a></td>".
            "<td style='text-align:right;'>".round(($x->scriptSize)/1024, 2)." kB</td>".
            "<td style='text-align:right;'>".$starttime."</td>".
            "<td style='text-align:right;'>".$endtime."</td>".
            "<td style='text-align:right;'>".$x->duration."</td>".
            "<td class='". ($x->subload == "true" ? "success" : "error") ."' style='text-align:right;'>".$x->subload."</td>".
            "</tr>";
	}
	echo "</table>";
}
echo "</ul>";

if ($x1 < 2) {
    $result[3] = "error";
    echo "<div class='error'>Der eingegebene Code hat keine Requests vorgenommen und scheint daher fehlerhaft!</div>";
}

echo "</div></div>";
echo "</div>";

?>
