<?php
date_default_timezone_set('UTC');

define('SITE_NAME','What.CD Vanilla'); //The name of your site
define('NONSSL_SITE_URL','192.168.1.3'); //The FQDN of your site
define('SSL_SITE_URL','192.168.1.3'); //The FQDN of your site, make this different if you are using a subdomain for ssl

define('NONSSL_STATIC_SERVER','static/');
define('SSL_STATIC_SERVER','static/'); // Allows you to run static content off another server

define('ANNOUNCE_URL','http://192.168.1.3:34000'); //Announce URL

define('BOT_NICK', 'Drone');
define('BOT_SERVER', '127.0.0.1');
define('BOT_PORT', '6667');
define('BOT_CHAN','#'.NONSSL_SITE_URL);
define('BOT_ANNOUNCE_CHAN', '#');
define('BOT_STAFF_CHAN','#');
define('BOT_DISABLED_CHAN', '#');
define('BOT_HELP_CHAN', '#');
define('BOT_DEBUG_CHAN', '#');
define('BOT_REPORT_CHAN', '#');
define('BOT_NICKSERV_PASS', '');
define('SOCKET_LISTEN_PORT', '51010');
define('SOCKET_LISTEN_ADDRESS', 'localhost');

define('ADMIN_CHAN', '#');
define('LAB_CHAN', '#');
define('STATUS_CHAN', '#');

define('SITE_SALT',''); //Random key. Default site wide salt for passwords, DO NOT LEAVE THIS BLANK/CHANGE AFTER LAUNCH!
define('SCHEDULE_KEY', ''); // Random key. This key must be the argument to schedule.php for the schedule to work. 
define('SESSION_DIR','/tmp/'); //PHP Session directory to count active users
define('RSS_HASH',''); //Random key. Used for generating unique RSS auth key.
define('DEBUG_MODE',false); //Set to false if you dont want everyone to see debug information, can be overriden with 'site_debug' 
define('OPEN_REGISTRATION',true); //Set to false to disable open regirstration, true to allow anyone to register
define('USER_LIMIT',5000); //The maximum number of users the site can have, 0 for no limit
define('STARTING_INVITES','0'); //# of invites to give to newly registered users
define('DONOR_INVITES',2);

if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 80) {
	define('SITE_URL',NONSSL_SITE_URL);
	define('STATIC_SERVER',NONSSL_STATIC_SERVER);
} else {
	define('SITE_URL',SSL_SITE_URL);
	define('STATIC_SERVER',SSL_STATIC_SERVER);
}

define('SERVER_ROOT','/home/leafy/Code/gazelle-vanilla'); //The root of the server, used for includes, purpose is to shorten the path string
define('DB_ERROR_PAGE',SERVER_ROOT.'/sections/error/index.php'); //Location of the error page in case of a mysql error

define('ENCKEY',''); //Random key. The key for encryption

define('SQLHOST','localhost'); //The MySQL host ip/fqdn
define('SQLLOGIN','gazelle');//The MySQL login
define('SQLPASS','TfRZXC9ZyWL7MAy'); //The MySQL password
define('SQLDB','gazelle-vanilla'); //The MySQL database to use
define('SQLPORT','3306'); //The MySQL port to connect on
define('SQLSOCK','/var/run/mysqld/mysqld.sock');

// Memcached details
define('MEMCACHED_HOST','unix:///var/run/memcached.sock'); // unix sockets are fast, and other people can't telnet into them
define('MEMCACHED_PORT',0);

// Sphinx details
define('SPHINX_HOST', '127.0.0.1');
define('SPHINX_PORT', 3312);
define('SPHINX_MAX_MATCHES', 1000); // Must be <= the server's max_matches variable (default 1000)
define('SPHINX_INDEX', 'gazelle');

define('USER',		'2');
define('MEMBER',	'3');
define('POWER',		'4');
define('ARTIST',	'19');
define('DONOR',		'20');
define('ELITE',		'5');
define('VIP',		'6');
define('TORRENT_MASTER','7');
define('LEGEND',	'8');
define('CELEB',		'9');
define('MOD',		'11');
define('DESIGNER',	'13');
define('CODER',		'14');
define('ADMIN',		'1');
define('SYSOP',		'15');


//Pagination
define('TORRENT_COMMENTS_PER_PAGE', '10');
define('POSTS_PER_PAGE', '25');
define('TOPICS_PER_PAGE', '50');
define('TORRENTS_PER_PAGE', '50');
define('REQUESTS_PER_PAGE', '25');
define("MESSAGES_PER_PAGE", 25);

