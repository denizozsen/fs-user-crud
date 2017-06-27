# FS User Crud

FS User Crud is a tool for creating entries in a user table.

## Introduction

FS User Crud is a framework for creating, reading, updating and deleting user records. It features:

* An MVC framework for the basic CRUD operations
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

## Usage

The functionality is provided by the command-line PHP application src/cli.php, which is used as follows.

### Display usage instructions
```bash
$ php src/cli.php
```

### List all users
```bash
$ php src/cli.php --retrieve-all
```

### List users matching filters
```bash
$ php src/cli.php --retrieve FILTERS
```

### Create a new user
```bash
$ php src/cli.php --create "email=some.one@mail.com&first_name=John&last_name=Doe&password=hardT0Gue55"
```

### Update the email of existing user with id 123
```bash
$ php src/cli.php --update "user_id=123&email=changed@mail.com"
```
