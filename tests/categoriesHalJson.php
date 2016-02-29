<?php
echo 'Categories page tests' . "\n";

/**
 * HAL + JSON
 */
echo ' - testing categories collection page in HAL + JSON' . "\n";
echo '   - retrieving home page' . "\n";
$test = (new WebserviceTestHalJson)->get($base);

$data = $test->getData();
$links = $data->_links;
$test->it('should pass if the home page includes a categories element', isset($links->categories));

// Follow the categories link.
echo '   - checking the categories collection page' . "\n";
$url = $links->categories->href;
$test = (new WebserviceTestHalJson('categories'))->get($url);
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertSelf();
$test->assertPagination();

$test->assertEmbedded('categories',
	[
        'id',
        'path',
        'extension',
        'alias',
        'language',
		'title',
	]
);

$data = $test->getData();
$test->it('should pass if the page limit is set to 20', $data->pageLimit == 20);
$test->it('should pass if there are 63 items available', $data->totalItems == 63);
$test->it('should pass if there is 4 pages available', $data->totalPages == 4);

// Check that we have the correct data for the first item in the embedded list.
echo '   - checking the first embedded contact item in more detail' . "\n";
$test->assertData($data->_embedded->categories[0],
	[
        'id'        => 'urn:joomla:categories:1',
        'title'     => 'ROOT',
        'path'      => '',
        'extension' => 'system',
        'alias'     => 'root',
        'language'  => '*',
        'state'     => 'published',
	]
);

// Check that we have the correct data for the eighth item in the embedded list.
echo '   - checking the eighth embedded contact item in more detail' . "\n";
$test->assertData($data->_embedded->categories[7],
	[
        'id'        => 'urn:joomla:categories:16',
        'title'     => 'Sample Data-Contact',
        'path'      => 'sample-data-contact',
        'extension' => 'com_contact',
        'alias'     => 'sample-data-contact',
        'language'  => '*',
        'state'     => 'published',
	]
);

// Follow the link to category id 16 (the eighth item on the list).
echo '   - following link to category with id 16' . "\n";

// Follow the link to the eighth category item listed.
$url = $data->_embedded->categories[7]->_links->self->href;

echo ' - testing category page in HAL + JSON' . "\n";
$test = (new WebserviceTestHalJson('categories'))->get($url);
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertLink('collection', $base . 'categories');
$test->assertLink('up', $base . 'categories/1');
$test->assertLink('j:contacts', $base . 'categories/16/contacts');
$test->assertSelf($base . 'categories/16');
$test->assertData($test->getData(),
	[
		'level'			=> '1',
		'path'			=> 'sample-data-contact',
		'extension'		=> 'com_contact',
		'alias'			=> 'sample-data-contact',
		'note'			=> '',
		'description'	=> '',
		'state'			=> 'published',
		'metadesc'		=> '',
		'metakey'		=> '',
		'pageTitle'		=> '',
		'hits'			=> '0',
		'language'		=> '*',
		'version'		=> '1',
	]
);

// Follow the link to the contacts collection.
echo '   - following link to contacts associated with category id 16' . "\n";
$contactsRel = 'j:contacts';
$url = $test->getData()->_links->{$contactsRel}->href;
$test = (new WebserviceTestHalJson('contacts'))->get($url);
$test->assertStatus(200);
$test->assertSelf($base . 'categories/16/contacts');
$test->assertPagination();
$test->assertData($test->getData(),
	[
		'page'			=> '1',
		'pageLimit'		=> '20',
		'limitstart'	=> '0',
		'totalItems'	=> '1',
		'totalPages'	=> '1',
	]
);
$test->assertData($test->getData()->_embedded->contacts[0],
	[
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
	]
);



/*
echo ' - creating a new contact' . "\n";
$contact = array(
	'name'			=> 'Chris Davenport',
	'alias'			=> 'chris-davenport',
	'position'		=> 'Sitting',
	'address'		=> 'Somewhere',
	'locality'		=> 'Sometown',
	'region'		=> 'Shropshire',
	'country'		=> 'United Kingdom',
	'postcode'		=> 'AB12 3CD',
	'telephone'		=> '01234 567890',
	'fax'			=> '01234 567891',
	'published'		=> 'published',
	'email'			=> 'test@domain.com',
);
$test = (new WebserviceTestHalJson('contacts'))->post($base . 'contacts', $contact);
$test->assertStatus(201);
$test->assertHeader('Location');

echo ' - following Location header' . "\n";
$url = $base . $test->headers['Location'];
echo ' - testing newly created contact page in HAL + JSON' . "\n";
$test = (new WebserviceTestHalJson('contacts'))->get($url);
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertSelf();
$data = $test->getData();

foreach ($contact as $key => $value)
{
	$test->it('should pass if the ' . $key . ' entry is present and correct', isset($data->$key) && $data->$key == $value);
}

echo ' - deleting newly created contact page' . "\n";
$test = (new WebserviceTestHalJson('contacts'))->delete($url);
$test->assertStatus(200);

echo ' - check that contact was deleted' . "\n";
$test = (new WebserviceTestHalJson('contacts'))->get($url);
$test->assertStatus(404);
*/