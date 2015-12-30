<?php
echo 'Contact page tests' . "\n";

/**
 * HAL + JSON
 */
echo ' - testing HAL + JSON' . "\n";
$url = $base . 'index.php';
$test = (new WebserviceTestHalJson)->get($url);

$data = $test->data;
$links = $data->_links;
$test->it('should pass if the home page includes a contacts element', isset($links->contacts));

// Follow the contacts link.
$url = $links->contacts->href;
$test = (new WebserviceTestHalJson)->get($url, 'contacts');
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertSelf();
$test->assertPagination();
$test->assertEmbedded('contacts',
	[
		'address',
		'country',
		'description',
		'email',
		'fax',
		'featured',
		'image',
		'language',
		'locality',
		'name',
		'position',
		'postcode',
		'region',
		'telephone',
	]
);

$data = $test->data;
$test->it('should pass if the page limit is set to 20', $data->pageLimit == 20);
$test->it('should pass if there are 8 items available', $data->totalItems == 8);
$test->it('should pass if there is 1 page available', $data->totalPages == 1);

// Check that we have the correct data for the first item in the embedded list.
echo 'Checking the first embedded contact item in more detail' . "\n";
$testData = [
	'address'		=> 'Street Address',
	'country'		=> 'Country',
	'description'	=> '<p>Information about or by the contact.</p>',
	'email'			=> 'email@example.com',
	'fax'			=> 'Fax',
	'featured'		=> 'true',
	'image'			=> 'images/powered_by.png',
	'language'		=> 'en-GB',
	'locality'		=> 'Suburb',
	'name'			=> 'Contact Name Here',
	'position'		=> 'Position',
	'postcode'		=> 'Zip Code',
	'region'		=> 'State',
	'telephone'		=> 'Telephone',
];
$item = $data->_embedded->contacts[0];

foreach ($testData as $key => $value)
{
	$test->it('should pass if the ' . $key . ' entry is present and correct for the first item in the _embedded list', isset($item->$key) && $item->$key == $value);
}

// Follow the link to contact id 1.
echo 'Following link to contact with id 1' . "\n";

// Follow the link to the first contact item listed.
$item = $data->_embedded->contacts[0];
$url = $item->_links->self->href;
$test = (new WebserviceTestHalJson)->get($url, 'contacts');
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertSelf();

$data = $test->data;
$testData = [
	'name'			=> 'Contact Name Here',
	'alias'			=> 'name',
	'position'		=> 'Position',
	'address'		=> 'Street Address',
	'locality'		=> 'Suburb',
	'region'		=> 'State',
	'country'		=> 'Country',
	'postcode'		=> 'Zip Code',
	'telephone'		=> 'Telephone',
	'fax'			=> 'Fax',
	'description'	=> '<p>Information about or by the contact.</p>',
	'image'			=> 'images/powered_by.png',
	'email'			=> 'email@example.com',
	'default'		=> 'true',
	'published'		=> 'published',
	'ordering'		=> 1,
	'mobile'		=> '',
	'webpage'		=> '',
	'sortname1'		=> 'last',
	'sortname2'		=> 'first',
	'sortname3'		=> 'middle',
	'language'		=> 'en-GB',
	'metakey'		=> '',
	'metadesc'		=> '',
	'metadata'		=> '',
	'featured'		=> 'true',
	'publish_up'	=> '',
	'publish_down'	=> '',
	'version'		=> 1,
	'hits'			=> 0,
];

foreach ($testData as $key => $value)
{
	$test->it('should pass if the ' . $key . ' entry is present and correct', isset($data->$key) && $data->$key == $value);
}

/**
 * HAL + XML
 */
echo ' - testing HAL + XML' . "\n";
$url = $base . 'index.php';
$test = (new WebserviceTestHalXml)->get($url);

$data = $test->data;
$links = $data->_links;
$url = $test->assertLink('contacts');

// Follow the link to the contacts collection.
$test = (new WebserviceTestHalXml)->get($url, 'contacts');
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertSelf();
$test->assertPagination();
$test->assertEmbedded('contacts',
	[
		'address',
		'country',
		'description',
		'email',
		'fax',
		'featured',
		'image',
		'language',
		'locality',
		'name',
		'position',
		'postcode',
		'region',
		'telephone',
	]
);

$data = $test->data;
$test->it('should pass if the page limit is set to 20', $data->pageLimit == 20);
$test->it('should pass if there are 8 items available', $data->totalItems == 8);
$test->it('should pass if there is 1 page available', $data->totalPages == 1);

// Check that we have the correct data for the first item in the embedded list.
echo 'Checking the first embedded contact item in more detail' . "\n";
$testData = [
	'address'		=> 'Street Address',
	'country'		=> 'Country',
	'description'	=> '<p>Information about or by the contact.</p>',
	'email'			=> 'email@example.com',
	'fax'			=> 'Fax',
	'featured'		=> 'true',
	'image'			=> 'images/powered_by.png',
	'language'		=> 'en-GB',
	'locality'		=> 'Suburb',
	'name'			=> 'Contact Name Here',
	'position'		=> 'Position',
	'postcode'		=> 'Zip Code',
	'region'		=> 'State',
	'telephone'		=> 'Telephone',
];
$item = $data->resource[0];

foreach ($testData as $key => $value)
{
	$test->it('should pass if the ' . $key . ' entry is present and correct for the first item in the _embedded list', isset($item->$key) && $item->$key == $value);
}

// Follow the link to contact id 1.
echo 'Following link to contact with id 1' . "\n";

// Follow the link to the first contact item listed.
$item = $data->resource[0];
$url = $item['href'];
$test = (new WebserviceTestHalXml)->get($url, 'contacts');
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertSelf();

$data = $test->data;
$testData = [
	'name'			=> 'Contact Name Here',
	'alias'			=> 'name',
	'position'		=> 'Position',
	'address'		=> 'Street Address',
	'locality'		=> 'Suburb',
	'region'		=> 'State',
	'country'		=> 'Country',
	'postcode'		=> 'Zip Code',
	'telephone'		=> 'Telephone',
	'fax'			=> 'Fax',
	'description'	=> '<p>Information about or by the contact.</p>',
	'image'			=> 'images/powered_by.png',
	'email'			=> 'email@example.com',
	'default'		=> 'true',
	'published'		=> 'published',
	'ordering'		=> 1,
	'mobile'		=> '',
	'webpage'		=> '',
	'sortname1'		=> 'last',
	'sortname2'		=> 'first',
	'sortname3'		=> 'middle',
	'language'		=> 'en-GB',
	'metakey'		=> '',
	'metadesc'		=> '',
	'metadata'		=> '',
	'featured'		=> 'true',
	'publish_up'	=> '',
	'publish_down'	=> '',
	'version'		=> 1,
	'hits'			=> 0,
];

foreach ($testData as $key => $value)
{
	$test->it('should pass if the ' . $key . ' entry is present and correct', isset($data->$key) && $data->$key == $value);
}
