<?php

namespace WebScraper\ApiClient;

use GuzzleHttp\Exception\RequestException;

class HttpClient {

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var \GuzzleHttp\Client
	 */
	private $guzzle;

	public function __construct($options) {

		$this->token = $options['token'];
		$baseUri =  'https://api.webscraper.io/api/v1/';
		if(isset($options['base_uri']) && $options['base_uri']) {
			$baseUri = $options['base_uri'];
		}

		$this->guzzle = new \GuzzleHttp\Client([
			'base_uri' => $baseUri,
			'timeout' => 5.0,
			'headers' => [
				'Accept' => 'application/json, text/javascript, */*',
				'User-Agent' => 'WebScraper.io PHP SDK v1.0',
			],
		]);
	}

	/**
	 * Make an api request
	 *
	 * @param $method
	 * @param null $uri
	 * @param array $options
	 */
	public function request($method, $uri = null, array $options = []) {

		$response = $this->requestRaw($method, $uri, $options);

		$body = $response->getBody()->getContents();
		$bodyData = json_decode($body, true);

		if(!$bodyData['success']) {
			throw new WebScraperApiException("Unsuccessful api response. $body");
		}

		return $bodyData;
	}

	public function get($uri = null, array $options = []) {

		$response = $this->request("GET", $uri, $options);
		return $response['data'];
	}

	public function post($uri = null, array $options = []) {

		$response =  $this->request("POST", $uri, $options);
		return $response['data'];
	}

	public function delete($uri, $options = []) {

		$response =  $this->request("DELETE", $uri, $options);
		return $response['data'];
	}

	/**
	 * Make a raw request
	 *
	 * @param $method
	 * @param null $uri
	 * @param array $options
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 * @throws WebScraperApiException
	 */
	public function requestRaw($method, $uri = null, array $options = []) {

		if(!isset($options['query'])) {
			$options['query'] = [];
		}
		$options['query']['api_token'] = $this->token;

		try {
			$response = $this->guzzle->request($method, $uri, $options);
		}
		catch(RequestException $e) {
			$statusCode = $e->getCode();
			throw new WebScraperApiException("Unexpected status code {$statusCode}. Message: ".$e->getMessage(), $statusCode);
		}

		$statusCode = $response->getStatusCode();
		if($statusCode !== 200) {
			throw new WebScraperApiException("Unexpected status code $statusCode", $statusCode);
		}

		return $response;
	}
}