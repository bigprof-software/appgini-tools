=AppGini 5.70 – Authentication logging

Out of the box, AppGini does not provide logging of user authentication attempts.  For some of my projects, I do need to track who logs on when.  Using the hooks feature, I put the following code together that will log who has logged on (and who hasn’t).

Edit the _hooks/__global.php_ file, and add the following function.
```php
function logit ($memberInfo,$status)
{
   if(!sql('select 1 from tbl_logs',$e))
   {
      sql('create table tbl_logs (id integer auto_increment primary key,username varchar(100), datetime datetime,status varchar(100),ip varchar(30))',$e);
   }
   sql('insert into tbl_logs (username,datetime,status,ip) values(\'' . makeSafe($memberInfo['username']) . '\',CURRENT_TIMESTAMP,\'' . makeSafe($status) . '\',\'' . makeSafe($memberInfo['IP']) . '\')',$e);
   return '';
}
```
Now edit the two functions below, and add a link the procedure above.
```php
function login_ok($memberInfo, &$args){
   logit($memberInfo,'success');
   return '';
}
 
function login_failed($attempt, &$args){
   logit($attempt,'failed');
   return '';
}
```
And that should be it. The next time someone logs on (or tries to log on), a new table (tbl_logs) will be created with the details of who has logged on.
