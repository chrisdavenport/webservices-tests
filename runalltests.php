<?php
function throws($exp,\Closure $cb){try{$cb();}catch(\Exception $e){return $e instanceof $exp;}return false;}

$base = 'http://localhost/webservices-test2/fullcms/www/';

include 'haljson.php';
include 'halxml.php';
include 'tests/home.php';
include 'tests/contact.php';
