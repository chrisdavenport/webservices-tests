<?php
/**
 * Abstract generic test class.
 */
abstract class WebserviceTest
{
	public $url = '';
	public $namespace = '';
	public $status = 0;
	public $headers = array();
	public $data = '';
	public $postData = array();

	/**
	 * Constructor.
	 * 
	 * @param   string  $namespace  Optional namespace
	 */
	public function __construct($namespace = '')
	{
		$this->namespace = $namespace;
	}

	/**
	 * Do an HTTP GET for a URL.
	 * 
	 * @param   string  $url      URL to GET.
	 * @param   string  $headers  Optional request headers.
	 * 
	 * @return  this object for chaining.
	 */
	public function get($url, $headers = ['Accept: application/json'])
	{
		$this->url = $url;
		$this->headers = array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$response = curl_exec($ch);

		// Split response into headers + body.
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->headers = $this->extractHeaders(substr($response, 0, $headerSize));
		$this->data = substr($response, $headerSize);

		$meta = curl_getinfo($ch);
		$this->status = (int) $meta['http_code'];
		curl_close($ch);

		return $this;
	}

	/**
	 * Do an HTTP POST to a URL.
	 * 
	 * @param   string  $url      URL to POST to.
	 * @param   array   $data     Data to POST.
	 * @param   string  $headers  Optional request headers.
	 * 
	 * @return  this object for chaining.
	 */
	public function post($url, array $postData = array(), $headers = ['Accept: application/json'])
	{
		$this->url = $url;
		$this->postData = $postData;
		$this->headers = array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$response = curl_exec($ch);

		// Split response into headers + body.
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->headers = $this->extractHeaders(substr($response, 0, $headerSize));
		$this->data = substr($response, $headerSize);

		$meta = curl_getinfo($ch);
		$this->status = (int) $meta['http_code'];
		curl_close($ch);

		return $this;
	}

	/**
	 * Do an HTTP DELETE to a URL.
	 * 
	 * @param   string  $url        URL to POST to.
	 * @param   string  $headers  Optional request headers.
	 * 
	 * @return  this object for chaining.
	 */
	public function delete($url, $headers = ['Accept: application/json'])
	{
		$this->url = $url;
		$this->headers = array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$response = curl_exec($ch);

		// Split response into headers + body.
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->headers = $this->extractHeaders(substr($response, 0, $headerSize));
		$this->data = substr($response, $headerSize);

		$meta = curl_getinfo($ch);
		$this->status = (int) $meta['http_code'];
		curl_close($ch);

		return $this;
	}

	/**
	 * Extract HTTP headers.
	 * 
	 * Note that this will not handle repeated headers correctly.
	 * 
	 * @param   string  $headers  String containing all headers concatenated.
	 * 
	 * @return  array containing each header as key-value pair.
	 */
	private function extractHeaders($headers)
	{
		$headerLines = [];

		$lines = explode("\r\n", $headers);

		foreach ($lines as $line)
		{
			// Skip empty lines.
			// This sometimes happens if the server initially responds with 100 Continue.
			if (trim($line) == '')
			{
				continue;
			}

			// Handle the status line separately because it has not separator.
			if (strpos($line, ':') === false)
			{
				$headerLines['Status'] = trim($line);

				continue;
			}

			$parts = explode(':', $line);
			$headerLines[trim($parts[0])] = trim($parts[1]);
		}

		return $headerLines;
	}

	/**
	 * Get data from response.
	 * 
	 * This should be overridden in child classes to apply
	 * any decode function that might be appropriate.
	 * 
	 * @return  string
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * Compare URLs.
	 * 
	 * @param   string  $url1  First URL to compare.
	 * @param   string  $url2  Second URL to compare.
	 * 
	 * @return  boolean
	 */
	public function compareUrls($url1, $url2)
	{
		$left  = str_replace('&amp;', '&', trim($url1));
		$right = str_replace('&amp;', '&', trim($url2));

		return $left == $right;
	}

	/**
	 * Assertion.
	 * 
	 * @param   string  $m   Message to show.
	 * @param   boolean  $p  Boolean assertion.
	 * 
	 * @return  void
	 */
	public function it($m,$p)
	{
		echo "\033[3", $p ? '2m✔︎' : '1m✘' . register_shutdown_function(function() { die(1); } ), " It $m\033[0m\n";
	}

	/**
	 * Tests a thrown exception.
	 * 
	 * @param   string    $exp  Expected exception class name.
	 * @param   \Closure  $cb   Closure that is expected to throw an exception.
	 * 
	 * @return  boolean
	 */
	public function throws($exp, \Closure $cb)
	{
		try
		{
			$cb();
		}
		catch(\Exception $e)
		{
			return $e instanceof $exp;
		}

		return false;
	}

	/**
	 * Assert the value of an HTTP header.
	 * 
	 * @param   string  $header  Name of the header.
	 * @param   string  $value   Optional value to be tested against.
	 * 
	 * @return  void
	 */
	public function assertHeader($header, $value = '')
	{
		$this->it('should pass if an HTTP header called ' . $header . ' is present', isset($this->headers[$header]));

		if ($value != '')
		{
			$this->it('should pass if the HTTP header called ' . $header . ' has the correct value', $this->headers[$header] == $value);
		}
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
}
