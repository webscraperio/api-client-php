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

	public function __construct(HttpClient $httpClient, $uriPath) {

		$this->httpClient = $httpClient;
		$this->uriPath = $uriPath;
		$this->position = 0;
		$this->page = 1;
	}

	public function rewind() {

		$this->position = 0;
		$this->getDataFromApi();
	}

	public function current() {

		return $this->array[$this->position];
	}

	public function key() {

		return $this->position + ($this->perPage * ($this->page-1));
	}

	public function next() {

		++$this->position;

		// load more data from server when there isn't anything locally
		if(!isset($this->array[$this->position])) {
			if($this->page < $this->lastPage) {
				$this->page++;
				$this->position = 0;
				$this->getDataFromApi();
			}
		}

	}

	public function valid() {

		return isset($this->array[$this->position]);
	}

	/**
	 * Load data from api
	 */
	private function getDataFromApi() {

		$options = [
			'query' => [
				'page' => $this->page,
			],
		];
		$response = $this->httpClient->request("GET", $this->uriPath, $options);
		$this->lastPage = $response['last_page'];
		$this->total = $response['total'];
		$this->perPage = $response['per_page'];
		$this->array = $response['data'];
	}
}
