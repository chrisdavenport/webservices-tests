<?php
/**
 * Generic test class for HAL + XML interactions.
 */
class WebserviceTestHalXml
{
	public $url = '';
	public $namespace = '';
	public $status = 0;
	public $data = '';

	/**
	 * Do an HTTP GET for a URL.
	 * 
	 * @param   string  $url  URL to GET.
	 * @param   string  $namespace  Optional namespace
	 * 
	 * @return  this object for chaining.
	 */
	public function get($url, $namespace = '')
	{
		$this->url = $url;
		$this->namespace = $namespace;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/hal+xml'));

		$this->data = simplexml_load_string(curl_exec($ch));
		$meta = curl_getinfo($ch);
		$this->status = (int) $meta['http_code'];
		curl_close($ch);

		return $this;
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
		$relFound = '';

		foreach ($this->data->children() as $link)
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
		$this->it('should pass if there is at least one embedded resource element', isset($this->data->resource));

		if ($rel == '')
		{
			return;
		}

		$embedded = array();

		foreach ($this->data->children() as $element)
		{
			if ($element->getName() == 'resource' && $element['rel'] == $rel)
			{
				$embedded[] = $element;
			}
		}

		$this->it('should pass if at least one embedded resource has a rel called ' . $rel, count($embedded));
		$this->it('should pass if there are the expected number of embedded resources with rel=' . $rel, count($embedded) == $this->data->totalItems);

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
		$first = $this->namespace . ':first';
		$last = $this->namespace . ':last';
		$prev = $this->namespace . ':previous';
		$next = $this->namespace . ':next';

		if (is_null($page))
		{
			$this->it('should pass if page number is 1', $this->data->page == 1);
		}

		$this->it('should pass if the page number is less than or equal to the total number of pages', $this->data->page <= $this->data->totalPages);
		$this->it('should pass if the total pages is compatible with the total items count', $this->data->totalItems <= $this->data->pageLimit * $this->data->totalPages);

		// First page.
		if ($this->data->page == 1)
		{
			$this->it('should pass if there is no first page link on page 1', !isset($this->data->_links->$first));
			$this->it('should pass if there is no previous page link on page 1', !isset($this->data->_links->$prev));
		}

		// Intermediate page.
		if ($this->data->page > 1)
		{
			$this->it('should pass if there is a first page link', isset($this->data->_links->$first));
			$this->it('should pass if there is a previous page link', isset($this->data->_links->$prev));
			$this->it('should pass if there is a last page link', isset($this->data->_links->$last));
			$this->it('should pass if there is a next page link', isset($this->data->_links->$next));
		}

		if ($this->data->page > 1 && $this->data->page < $this->data->totalPages)
		{
			
		}

		// Last page.
		if ($this->data->page == $this->data->totalPages)
		{
			$this->it('should pass if there is no last page link on the last page', !isset($this->data->_links->$last));
			$this->it('should pass if there is no next page link on the last page', !isset($this->data->_links->$next));
		}
	}

	/**
	 * Assert self link.
	 * 
	 * @return  void
	 */
	public function assertSelf()
	{
		$this->it('should pass if the resource element includes a rel attribute', isset($this->data['rel']));
		$this->it('should pass if the rel attribute has the value "self"', $this->data['rel'] == 'self');
		$this->it('should pass if the resource element includes an href attribute', isset($this->data['href']));
		$this->it('should pass if the href attribute matches the request url', (string) $this->data['href'] == $this->url);
	}

	/**
	 * Status code assertion.
	 * 
	 * @param   integer  $status  Expected status code.
	 * 
	 * @return  void
	 */
	public function assertStatus($status)
	{
		$this->it('should pass if HTTP status code is ' . $status . ' (' . $this->status . ' returned)', $this->status == $status);
	}

	public function it($m,$p)
	{
		echo "\033[3", $p ? '2m✔︎' : '1m✘' . register_shutdown_function(function() { die(1); } ), " It $m\033[0m\n";
	}
}
