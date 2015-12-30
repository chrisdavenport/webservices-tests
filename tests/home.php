<?php
echo 'Home page tests' . "\n";

$url = $base . 'index.php';

/**
 * HAL + JSON
 */
echo ' - testing HAL + JSON' . "\n";
$test = (new WebserviceTestHalJson)->get($url);
$test->assertStatus(200);

$data = $test->data;

$test->it('should pass if data includes a _links element', isset($data->_links));

$links = $data->_links;

$test->assertLink('contents', $base);

$test->it('should pass if _links element includes a home element', isset($links->home));

/**
 * HAL + XML
 */
echo ' - testing HAL + XML' . "\n";
$test = (new WebserviceTestHalXml)->get($url);
$test->assertStatus(200);

$data = $test->data;

$test->assertLink('contents', $base);
