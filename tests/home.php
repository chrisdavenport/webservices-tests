<?php
echo 'Home page tests' . "\n";

/**
 * HAL + JSON
 */
echo ' - testing home page in HAL + JSON' . "\n";
$test = (new WebserviceTestHalJson)->get($base);
$test->assertStatus(200);
$test->it('should pass if data includes a _links element', isset($test->getData()->_links));
$test->assertLink('contents', $base);

/**
 * HAL + XML
 */
echo ' - testing home page HAL + XML' . "\n";
$test = (new WebserviceTestHalXml)->get($base);
$test->assertStatus(200);
$test->assertLink('contents', $base);
