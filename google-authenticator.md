# Google Authenticator in AppGini
There may be various reasons why you'd like to enable multi factor authentcation. It protects your application with a little bit more protection when users may have poor password management skills.

## Installation
* Download the [GoogleAuthenticatorClass.php](https://github.com/massyn/php-framework/blob/master/GoogleAuthenticatorClass.php) file and save it to your _hooks_ folder.
* Download the [setup_googleauth.php](setup_googleauth.php) and save it to your _hooks_ folder.
* Edit the _links-navmenu.php_ file in the _hooks_ folder and add the following text to the file
```php
	$navLinks[] = array(
		'url' => 'hooks/setup_googleauth.php', 
		'title' => 'Google Authenticator', 
		'groups' => array('*'),
		'table_group' => 0
	);
  ```
  * Edit the _links-home.php_ file in the _hooks_ folder and add the following text to the file
```php
	$homeLinks[] = array(
		'url' => 'hooks/setup_googleauth.php', 
		'title' => 'Google Authenticator', 
		'description' => 'Setup multi-factor authentication through the Google Authenticator mobile app.',
		'groups' => array('*'),
		'grid_column_classes' => '',
		'panel_classes' => '',
		'link_classes' => '',
		'table_group' => ''
	);
```
* Edit the _login.php_ folder in the root.  Around line 33, you'll notice the end of the "Password" field, and the start of the "Remember Me" code.  Between the two fields, insert the follwing HTML code.
```HTML
<div class="form-group">
	<label class="control-label" for="otp">Google Authenticator</label>
	<input class="form-control" name="otp" id="otp" type="text" placeholder="Google Authenticator">
</div>	
```
* Edit the _incCommon.php_ script, and look for the *logInMember* function.  
* Add the following lines of code just after the *function logInMember(){* statement
```php
$curr_dir = dirname(__FILE__);
require_once "$curr_dir/hooks/GoogleAuthenticatorClass.php";
$ga = new framework_GoogleAuthenticator();
```
* Continue editing _incCommon.php_ by scrolling down, and look for this line of code.
```php
if(sqlValue("select count(1) from membership_users where lcase(memberID)='$username' and passMD5='$password' and isApproved=1 and isBanned=0")==1){
```
Replace it with the following code
```php
if(sqlValue("select count(1) from membership_users where lcase(memberID)='$username' and passMD5='$password' and isApproved=1 and isBanned=0")==1 && ($ga->TOTPauthenticate(db_link(),$username))){
```
## Design notes
* Multi factor authentication is not forced, meaning that if the user did not configure Google Authenticator, they will still be allowed to logon.


