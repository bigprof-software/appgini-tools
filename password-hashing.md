# Securing passwords in AppGini 5.70
AppGini upto version 5.70 is utilizing MD5 to hash user passwords in the _membership_users_ table.  There are [many](https://en.wikipedia.org/wiki/MD5#Overview_of_security_issues) reasons why MD5 should not be used, an issue I have reported to the developers of AppGini back in January 2018 (we're in May, and still no solution in sight).  This is a serious issue, and once that demands a solution.  So here is my take on it.  While it is manual, the following patch applied to your generated AppGini code will make your passwords a lot more secure.

This patch will also improve the Remember Me function, removing the MD5 functions used to validate the cookies.

### Schema update
Run the following SQL query against the database to upgrade the schema.
```sql
ALTER TABLE membership_users ADD passPHP varchar(60);
CREATE TABLE membership_sessions (
	session 	varchar(60) PRIMARY KEY,
	datetime	datetime,
	memberID	varchar(20),
	memberGroupID	int(10)
);
```
### incCommon.php
If you have an existing system, then getting your users reset their passwords will be a very time consuming activity.  This fix will allow the existing MD5 passwords to remain, and will replace them with more secure passwords as soon as the user logs on the next time.

The function will add a new field (_passPHP_), authenticate against MD5, then replace the password with the new hash, and delete the old MD5 hash.

Replace the following code in *incCommon.php*...
```php
	function logInMember(){
		$redir = 'index.php';
		if($_POST['signIn'] != ''){
			if($_POST['username'] != '' && $_POST['password'] != ''){
				$username = makeSafe(strtolower($_POST['username']));
				$password = md5($_POST['password']);

				if(sqlValue("select count(1) from membership_users where lcase(memberID)='$username' and passMD5='$password' and isApproved=1 and isBanned=0")==1){
```
with this
```php
	function logInMember(){
		$redir = 'index.php';
		if($_POST['signIn'] != ''){
			if($_POST['username'] != '' && $_POST['password'] != ''){
				$username = makeSafe(strtolower($_POST['username']));
				$password = md5($_POST['password']);

				// read the password from the database
				$passPHP = sqlValue("select passPHP from membership_users where lcase(memberID)='$username' and isApproved=1 and isBanned=0");

				// if the password is blank, it could indicate that the password was not converted to PHP yet
				if($passPHP == '') {
					// retrieve the old MD5 password
					$passMD5 = sqlValue("select passMD5 from membership_users where lcase(memberID)='$username' and isApproved=1 and isBanned=0");

					// == authenticate against MD5
					if($passMD5 == $password) {
						// we need to convert the password to a more secure hashing algorithm
						$options = [
			    				'cost' => 12,
		    				];
						$passPHP = password_hash($_POST['password'], PASSWORD_BCRYPT, $options);
						
						db_query("update membership_users set passMD5='', passPHP='$passPHP' where lcase(memberID)='$username' and isApproved=1 and isBanned=0");
					}
				}

				if(password_verify($_POST['password'],$passPHP)) {
```
A few lines down, replace this code (where Application is your own application's title)
```php
@setcookie('Application_rememberMe', md5($username.$password), time()+86400*30);
```
with this code
```php
$SessionID = sha1(bin2hex(random_bytes(60)));
db_query("INSERT INTO membership_sessions (session,memberID,memberGroupID,datetime) values('$SessionID','" . makeSafe($username) . "'," . makeSafe($_SESSION['memberGroupID']) . ",CURRENT_TIMESTAMP)");
@setcookie('Application_rememberMe', $SessionID, time()+86400*30,'','',isset($_SERVER["HTTPS"]), true);
```
*REMEMBER* - you need to replace the title _Application_ with your own application's title.

At the bottom of the same function, look for the following piece of code
```php
if($username=sqlValue("select memberID from membership_users where convert(md5(concat(memberID, passMD5)), char)='$chk' and isBanned=0")){
```
and replace it with the following code
```php
// perform a maintenance clean up of any old entries in the session table (this also invalidates old sessions).  The current default is 14 days.  If you want sessions to remain more than 2 weeks, increase the number in the query
db_query("DELETE from `membership_sessions` where TIME_TO_SEC(timediff(CURRENT_TIMESTAMP,datetime)) > 1209600");
if($username=sqlValue("select memberID from membership_sessions where session='$chk' and isBanned=0")){
	// update the datetime, so that the session remain valid
	db_query("update membership_sessions set datetime = CURRENT_TIMESTAMP where session='$chk' and isBanned=0");
```

### admin/pageEditMember.php
Around line 64, replace the following code
```php
sql("INSERT INTO `membership_users` set memberID='{$memberID}', passMD5='" . md5($password) . "', email='{$email}', signupDate='" . @date('Y-m-d') . "', groupID='{$groupID}', isBanned='{$isBanned}', isApproved='{$isApproved}', {$customs_sql} comments='{$comments}'", $eo);
```
with this code
```php
sql("INSERT INTO `membership_users` set memberID='{$memberID}', passPHP='" . password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]) . "', email='{$email}', signupDate='" . @date('Y-m-d') . "', groupID='{$groupID}', isBanned='{$isBanned}', isApproved='{$isApproved}', {$customs_sql} comments='{$comments}'", $eo);	
```
Around line 101, replace
```php
$non_superadmin_sql = "passMD5=" . ($password != '' ? "'" . md5($password) . "'" : "passMD5") . ", email='{$email}', groupID='{$groupID}', isBanned='{$isBanned}', isApproved='{$isApproved}', ";
```
with
```php
$non_superadmin_sql = "passPHP=" . ($password != '' ? "'" . password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]) . "'" : "passPHP") . ", email='{$email}', groupID='{$groupID}', isBanned='{$isBanned}', isApproved='{$isApproved}', ";
```
Around line 109, replace
```php
$non_superadmin_sql = "passMD5='{$admin_pass_md5}', email='{$admin_email}', isBanned='0', isApproved='1', ";
```
with
```php
$non_superadmin_sql = "passPHP='" . password_hash($adminConfig['adminPassword'], PASSWORD_BCRYPT, ['cost' => 12]) . "', email='{$admin_email}', isBanned='0', isApproved='1', ";
```
### admin/pageSettings.php
Around line 44, replace the following code
```php
$adminPassword = md5($adminPassword);
```
with
```php
$adminPassword = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
````

Around line 108, replace the following code
```php
sql( "update membership_users set memberID='$adminUsername', passMD5='$adminPassword', email='{$post['senderEmail']}', comments=concat_ws('', comments, '\\n', '".str_replace ( "<DATE>" , @date('Y-m-d') , $Translation['record updated automatically'] ) ."') where lcase(memberID)='" . makeSafe(strtolower($adminConfig['adminUsername'])) . "'" , $eo);
```
with the following code
```php
sql( "update membership_users set memberID='$adminUsername', passPHP='$adminPassword', email='{$post['senderEmail']}', comments=concat_ws('', comments, '\\n', '".str_replace ( "<DATE>" , @date('Y-m-d') , $Translation['record updated automatically'] ) ."') where lcase(memberID)='" . makeSafe(strtolower($adminConfig['adminUsername'])) . "'" , $eo);
```

