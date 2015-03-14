# Password Recovery #

In the event that you forget your password or otherwise become locked out of the application, I have written a script called `password_recovery.php` that can be used to reset any user's password. The usefulness of this script is dependent on your knowledge of the username of an account with administrator permissions.

You can download this script here: [Downloads](http://code.google.com/p/collate-inventory/downloads/list)

Once you have downloaded this script, unpack it and place it in the same directory as the rest of the application. (In the single user/demo version, this would be `W:\www\inventory` while the application is running.) Browse to the `password_recovery.php` page on your server and you will be shown a form to reset the password of any user account.


If you do not know the name of an account with administrator priveledges, you will have to manually change /settings/checklevel5perms, /settings/checklevel3perms/, and /settings/checklevel1perms to "0" in the database. The SQL for this action would look something like this:
```
"UPDATE settings SET value='0' WHERE name='checklevel1perms' OR name='checklevel3perms' OR name='checklevel5perms';"
```


