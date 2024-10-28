<?php

namespace WebScraper\ApiClient;


class PaginationIterator implements \Iterator {

	/**
	 * Current pagination page
	 * @var integer
	 */
	private $page;

	/**
	 * Current position in local array
	 * @var integer
	 */
	private $position;

	/**
	 * @var HttpClient
	 */
	private $httpClient;

	/**
	 * @var string
	 */
	private $uriPath;

	/**
	 * Last pagination page
	 * @var integer
	 */
	private $lastPage;

	/**
	 * Total records to be fetched
	 * @var integer
	 */
	private $total;

	/**
	 * Record amount per pagination page
	 * @var integer
	 */
	private $perPage;

	/**
	 * Local data of the current pagination page
	 * @var array
	 */
	private $array;

	/**
	 * Guzzle http options
	 * @var array
	 */
	private $httpClientOptions;

	public function __construct(HttpClient $httpClient, $uriPath, $httpClientOptions = []) {

		$this->httpClient = $httpClient;
		$this->uriPath = $uriPath;
		$this->httpClientOptions = $httpClientOptions;
		$this->position = 0;
		$this->page = null;
	}

	#[\ReturnTypeWillChange]
	public function rewind() {

		$this->position = 0;
		$this->getPageData(1);
	}

	/**
	 * Load data from api
	 * @return array
	 */
	public function getPageData($page) {

		// do not load the same page twice
		if($this->page === $page) {
			return $this->array;
		}

		$this->page = $page;

		$options = $this->httpClientOptions;
		$options['query']['page'] = $page;

		$response = $this->httpClient->request("GET", $this->uriPath, $options);
		$this->lastPage = $response['last_page'];
		$this->total = $response['total'];
		$this->perPage = $response['per_page'];
		$this->array = $response['data'];

		return $this->array;
	}

	#[\ReturnTypeWillChange]
	public function current() {

		return $this->array[$this->position];
	}

	#[\ReturnTypeWillChange]
	public function key() {

		return $this->position + ($this->perPage * ($this->page - 1));
	}

	#[\ReturnTypeWillChange]
	public function next() {

		++$this->position;

		// load more data from server when there isn't anything locally
		if (!isset($this->array[$this->position])) {
			if ($this->page < $this->lastPage) {
				$this->position = 0;
				$this->getPageData($this->page + 1);
			}
		}
	}

	#[\ReturnTypeWillChange]
	public function valid() {

		return isset($this->array[$this->position]);
	}

	public function getLastPage() {

		return $this->lastPage;
	}
}
