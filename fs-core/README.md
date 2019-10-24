# fs-core install instructions

A Cake based framework, customized by Kien Truong


## Requires

1. Apache/Nginx web-server
2. Mysql server
3. Php 7 or 5.6

Hint: You can simply install Xampp for all of above things.

4. Composer: https://getcomposer.org/

## Steps to deploy a local build

This is the a general guidelines to deploy a project written based-on fs-core, not the fs-core itself.


1. Clone source code to local, I will refer the root Dir at [Root_Dir]

2. Config a virtual host (Apache or Nginx) point to [Root_Dir]/src

3. Go to [Root_Dir]/src, with bash/powershell, run the install script:

> $ composer install

4. Create a new database (I prefer utf8-general ci for collation), then config the datasource in src/config/app.php

5. Run sql scripts in \src\vendor\fs-core\config\schema\ on created database

6. Run sql scripts in \src\config\schema\ on created database

7. reload apache/nginx server


## Admin User creation

From the first time you visit website back end at: examplesite.local/backend

Enter a username/password, this credential will be save and can be use as of admin user.


