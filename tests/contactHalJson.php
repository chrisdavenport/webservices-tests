<?php
echo 'Contact page tests' . "\n";

/**
 * HAL + JSON
 */
echo ' - testing contact collection page in HAL + JSON' . "\n";
echo '   - retrieving home page' . "\n";
$test = (new WebserviceTestHalJson)->get($base);

$data = $test->getData();
$links = $data->_links;
$test->it('should pass if the home page includes a contacts element', isset($links->contacts));

// Follow the contacts link.
echo '   - checking the contacts collection page' . "\n";
$url = $links->contacts->href;
$test = (new WebserviceTestHalJson('contacts'))->get($url);
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

$data = $test->getData();
$test->it('should pass if the page limit is set to 20', $data->pageLimit == 20);
$test->it('should pass if there are 8 items available', $data->totalItems == 8);
$test->it('should pass if there is 1 page available', $data->totalPages == 1);

// Check that we have the correct data for the first item in the embedded list.
echo '   - checking the first embedded contact item in more detail' . "\n";
$test->assertData($data->_embedded->contacts[0],
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

// Follow the link to contact id 1.
echo '   - following link to contact with id 1' . "\n";

// Follow the link to the first contact item listed.
$item = $data->_embedded->contacts[0];
$url = $item->_links->self->href;

echo ' - testing contact page in HAL + JSON' . "\n";
$test = (new WebserviceTestHalJson('contacts'))->get($url);
$test->assertStatus(200);
$test->assertLink('contents', $base);
$test->assertLink('collection', $base . 'contacts');
$test->assertLink('j:category', $base . 'categories/16');
$test->assertLink('author', $base . 'users/368');
$test->assertSelf($base . 'contacts/1');

$test->assertData($test->getData(),
	[
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
		'publish_up'	=> '1970-01-01T00:00:00+0100',
		'publish_down'	=> '1970-01-01T00:00:00+0100',
		'version'		=> 1,
		'hits'			=> 0,
	]
);

echo ' - check that we get a required field error when creating a new contact with no data' . "\n";
$test = (new WebserviceTestHalJson('contacts'))->post($base . 'contacts', []);
$test->assertStatus(406);
$data = $test->getData();

$test->it('should pass if the data returned contains a _messages element', isset($data->_messages));
$test->it('should pass if the _messages element is an array', is_array($data->_messages));
$test->it('should pass if the _messages array has exactly one element', count($data->_messages) == 1);
$test->it('should pass if the first _messages element has a type element', isset($data->_messages[0]->type));
$test->it('should pass if the first _messages element has a type element = \'error\'', $data->_messages[0]->type == 'error');
$test->it('should pass if the first _messages element has a message element', isset($data->_messages[0]->message));
$test->it('should pass if the first _messages element has a message element = \'Field \'name\' is required.\'', $data->_messages[0]->message == 'Field \'name\' is required.');

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
