# Facebook (Oath2) integration

## Configure Facebook
Before you can use this, you have to setup the application in [Facebook](https://developers.facebook.com/docs/facebook-login/).  There are a few things to note :
* You must configure the callback URL (typically it will be your app URL /hooks/facebook.php)
* You must specify your server's IP address
* You must obtain the client ID, and the application secret.

## Adding the plugin
Download the [image](facebook-login-blue.png), and [plugin](facebook.php), and save them to your hooks folder. Next, you need to edit the _facebook.php_ script, and adjust it to your own needs.
## Configure the plugin
* Once you've configured the Facebook application, update the client ID and the Application secret in the script with the ones you obtained from Facebook earlier.
* Create a group in your application that will be the default group added to all new Facebook users, and setup the permissions correctly.
Update the _$GROUP_ variable with the name of the group you've configured. 

### login.php
Edit _login.php_ and around line 48, add the following HTML code
```html
<a href="hooks/facebook.php"><img src="hooks/facebook-login-blue.png"></a>
```
If everything is setup correctly, you should see a Login to Facebook button on your main page, and when you click it, you should be authenticated to your application.
