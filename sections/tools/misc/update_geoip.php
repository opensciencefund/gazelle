<?
if (!check_perms('admin_update_geoip')) { die(); }
enforce_login();

ini_set('memory_limit',1024*1024*1024);
ini_set('max_execution_time', 3600);

header('Content-type: text/plain');
ob_end_clean();
restore_error_handler();

//TODO: maxmind geolite city
//requires wget, unzip commands to be installed
/*
shell_exec('wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/GeoLiteCity_'.date('Ym').'01.zip');
shell_exec('unzip GeoLiteCity_'.date('Ym').'01.zip');
shell_exec('rm GeoLiteCity_'.date('Ym').'01.zip');
*/
$Registries[] = 'http://ftp.apnic.net/stats/afrinic/delegated-afrinic-latest'; //Africa
$Registries[] = 'http://ftp.apnic.net/stats/apnic/delegated-apnic-latest'; //Asia & Pacific
$Registries[] = 'http://ftp.apnic.net/stats/arin/delegated-arin-latest'; //North America
$Registries[] = 'http://ftp.apnic.net/stats/lacnic/delegated-lacnic-latest'; //South America
$Registries[] = 'http://ftp.apnic.net/stats/ripe-ncc/delegated-ripencc-latest'; //Europe
/*
$Registries[] = 'ftp://ftp.afrinic.net/pub/stats/afrinic/delegated-afrinic-latest'; //Africa
$Registries[] = 'ftp://ftp.apnic.net/pub/stats/apnic/delegated-apnic-latest'; //Asia & Pacific
$Registries[] = 'ftp://ftp.arin.net/pub/stats/arin/delegated-arin-latest'; //North America
$Registries[] = 'ftp://ftp.lacnic.net/pub/stats/lacnic/delegated-lacnic-latest'; //South America
$Registries[] = 'ftp://ftp.ripe.net/ripe/stats/delegated-ripencc-latest'; //Europe
*/
$Query = array();

foreach ($Registries as $Registry) {
	$CountryData = explode("\n",file_get_contents($Registry));
	foreach ($CountryData as $Country) {
		if (preg_match('/\|([A-Z]{2})\|ipv4\|(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\|(\d+)\|/', $Country, $Matches)) {

			$Start = ip2unsigned($Matches[2]);
			if($Start == 2147483647) { continue; }
			
			if (!isset($Current)) {
				$Current = array('StartIP' => $Start, 'EndIP' => $Start + $Matches[3],'Code' => $Matches[1]);
			} elseif ($Current['Code'] == $Matches[1] && $Current['EndIP'] == $Start) {
				$Current['EndIP'] = $Current['EndIP'] + $Matches[3];
			} else {
				$Query[] = "('".$Current['StartIP']."','".$Current['EndIP']."','".$Current['Code']."')";
				$Current = array('StartIP' => $Start, 'EndIP' => $Start + $Matches[3],'Code' => $Matches[1]);
			}
		}
	}
}
$Query[] = "('".$Current['StartIP']."','".$Current['EndIP']."','".$Current['Code']."')";

$DB->query("TRUNCATE TABLE geoip_country");
$DB->query("INSERT INTO geoip_country (StartIP, EndIP, Code) VALUES ".implode(',', $Query));
echo $DB->affected_rows();
