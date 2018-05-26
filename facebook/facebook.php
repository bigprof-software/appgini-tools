<?php

// Filename : facebook..php
// Purpose  : Provides an Appgini application with an oath2 client that can logon via Facebook
// Author   : Phil Massyn (@massyn)
// Date     : 2018-05-26
// Status   : Alpha - this script is stil being tested.  Production use is NOT encouraged.
//
// Release  : 2018-05-26 - Initial release
//
// MIT License
//
// Copyright (c) 2018 Phil Massyn
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
// =====================================================================================

define('PREPEND_PATH', '../');
$hooks_dir = dirname(__FILE__);
include("$hooks_dir/../defaultLang.php");
include("$hooks_dir/../language.php");
include("$hooks_dir/../lib.php");

// Define the app on Facebook - https://developers.facebook.com/apps/
// Get the clinet ID and the app secret, and set them here
$APPID = "<insert the app id in here>";
$APPSECRET = "<insert the appsecret here>";

// Create a group in AppGini, and define the group here.  Since you're basically allowing anyone from Facebook to logon, you need to specify what access any default person may have
$GROUP = 'Facebook';		// The group that will be assigned to all new members to the site

// The callback path is basically the path of this script. It is the same script used to intiate the logon, and that gets the feedback from Facebook

$CALLBACK = "https://phonebook.massyn.net/hooks/facebook.php";	// the callback is the path of this script (of course you need to change this).  I've left my example in here for visibility. Of course if you leave this in, your authentication call will not work, and Facebook will reject your OATH2 token request.

// ===========================================================

// we need to define a state, this is used as a csrf check during authentication
if(!isset($_SESSION['state'])) {
	        $_SESSION['state']  = sha1(time().mt_rand());
}

if(!isset($_GET['code'])) {
	initiate_logon($APPID,$CALLBACK);
} else {
	if($_SESSION['state'] != $_GET['state']) {
		error("State doesn't match... sorry!  Access denied");
	} else {
		$json = json_curl('https://graph.facebook.com/v3.0/oauth/access_token',
		[
			'client_id'     => $APPID,
			'redirect_uri'  => $CALLBACK,
			'client_secret' => $APPSECRET,
			'code'          => $_GET['code']
		]);

		if(isset($json['error'])) {
			error($json['error']['message']);
		} else {
			$access_token = $json['access_token'];

			// for some reason, facebook is not returning the email address, so we'll settle for the ID

			$json = json_curl('https://graph.facebook.com/v3.0/me',
			[
				'fields'                => 'id,first_name,last_name',
				'access_token'          => $access_token,
				'appsecret_proof'       => hash_hmac('sha256', $access_token, $APPSECRET)
			]);

			$first_name = $json['first_name'];
			$last_name = $json['last_name'];
			$id = $json['id'];

			if($id != '') {
				// If you made it this far, you managed to log on

				// create the user account if it doesn't already exist	
				// we will use a dummy email address for now until we can figure out how to pull the email from facecbook (if it even allows it)
				$memberID = create_user_account("$id@facebook.com",$id,$first_name,$last_name,$GROUP);

				$memberGroupID = sqlValue("select groupID from membership_users where lcase(memberID)='$memberID'");

				if(!$_SESSION['memberGroupID']) {
					error("The group ($GROUP) may not exist in AppGini.  Create the group and assign it permissions");
				}

				// if you made it this far, everything checks out, and the user can now log on
				$_SESSION['memberID']		= $memberID;
				$_SESSION['memberGroupID']	= $memberGroupID;

				redirect("../");
				exit;

			} else {
				error("We were not able to obtain the id.. Login cannot continue");
			}
			 
		}

	}

}

exit(0);

// =========================================================================== 

function initiate_logon ($APPID,$CALLBACK) {
	$URL = "https://www.facebook.com/v3.0/dialog/oauth?client_id=$APPID&redirect_uri=" . urlencode($CALLBACK) . "&state=" . $_SESSION['state'];

	header('Location: ' . $URL);

}

function create_user_account($email,$id,$fn,$ln,$g) {

	$iddb = sqlValue(sprintf("select memberID from membership_users where memberID = '%s'",makeSafe($id)));

	if(!isset($iddb) || $iddb == '') {
		echo "creating a new user<br>\n";
		if(!db_query(sprintf("INSERT INTO membership_users (memberID,email,signupDate,groupID,isBanned,isApproved,comments,custom1) VALUES ('%s','%s',CURRENT_DATE,(select groupID from membership_groups where name = '%s'),0,1,'Created by Facebook plugin','%s %s')",makeSafe($id),makeSafe($email),makeSafe($g),makeSafe($fn),makeSafe($ln)))) {
			error("Something went wrong creating your account in the database");
		}

		return $id;
	} else {
		return $iddb;
	}
}

function json_curl($url,$params) {

	$defaults = array(
		CURLOPT_URL => $url,
		CURLOPT_POSTFIELDS => $params,
		CURLOPT_SSL_VERIFYPEER => false ,
		CURLOPT_RETURNTRANSFER => true
	);
	$ch = curl_init();
	curl_setopt_array($ch, $defaults);

	return json_decode(curl_exec($ch),true);
}

function error($message) {
	include_once("$hooks_dir/../header.php");

	echo "<div class=\"alert alert-danger\">$message</div>\n";

	echo "<p><a href=facebook.php><img src=\"facebook-login-blue.png\" /></a></p>";
	include_once("$hooks_dir/../footer.php");
}
?>


