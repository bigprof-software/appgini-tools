<?php
// Use Google Authenticator with AppGini script by Phil Massyn
// For reference, see https://bigprof.com/appgini/help/advanced-topics/custom-limited-access-pages
#
# How to use
# 1) Copy this script to your hooks folder
# 2) Update the group in line 20 (or leave it as is, your call :-)
# 3) Update the SQL query in line 25
# 4) Update the menu path (see https://bigprof.com/appgini/help/advanced-topics/hooks/folder-contents )

define('PREPEND_PATH', '../');
$hooks_dir = dirname(__FILE__);
include("$hooks_dir/../defaultLang.php");
include("$hooks_dir/../language.php");
include("$hooks_dir/../lib.php");
include_once("$hooks_dir/../header.php");

require_once "$hooks_dir/GoogleAuthenticatorClass.php";
$ga = new framework_GoogleAuthenticator();

$conn = db_link();
$memberinfo = getMemberInfo();

echo '<h2>Setup Google Authenticator</h2>';

if(!isset($_POST['secret']) && !isset($_POST['otp'])) {
        // this is the first page, to present the user with a QR code, and a form to validate the token
        echo $ga->TOTPsetupPage($memberinfo['username'],'AppGini');	// need to find a way to retreive the site name and put it in here.
} elseif(isset($_POST['secret']) && isset($_POST['otp'])) {
	// now we check if the provided token is actually working
	$message = $ga->TOTPsetup($conn,$memberinfo['username']);

	if (preg_match("/ERROR/i", $message)) {
		echo '<div class="alert alert-warning"><i class="glyphicon glyphicon-warning-sign"></i> ';
		echo $message;
		echo '</div>';
		echo '<a href="setup_googleauth.php">Try again</a>';
	} else {
		echo '<div class="alert alert-success"><i class="glyphicon glyphicon-success-sign"></i> ';
		echo $message;
		echo '</div>';
	}
}

include_once("$hooks_dir/../footer.php");
?>

