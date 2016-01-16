<?php
/**
 * Generic test class for HAL + JSON interactions.
 */
class WebserviceTestHalJson extends WebserviceTest
{
	/**
	 * Do an HTTP GET for a URL.
	 * 
	 * @param   string  $url  URL to GET.
	 * 
	 * @return  this object for chaining.
	 */
	public function get($url)
	{
		return parent::get($url, ['Accept: application/hal+json']);
	}

	/**
	 * Do an HTTP POST to a URL.
	 * 
	 * @param   string  $url   URL to POST to.
	 * @param   array   $data  Data to POST.
	 * 
	 * @return  this object for chaining.
	 */
	public function post($url, array $postData = array())
	{
		return parent::post($url, $postData, ['Accept: application/hal+json']);
	}

	/**
	 * Do an HTTP DELETE to a URL.
	 * 
	 * @param   string  $url  URL of resource to DELETE.
	 * 
	 * @return  this object for chaining.
	 */
	public function delete($url)
	{
		return parent::delete($url, ['Accept: application/hal+json']);
	}

	/**
	 * Get data from response.
	 * 
	 * @return  string
	 */
	public function getData()
	{
		return json_decode($this->data);
	}

	/**
	 * Assertions about data properties.
	 * 
	 * @param   object  $item      Object to be tested.
	 * @param   array   $testData  Array of key-value pairs that are expected to be present.
	 * 
	 * @return  void 
	 */
	public function assertData($item, array $testData = [])
	{
		foreach ($testData as $key => $value)
		{
			$this->it(
				'should pass if the ' . $key . ' entry is present and correct',
				isset($item->$key) && $item->$key == $value
			);
		}
	}

	/**
	 * Assertions about embedded content.
	 * 
	 * @param   string  $rel         An optional rel that must exist.
	 * @param   array   $properties  Optional array of key-value pairs that must be present in each embedded item.
	 * 
	 * @return  void
	 */
	public function assertEmbedded($rel = '', array $properties = [])
	{
		$data = $this->getData();

		$this->it('should pass if there is an _embedded element', isset($data->_embedded));

		if ($rel == '')
		{
			return;
		}

		$this->it('should pass if _embedded contains a rel called ' . $rel, isset($data->_embedded->{$rel}));
		$this->it('should pass if the _embedded rel contains an array of elements ', is_array($data->_embedded->{$rel}));
		$this->it('should pass if the _embedded rel array has the correct number of elements ', count($data->_embedded->{$rel}) == min($data->totalItems, $data->pageLimit));

		foreach ($data->_embedded->{$rel} as $k => $item)
		{
			$this->it('should pass if the _links property is present in entry ' . $k, isset($item->_links));
			$this->it('should pass if the _links property has a self entry', isset($item->_links->self));
			$this->it('should pass if the self entry has an href property', isset($item->_links->self->href));

			foreach ($properties as $property)
			{
				$this->it('should pass if the ' . $property . ' entry is present in entry ' . $k, isset($item->$property));
			}
		}
	}

	/**
	 * Assert that a link exists and optionally matches a given URL.
	 * 
	 * @param   string  $rel   Link relation to look for.
	 * @param   string  $href  Href to check.
	 * 
	 * @return  Href of the link that was found.
	 */
	public function assertLink($rel, $href = '')
	{
		$data = $this->getData();

		$this->it('should pass if there is a _links element with rel=' . $rel, isset($data->_links->{$rel}));

		if (!isset($data->_links->$rel) || $href == '')
		{
			return '';
		}

		$this->it('should pass if the _links element matches the given url', $this->compareUrls($data->_links->{$rel}->href, $href));

		return $data->_links->{$rel}->href;
	}

	/**
	 * Assert pagination links.
	 * 
	 * @param   integer  $page  Page number.
	 * 
	 * @return  void
	 */
	public function assertPagination($page = null)
	{
		$data = $this->getData();

		$first = $this->namespace . ':first';
		$last  = $this->namespace . ':last';
		$prev  = $this->namespace . ':previous';
		$next  = $this->namespace . ':next';

		if (is_null($page))
		{
			$this->it('should pass if page number is 1', $data->page == 1);
		}

		$this->it('should pass if the page number is less than or equal to the total number of pages', $data->page <= $data->totalPages);
		$this->it('should pass if the total pages is compatible with the total items count', $data->totalItems <= $data->pageLimit * $data->totalPages);

		// First page.
		if ($data->page == 1)
		{
			$this->it('should pass if there is no first page link on page 1', !isset($data->_links->$first));
			$this->it('should pass if there is no previous page link on page 1', !isset($data->_links->$prev));
		}

		// Intermediate page.
		if ($data->page > 1)
		{
			$this->it('should pass if there is a first page link', isset($data->_links->$first));
			$this->it('should pass if there is a previous page link', isset($data->_links->$prev));
			$this->it('should pass if there is a last page link', isset($data->_links->$last));
			$this->it('should pass if there is a next page link', isset($data->_links->$next));
		}

		if ($data->page > 1 && $data->page < $data->totalPages)
		{
			
		}

		// Last page.
		if ($data->page == $data->totalPages)
		{
			$this->it('should pass if there is no last page link on the last page', !isset($data->_links->$last));
			$this->it('should pass if there is no next page link on the last page', !isset($data->_links->$next));
		}
	}

	/**
	 * Assert self link.
	 * 
	 * @param   string  $url  Optional URL to test against.
	 * 
	 * @return  void
	 */
	public function assertSelf($url = '')
	{
		$this->assertLink('self', $this->url);

		if ($url != '')
		{
			$this->assertLink('self', $url);
		}
	}
}
