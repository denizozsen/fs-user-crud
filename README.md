# FS User Crud

FS User Crud is a tool for creating entries in a user table.

## Introduction

FS User Crud is a framework for creating, reading, updating and deleting user records, within a MySQL database.
It features:

* A command-line interface that lets the user issue CRUD commands
* An MVC framework, used by the command-line interface
* A database schema to create the user table
* Dependency management via Composer
* 100% method code coverage by PHPUnit tests
* Full code documentation via PHPDoc comments
* Compliance with PSR-2 coding standards
* A Vagrant setup for running MySQL

## Pre-requisites

The following need to be installed on your machine, before you can set up and use fs-user-crud.

* PHP >=5.6
* git
* Composer
* Vagrant
* VirtualBox

## Dependencies installed via Composer

The only dependency is AOrm - a simple ORM library for PHP.

## Development dependencies installed via Composer

PHPUnit is set as a require-dev dependency, which is only used for development or testing purposes, and therefore
optional.

## Set up instructions

### Download and install dependencies
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

### List users that match given filters, e.g. where the last name contains "Smith" and the email contains "gmail.com"
```bash
$ php src/cli.php --retrieve "last_name=Smith&email=gmail.com"
```

### Create a new user
```bash
$ php src/cli.php --create "email=some.one@mail.com&first_name=John&last_name=Doe&password=hardT0Gue55"
```

### Update the email of existing user with id 123
```bash
$ php src/cli.php --update "user_id=123&email=changed@mail.com"
```

### Delete the existing user with id 5
```bash
$ php src/cli.php --delete 5
```
