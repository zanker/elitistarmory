<?
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
$MIRROR_VERSION = 2;
$SECRET_KEY = "";

// Handle requesting the actual data
class ea_mirror {
	function request($url, $base_url) {
		global $MIRROR_VERSION;
		$headers = array(
			"Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
			"Cache-Control: max-age=0",
			"Connection: close",
			"Accept-Charset: utf-8",
			"Accept-Language: en-us,en;q=0.5",
			"Pragma: ");

		$opts = array(
			CURLOPT_URL => $url,
			CURLOPT_REFERER => $base_rul,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6",
			CURLOPT_COOKIE => "loginChecked=1",
			CURLOPT_HEADER => false,
			CURLOPT_ENCODING => "gzip,deflate",
			//CURLOPT_FOLLOWLOCATION => true,
			//CURLOPT_MAXREDIRS => 5,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_CONNECTTIMEOUT => 5);
		
		$curl = curl_init();
		// Older versions of PHP don't support curl_setopt_array
		foreach( $opts as $key => $value) { 
			curl_setopt($curl, $key, $value);
		}
		$output = curl_exec($curl);
		$response = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		echo "RESPONSE:$response:DATA:$output";
	}
}

$pages = array(
	"character" => "character-sheet.xml",
	"talents" => "character-talents.xml",
	"achievements" => "character-achievements.xml",
	"statistics" => "character-statistics.xml",
	"item_tooltip" => "item-tooltip.xml",
	"item_info" => "item-info.xml",
	"guild" => "guild-info.xml",
	"battlegroups" => "battlegroups.xml",
	"reputation" => "character-reputation.xml",
	"arena_team" => "team-info.xml",
	"feed" => "character-feed.xml",
	"arena_ladder" => "arena-ladder.xml",
);

// do our basic verifications
if( !empty($SECRET_KEY) && $_GET["key"] != $SECRET_KEY ) {
	echo "ERROR:SECRET_MISMATCH";
	return;
} elseif( !is_numeric($_GET["version"]) ) {
	echo "ERROR:BAD_VERSION";
	return;
} elseif( $_GET["version"] < $MIRROR_VERSION ) {
	echo "ERROR:VERSION_MISMATCH";
	return;
} elseif( empty($_GET["ping"]) && $_GET["region"] != "us" && $_GET["region"] != "eu" && $_GET["region"] != "tw" && $_GET["region"] != "cn" && $_GET["region"] != "kr" ) {
	echo "ERROR:BAD_REGION";
	return;
} elseif( empty($_GET["ping"]) &&  empty($pages[$_GET["page"]]) ) {
	echo "ERROR:BAD_PAGE";
	return;
}

// Make sure the server is up
if( $_GET["ping"] ) {
	echo "PONG";
	return;
}

$base_url = "http://{$_GET["region"]}.wowarmory.com";
$url = "$base_url/{$pages[$_GET['page']]}";

$list = array('r', 'n', 'cn', 'i', 'gn', 'ts', 't', 'p', 'b', 'c');
$args = array();
foreach( $list as $key ) {
	if( !empty($_GET[$key]) ) {
		$santized = urlencode(stripslashes($_GET[$key]));
		$args[] = "{$key}={$santized}";
	}
}

if( count($args) > 0 ) {
	$url = $url . "?" . implode("&", $args);
}

if( !empty($_GET["debug"]) ) {
	echo "DEBUG<br />";
	echo "URL: {$url}<br />";
	echo "Base URL: {$base_url}<br />";
	echo "Region: {$_GET['region']}<br />";
	echo "Page: {$_GET['page']} / {$pages[$_GET['page']]}<br />";
	echo "Passed verion: {$_GET['version']}<br />";
	echo "Active version: {$MIRROR_VERSION}<br />";
	echo "Args (" . count($args) . "): " . implode("&", $args) . "<br />";
	return;
}

// Alright send it off
$mirror = new ea_mirror();
$mirror->request($url, $base_url);
?>