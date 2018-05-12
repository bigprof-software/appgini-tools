# Google Authenticator in AppGini
There may be various reasons why you'd like to enable multi factor authentcation. It protects your application with a little bit more protection when users may have poor password management skills.

## Installation
* Download the [GoogleAuthenticatorClass.php](https://github.com/massyn/php-framework/blob/master/GoogleAuthenticatorClass.php) file and save it to your _hooks_ folder.
* Download the [setup_googleauth.php](setup_googleauth.php) and save it to your _hooks_ folder.
* Edit the _links-navmenu.php_ file and add the following text to the file
```php
	$navLinks[] = array(
		'url' => 'hooks/setup_googleauth.php', 
		'title' => 'Google Authenticator', 
		'groups' => array('*'),
		'table_group' => 0
	);
  ```
  * Edit the _links-home.php_ file and add the following text to the file
  

## Next step installation (DRAFT)

* Update the login form
* Update the authentication code
