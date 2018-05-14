# Securing passwords in AppGini 5.70
AppGini upto version 5.70 is utilizing MD5 to hash user passwords in the _membership_users_ table.  There are [many](https://en.wikipedia.org/wiki/MD5#Overview_of_security_issues) reasons why MD5 should not be used, an issue I have reported to the developers of AppGini back in January 2018 (we're in May, and still no solution in sight).  This is a serious issue, and once that demands a solution.  So here is my take on it.  While it is manual, the following patch to your generated AppGini code will make your passwords a lot more secure.

*Do note that this will break the _Remember Me_ function, which is also badly broken, using yet another MD5 function that contains the password.*

### incCommon.php
If you have an existing system, then getting your users reset their passwords will be a very time consuming activity.  This fix will allow the existing MD5 passwords to remain, and will replace them with more secure passwords as soon as the user logs on the next time.

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
				$passwordDB = sqlValue("select passMD5 from membership_users where lcase(memberID)='$username' and isApproved=1 and isBanned=0");
				if($passwordDB == $password && preg_match('/^[a-f0-9]{32}$/i',$passwordDB)) {

					// this means we managed to authenticate with the MD5 password
					// we need to convert the password to a more secure hashing algorithm
					$options = [
			    			'cost' => 12,
		    			];
					$passwordHash = password_hash($_POST['username'], PASSWORD_BCRYPT, $options);

					db_query("update membership_users set passMD5='$passwordHash' where lcase(memberID)='$username' and isApproved=1 and isBanned=0");
				}
				
				if(password_verify($_POST['password'],$passwordDB)) {
```

## TODO
Change password code
