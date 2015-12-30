# webservices-tests
A suite of tests for the webservices project (https://github.com/chrisdavenport/webservices).

* git clone https://github.com/chrisdavenport/webservices.git
* cd webservices-tests
* In runalltests.php change the $base variable to point to the Joomla webservices API.
* php runalltests.php

The tests assume a default Joomla installation with the Test Sample Data set installed.

The "It should pass if the published entry is present and correct" test is currently failing due to
the transforms being called twice.  See https://github.com/joomla-projects/webservices/issues/14
