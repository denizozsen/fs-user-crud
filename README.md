# FS User Crud

## Introduction

FS User Crud is a framework for creating, reading, updating and deleting user records, within a MySQL database.
This project is an exercise in creating a well-structured PHP project with Composer dependencies, namespacing,
unit tests and more.

The project includes:

* A command-line interface that lets the user issue CRUD commands
* An MVC framework, used by the command-line interface
* A database schema to create the user table
* Dependency management via Composer
* 100% method code coverage by PHPUnit tests
* Full code documentation via PHPDoc comments
* Compliance with PSR-2 coding standards
* A Vagrant setup for running MySQL

## A note on "re-inventing the wheel"

If this was a real-world application, I would certainly re-use existing well-tested software for parts, such as a
command-line tool framework or an argument parser. However I chose to implement everything myself, as an exercise.
The AOrm project dependency was also developed by me.

## Pre-requisites

The following need to be installed on your machine, before you can set up and use fs-user-crud.

* PHP >=5.6 (if installing dev dependencies, PHP >= 7.0 is required)
* git
* Composer
* Vagrant
* VirtualBox

## Dependencies installed via Composer

The only production dependency is AOrm - a simple ORM library for PHP.

## Development dependencies installed via Composer

PHPUnit is set as a require-dev dependency, which is only used for development or testing purposes, and therefore
optional.

## Set up instructions

### Download and install dependencies
```bash
$ git clone git@github.com:denizozsen/fs-user-crud

$ cd fs-user-crud

$ composer install --no-dev
```
**Note 1**
If you don't have a GitHub SSH key setup, you can use the HTTPS method for cloning the repository, by replacing the
first of these commands with `git clone https://github.com/denizozsen/fs-user-crud.git`
**Note 2**
If you wish to run the PHP Unit tests, you have to install the dev requirements, too. To do this, simply run
`composer install` without the --no-dev option. You will need at least version 7.0 of PHP for this.

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

The functionality is provided by the command-line PHP application src/cli.php. The following assumes that the current
working directory in your terminal is the root of the cloned fs-user-crud repository.

Note: the Vagrant configuration is such that you should be able to use these commands from your host OS. However, if
you experience any errors related to the MySQL connection, please use the command-line interface from within the guest
OS, by running the following commands first, from within the project root:
```bash
$ vagrant ssh

$ cd /vagrant
```

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
