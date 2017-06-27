# fs-user-crud

TODO - description

## Pre-requisites

The following

* PHP >=5.6
* git
* Composer
* Vagrant
* VirtualBox

## How to set it up

### Download and install pre-requisites
```bash
$ git clone git@github.com:denizozsen/fs-user-crud

$ cd fs-user-crud

$ composer install
```

### Set up virtual machine
```bash
$ vagrant up
```

### Create user table
```bash
$ vagrant ssh

$ cat /vagrant/db_schema.sql | mysql -Dmy_app

$ exit
```
