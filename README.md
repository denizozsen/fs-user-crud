# FS User Crud

FS User Crud is a tool for creating entries in a user table.

## Why

The purpose of this project is for me to practice creating a PHP project with dependencies. It includes the following:

* An MVC structure for the basic CRUD operations for manipulating user records
* A database schema (or maybe a phinx migration) to create the database table(s)
* Dependency management via Composer
* 100% code coverage with PHPUnit tests
* Full code documentation via PHP code blocks
* Compliance with PSR-2 coding standards
* A Vagrant setup for running MySQL (and maybe a webserver)

## Pre-requisites

The following need to be installed on your machine, before you can set up and use fs-user-crud.

* PHP >=5.6
* git
* Composer
* Vagrant
* VirtualBox

## Set up instructions

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
