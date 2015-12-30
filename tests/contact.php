<?php
echo 'Contact page tests' . "\n";

$url = $base . 'index.php';

/**
 * HAL + JSON
 */
echo ' - testing HAL + JSON' . "\n";
$test = (new WebserviceTestHalJson)->get($url);

$data = $test->data;
$links = $data->_links;
$test->it('should pass if the home page includes a contacts element', isset($links->contacts));

// Follow the contacts link.
$url = $links->contacts->href;
$test = (new WebserviceTestHalJson)->get($url, 'contacts');
$test->assertStatus(200);
$test->assertBase($base);
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

// Find the contact with id 1.
echo 'Embedded contact items:-' . "\n";

foreach ($data->_embedded->contacts as $item)
{
	$test->it('should pass if the embedded item has a name property', isset($item->name));
	echo $item->name . "\n";

	$test->it('should pass if the embedded item has a _links property', isset($item->_links));
	$test->it('should pass if the _links property has a self entry', isset($item->_links->self));
	$test->it('should pass if the self entry has an href property', isset($item->_links->self->href));
}

// Follow the link to contact id 1.
echo 'Following link to contact with id 1' . "\n";

// Follow the link to the first contact item listed.
$item = $data->_embedded->contacts[0];
$url = $item->_links->self->href;
$test = (new WebserviceTestHalJson)->get($url, 'contacts');
$test->assertStatus(200);
$test->assertBase($base);
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
