# Facebook Authentication with OATH2

## About the plugin
I like AppGini.  It is a great tool for rapidly developing websites.  Out of the box, AppGini does not support authentication against a centralized directory services.  When developing a Cloud-based solution for your customers, they may want to authenticate against their own [ADFS](https://en.wikipedia.org/wiki/Active_Directory_Federation_Services) server to provide [single signon](https://en.wikipedia.org/wiki/Single_sign-on) capability.  While the plugin is specific to Facebook, it is written without any special plugins using only native [OATH2](https://oauth.net/2/) API calls, and could easily be adjusted to any OATH2 provider.
The plugin is not a replacement for the existing authentication in AppGini.  It works in addition to it, creating new users that come in via Facebook, and allocating a specific group to them.
## Concerns raised about the plugin
### If I loose control over my Facebook account, all my sites will be isolated
The risk of losing control over a single Facebook account is far lower than any single site getting comprimised, since AppGini does not offer any kind of brute force protection, and the passwords are also hashed to MD5 in the backend (a hashing algorithm that should not be used for passwords)

You should ensure that your Facebook account has a secure and unique password, and that multi-factor authentication is enabled.  If for whatever reason you do loose the Facebook account, you can always log onto the AppGini server, and adjust the _membership_users_ table directly and update the _passMD5_ field.

## Ideas for future improvements
* User Creation - while users can automatically be created, do not automatically allocate them to a group.  admin will need to approve the facebook accounts first.

## Configure Facebook
Before you can use this, you have to setup the application in [Facebook](https://developers.facebook.com/docs/facebook-login/).  There are a few things to note :
* You must configure the callback URL (typically it will be your app URL /hooks/facebook.php)
* You must specify your server's IP address (where the calls will be made from)
* You must obtain the client ID, and the application secret.
* Before you go to production, you have to publish the application in Facebook.

## Adding the plugin
Download the [image](facebook-login-blue.png), and [plugin](facebook.php), and save them to your hooks folder. Next, you need to edit the _facebook.php_ script, and adjust it to your own needs.
## Configure the plugin
* Once you've configured the Facebook application, update the client ID and the Application secret in the script with the ones you obtained from Facebook earlier.
* Create a group in your application that will be the default group added to all new Facebook users, and setup the permissions correctly.
Update the _$GROUP_ variable with the name of the group you've configured. 

### login.php
Edit _login.php_ and around line 48, add the following HTML code
```html
<div class="col-sm-offset-2 col-sm-6"><a href="hooks/facebook.php"><img src="hooks/facebook-login-blue.png"></a></div>
```
If everything is setup correctly, you should see a Login to Facebook button on your main page, and when you click it, you should be authenticated to your application.

## Troubleshooting
If the plugin is not working, chances are that there is something wrong with the way you have configured the application within Facebook.  The plugin will give you some feedback, especially the errors reported, so just follow the prompts, and read the Facebook documentation.
