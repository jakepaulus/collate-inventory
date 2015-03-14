# Documentation #

Many features of this application are self-explanatory and so will not be
covered here. I will instead attempt to cover only features that may be unclear
or functions that have multiple functionalities based on the type of user
that is using them.


## Installation ##

Installing this program is a two-part process. I assume that you already have
a PHP-enabled web server and a MySQL database server installed and configured.

Start by unpacking the zip archive you downloaded from Collate.info. Browse
the folder you have unpacked for `/include/db_connect.php`. There are four settings
in this file that you must change: the database server's IP Address/Hostname, the
database username, the database password, and the name of the database for
Collate:Inventory to use. The database username you specify should have full
rights to the database.

Now that you have prepared the program, upload it to your webserver. Once in
place, browse to the install.php file on your webserver with any web browser.
Browsing to this page will load the necessary tables into the database. This
process will delete any existing tables of the same names if they exist, so it
is important that you use a blank database. It is important to delete the
install.php file from your web server once you are given a notice stating
installation was successful or someone browsing to the page would wipe out
your database!



## General Usage ##

By default, full access is granted for all people browsing the site without
logging in. To correct this, go to the Control Panel and use the Settings page
to lock the application down. You will find, however, that you cannot do this
without first creating a user with Administrator rights and setting a password
for this user.

There is a special user account the program uses called "system." This is the
account that hardware is assigned to when it is considered "in inventory." This
account cannot be used to login.



## Security ##
Features in this application divided into four access levels. Though they are
specified with numbers within the progam (0, 1, 3, 5), they can be named as
follows:

  * No Access
  * Read-Only Acces
  * Read+Write Access
  * Administrator Access

Most of the application of these access levels is obvious. The access level is
used to restrict access at a function level within the program. There are a few
exceptions to this however. A good example is the New User form. A user with
Read+Write access can add a user. They cannot, however, set the new user's
access level (the new user will default to 0, or no access.)



## A note on Asset Numbers ##
One feature of this application is automatic asset number generation. You can
ofcoarse type your own asset number in or disable this feature all together on
the settings page in the control panel. Automatically generated asset numbers
use the following format:
```
[letter][number]Y[number]
```

The first part, the letter, is actually the month the number was generated converted
to a letter. So, A is January, B is February, etc. The number is an incremental value
that counts up one digit per new hardware added since the application was installed.
The letter Y separates the two numbers. Lastly, the second number given is the two digit
year that the asset number was generated.

Most people find asset numbers shorter and easier to work with than serial numbers. This
is why I have added support for them. Due to the way that the search feature works to
make finding assets easier however, it is necessary for all asset numbers and serial
numbers to be collectively unique.