//Cache catalogues
define('THREAD_CATALOGUE',500);

// Miscellaneous values

$Categories = array('Music', 'Applications', 'E-Books', 'Audiobooks', 'E-Learning Videos', 'Comedy', 'Comics');
$CategoryIcons = array('music.png', 'apps.png', 'ebook.png', 'audiobook.png', 'elearning.png', 'comedy.png', 'comics.png');

$Formats = array('MP3', 'FLAC', 'Ogg', 'AAC', 'AC3', 'DTS');
$Bitrates = array('192', 'APS (VBR)', 'V2 (VBR)', 'V1 (VBR)', '256', 'APX (VBR)', 'V0 (VBR)', 'q8.x (VBR)', '320', 'Lossless', '24bit Lossless', 'Other');
$Media = array('CD', 'DVD', 'Vinyl', 'Soundboard', 'SACD', 'DAT', 'Cassette', 'WEB');

$ReleaseTypes = array(1=>'Album', 3=>'Soundtrack', 5=>'EP', 7=>'Compilation', 9=>'Single', 11=>'Live album', 13=>'Remix', 14=>'Bootleg', 15=>'Interview', 16=>'Mixtape', 21=>'Unknown');
$ForumCats = array(1=>'Site',5=>'Community',10=>'Help',8=>'Music',20=>'Trash');

$ZIPGroups = array(
	0 => 'MP3 (VBR) - High Quality',
	1 => 'MP3 (VBR) - Low Quality',
	2 => 'MP3 (CBR)',
	3 => 'FLAC - Lossless',
	4 => 'Others'
);

//3D array of attributes, OptionGroup, OptionNumber, Name
$ZIPOptions = array(
	'00' => array(0,0,'V0'),
	'01' => array(0,1,'APX'),
	'02' => array(0,2,'256'),
	'03' => array(0,3,'V1'),
	'10' => array(1,0,'224'),
	'11' => array(1,1,'V2'),
	'12' => array(1,2,'APS'),
	'13' => array(1,3,'192'),
	'20' => array(2,0,'320'),
	'21' => array(2,1,'256'),
	'22' => array(2,2,'224'),
	'23' => array(2,3,'192'),
	'30' => array(3,0,'FLAC / 24bit / Vinyl'),
	'31' => array(3,1,'FLAC / 24bit / DVD'),
	'32' => array(3,2,'FLAC / 24bit / SACD'),
	'33' => array(3,3,'FLAC / Log (100) / Cue'),
	'34' => array(3,4,'FLAC / Log (100)'),
	'35' => array(3,5,'FLAC / Log'),
	'36' => array(3,6,'FLAC'),
	'40' => array(4,0,'DTS'),
	'41' => array(4,1,'Ogg'),
	'42' => array(4,2,'AAC - 320'),
	'43' => array(4,3,'AAC - 256'),
	'44' => array(4,4,'AAC - q5.5'),
	'45' => array(4,5,'AAC - q5'),
	'46' => array(4,6,'AAC - 192')
);

// Ratio requirements, in descending order
$RatioRequirements = array(
	array(50*1024*1024*1024, 0.60, date('Y-m-d H:i:s', time())),
	array(40*1024*1024*1024, 0.50, date('Y-m-d H:i:s', time())),
	array(30*1024*1024*1024, 0.40, date('Y-m-d H:i:s', time())),
	array(20*1024*1024*1024, 0.30, date('Y-m-d H:i:s', time())),
	array(10*1024*1024*1024, 0.20, date('Y-m-d H:i:s', time())),
	array(5*1024*1024*1024,  0.15, date('Y-m-d H:i:s', time()-(60*60*24*14)))
);

//Captcha fonts should be located in /classes/fonts
$CaptchaFonts=array('ARIBLK.TTF','IMPACT.TTF','TREBUC.TTF','TREBUCBD.TTF','TREBUCBI.TTF','TREBUCIT.TTF','VERDANA.TTF','VERDANAB.TTF','VERDANAI.TTF','VERDANAZ.TTF');
//Captcha images should be located in /captcha
$CaptchaBGs=array('captcha1.png','captcha2.png','captcha3.png','captcha4.png','captcha5.png','captcha6.png','captcha7.png','captcha8.png','captcha9.png');

// Special characters, and what they should be converted to
// Used for torrent searching
$SpecialChars = array(
        '&' => 'and',

);
?>
