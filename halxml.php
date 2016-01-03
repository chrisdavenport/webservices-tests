<?php
/**
 * Generic test class for HAL + XML interactions.
 */
class WebserviceTestHalXml extends WebserviceTest
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
		return parent::get($url, ['Accept: application/hal+xml']);
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
		return parent::post($url, $postData, ['Accept: application/hal+xml']);
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
		return parent::delete($url, ['Accept: application/hal+xml']);
	}

	/**
	 * Get data from response.
	 * 
	 * @return  string
	 */
	public function getData()
	{
		return simplexml_load_string($this->data);
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

		$relFound = '';

		foreach ($data->children() as $link)
		{
			if (isset($link['rel']) && $link['rel'] == $rel)
			{
				$relFound = $link['href'];

				break;
			}
		}

		$this->it('should pass if there is a link element with rel=' . $rel, $relFound != '');

		if ($href != '')
		{
			$this->it('should pass if the link element matches the given url', $relFound == $href);
		}

		return $relFound;
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

		$this->it('should pass if there is at least one embedded resource element', isset($data->resource));

		if ($rel == '')
		{
			return;
		}

		$embedded = array();

		foreach ($data->children() as $element)
		{
			if ($element->getName() == 'resource' && $element['rel'] == $rel)
			{
				$embedded[] = $element;
			}
		}

		$this->it('should pass if at least one embedded resource has a rel called ' . $rel, count($embedded));
		$this->it('should pass if there are the expected number of embedded resources with rel=' . $rel, count($embedded) == $data->totalItems);

		foreach ($embedded as $k => $item)
		{
			foreach ($properties as $property)
			{
				$this->it('should pass if the ' . $property . ' entry is present in entry ' . $k, isset($item->$property));
			}
		}
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
		$last = $this->namespace . ':last';
		$prev = $this->namespace . ':previous';
		$next = $this->namespace . ':next';

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
	 * @return  void
	 */
	public function assertSelf()
	{
		$data = $this->getData();

		$this->it('should pass if the resource element includes a rel attribute', isset($data['rel']));
		$this->it('should pass if the rel attribute has the value "self"', $data['rel'] == 'self');
		$this->it('should pass if the resource element includes an href attribute', isset($data['href']));
		$this->it('should pass if the href attribute matches the request url', $this->compareUrls((string) $data['href'], $this->url));
	}
}
