F1 API PHP Tests
====================

F1 API Tests is a suite of tests for the Fellowship One API based on the PHPUnit framework.  It is designed to enable rapid testing of all of the methods available for a given realm in a given environment.  It traverses the entire realm, using response data to construct new requests.  The HTTP Response Code and Response Body are evaluated for every method in the realm.  

It may also be used as an example of how to consume any of the methods in our API.


License
-------
F1 API PHPUnit Tests is free and open source software.
[See license details here](https://github.com/fellowshiptech/f1api-php-tests/blob/master/license.txt).


Requirements
-------------

* PHP 5.3 or higher.
* PECL OAuth Extension. [Details Here](http://php.net/oauth).
* Composer
* PHPUnit Test Framework.  [Installation instructions here](http://www.phpunit.de/manual/current/en/installation.html).
* Terminal / Command Line.
* Xdebug Extension for logs. [Installation instructions here](http://xdebug.org/).


Run Test Suite
---------------

In order to run a suite of tests on a given realm, do the following:

Rename settings.template.php to settings.php

Enter api key, secret, username and password for each environment in settings.php

Set the appropriate environment within the "setupBeforeClass" method.

Run following in your command line

    $ composer update 
    $ cd f1api-php-tests/tests
    $ ../vendor/bin/phpunit --debug people

Tests are separated into realms - people.php, giving.php, groups.php, events.php

To only run a group of tests within a realm, use:

	$ ../vendor/bin/phpunit --debug --group RecurrenceTypes events

PHPUnit supports the declaration of explicit dependencies between test methods. Such dependencies do not define the order in which the test methods are to be executed but if a method which is dependent on another method fails, the consequent tests will be skipped.