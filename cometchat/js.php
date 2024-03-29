<?php

/*

CometChat
Copyright (c) 2012 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

include_once (dirname(__FILE__).'/config.php');
include_once (dirname(__FILE__).'/jsmin.php');

if (BAR_DISABLED == 1) { exit; }

if(get_magic_quotes_runtime()) { 
	set_magic_quotes_runtime(false); 
}

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime; 

$HTTP_USER_AGENT = '';
$useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;

ob_start();

if (!empty($_REQUEST['type']) && !empty($_REQUEST['name'])) {
	$type = cleanInput($_REQUEST['type']);
	$name = cleanInput($_REQUEST['name']);
} else {
	$type = 'core';
	$name = 'default';
}

$cbfn = '';

if (!empty($_REQUEST['callbackfn'])) {
	$cbfn = $_REQUEST['callbackfn'];
} 

if (file_exists(dirname(__FILE__).'/cache/'.$cbfn.$lang.$type.$name.'.js') && DEV_MODE != 1) {

	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime(dirname(__FILE__).'/cache/'.$cbfn.$lang.$type.$name.'.js')) {
		header("HTTP/1.1 304 Not Modified");
		exit;
	}

	readfile(dirname(__FILE__).'/cache/'.$cbfn.$lang.$type.$name.'.js');
	$js = ob_get_clean();

} else {

	if ($type != 'core' || $name != 'default') {
		
		if ($type == 'core') {
			include_once (dirname(__FILE__)."/js/".$name.".js");
		} else {
			include_once (dirname(__FILE__)."/".$type."s/".$name."/".$name.".js");
		}

	} else {

		if (USE_COMET == 1) {
			$minHeartbeat = REFRESH_BUDDYLIST.'000';
			$maxHeartbeat = REFRESH_BUDDYLIST.'000';
		}
		
		if (defined('INCLUDE_JQUERY') && INCLUDE_JQUERY == 1) {
			include_once (dirname(__FILE__)."/js/jquery.js");
		}

		$settings = '';

		if ((defined('DISPLAY_ALL_USERS') && DISPLAY_ALL_USERS == 1) || (defined('FORCE_ALL_USERS') && FORCE_ALL_USERS == 1)) {
			$language[14] = $language[28];
		} else if ($hideOffline == 1) {
			$language[14] = $language[29];
		}

		for ($i=0;$i<count($language);$i++) {
			$settings .= "_2[".$i."] = '".str_replace("'", "\'", $language[$i])."';\n";
		}

		for ($i=0;$i<count($trayicon);$i++) {
			$id = $trayicon[$i];
			if (!empty($trayicon[$i][7]) && $trayicon[$i][7] == 1) {
				$trayicon[$i][2] = BASE_URL.$trayicon[$i][2];
			}

			if (file_exists(dirname(__FILE__)."/modules/".$trayicon[$i][0]."/lang/".$lang.".php")) {
				include_once (dirname(__FILE__)."/modules/".$trayicon[$i][0]."/lang/".$lang.".php");
				$traylanguage = $trayicon[$i][0].'_language';

				if (!empty(${$traylanguage}[100])) {
					$trayicon[$i][1] = ${$traylanguage}[100];
				}
			}

			$settings .= "_3['".$id[0]."'] = ['".implode("','",$trayicon[$i])."'];\n";
		}

		if (!empty($_GET['callbackfn'])) {
			$hideBar = 0;
		}

		$settings .= "var _4 = ['".implode("','",$plugins)."'];\n";
		$settings .= "var _5 = ".$autoPopupChatbox.";";
		$settings .= "var _6 = ".$messageBeep.";";
		$settings .= "var _7 = '".$theme."';";
		$settings .= "var _8 = ".$minHeartbeat.";";
		$settings .= "var _9 = ".$maxHeartbeat.";";
		$settings .= "var _a = '".$cookiePrefix."';";
		$settings .= "var _b = '".$barType."';";
		$settings .= "var _c = ".$barWidth.";";
		$settings .= "var _d = '".$barAlign."';";
		$settings .= "var _e = ".$barPadding.";";
		$settings .= "var _f = ".$beepOnAllMessages.";";
		$settings .= "var _10 = ".$fullName.";";
		$settings .= "var _11 = ".$autoLoadModules.";";
		$settings .= "var _12 = ".$longNameLength.";";
		$settings .= "var _13 = ".$shortNameLength.";";
		$settings .= "var _14 = ".$searchDisplayNumber.";";
		$settings .= "var _15 = ".$thumbnailDisplayNumber.";";
		$settings .= "var _16 = ".$typingTimeout.";";
		$settings .= "var _17 = ".$idleTimeout.";";
		$settings .= "var _18 = ".$displayOfflineNotification.";";
		$settings .= "var _19 = ".$displayOnlineNotification.";";
		$settings .= "var _1a = ".$displayBusyNotification.";";
		$settings .= "var _1b = ".$notificationTime.";";
		$settings .= "var _1c = ".$announcementTime.";";
		$settings .= "var _1d = ".$armyTime.";";
		$settings .= "var _1e = ".$scrollTime.";";
		$settings .= "var _1f = ".$disableForIE6.";";
		$settings .= "var _20 = ".$disableForMobileDevices.";";
		$settings .= "var _21 = ".$iPhoneView.";";
		$settings .= "var _22 = ".$hideBar.";";
		$settings .= "var _23 = ".$fixFlash.";";
		$settings .= "var _24 = ['".implode("','",$extensions)."'];\n";

		include_once (dirname(__FILE__)."/js/libraries.js");

		if ($sleekScroller == 1) {
			include_once (dirname(__FILE__)."/js/scroll.js");
		}

		if (USE_COMET == 1) {
			include_once (dirname(__FILE__)."/transports/".TRANSPORT.'/config.php');
			include_once (dirname(__FILE__)."/transports/".TRANSPORT.'/includes.php');
		}

		// Modifying this will void license
		if ($p_<2) { $jsfn = 'c5'; } else { $jsfn = 'c6'; }
		
		if ($lightWeight == 1 && empty($_GET['callbackfn'])) {
			include_once (dirname(__FILE__)."/js/cometchat_lite.js");     
		} else {
			include_once (dirname(__FILE__)."/js/cometchat.js");                                                                                                                     
		}

		$include = 'init';

		if (!empty($_GET['callbackfn']) && $_GET['callbackfn'] != 'desktop') {
			$include = $_GET['callbackfn'];
		}

		foreach ($plugins as $plugin) {
			if (file_exists(dirname(__FILE__)."/plugins/".$plugin."/".$include.".js")) {
				include_once (dirname(__FILE__)."/plugins/".$plugin."/".$include.".js");
			}
		}

		foreach ($extensions as $extension) {
			if (file_exists(dirname(__FILE__)."/extensions/".$extension."/".$include.".js")) {
				include_once (dirname(__FILE__)."/extensions/".$extension."/".$include.".js");
			}
		}

		for ($i=0;$i<count($trayicon);$i++) {
			$id = $trayicon[$i];
			if (file_exists(dirname(__FILE__)."/modules/".$id[0]."/extra.js") && empty($_GET['callbackfn'])) {
				include_once (dirname(__FILE__)."/modules/".$id[0]."/extra.js");
			}
		}

		if (file_exists(dirname(__FILE__)."/js/extra.js")  && empty($_GET['callbackfn'])) {
			include_once (dirname(__FILE__)."/js/extra.js");
		}


	}

	$js = JSMin::minify(ob_get_clean());

	$fp = @fopen(dirname(__FILE__).'/cache/'.$cbfn.$lang.$type.$name.'.js', 'w'); 
	@fwrite($fp, $js);
	@fclose($fp);

}

if (phpversion() >= '4.0.4pl1' && (strstr($useragent,'compatible') || strstr($useragent,'Gecko'))) {
	if (extension_loaded('zlib') && GZIP_ENABLED == 1) {
		ob_start('ob_gzhandler');
	} else { ob_start(); }
} else { ob_start(); }

$lastModified = filemtime(dirname(__FILE__).'/cache/'.$cbfn.$lang.$type.$name.'.js');

header('Content-type: text/javascript;charset=utf-8');
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastModified)." GMT");
header('Expires: '.gmdate("D, d M Y H:i:s", time() + 3600*24*365).' GMT');

echo $js;

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo "\n\n/* Execution time: ".$totaltime." seconds */";

function cleanInput($input) {
	$input = trim($input);
	$input = preg_replace("/[^+A-Za-z0-9\_]/", "", $input); 
	return strtolower($input);
}