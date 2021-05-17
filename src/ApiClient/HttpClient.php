<?php

namespace WebScraper\ApiClient;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client as GuzzleClient;

class HttpClient {

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var bool
	 */
	private $useBackoffSleep;

	/**
	 * @var \GuzzleHttp\Client
	 */
	private $guzzle;

	public function __construct($options) {

		$this->token = $options['token'];
		$this->useBackoffSleep = isset($options['use_backoff_sleep']) ? $options['use_backoff_sleep'] : true;

		$baseUri =  'https://api.webscraper.io/api/v1/';
		if(isset($options['base_uri']) && $options['base_uri']) {
			$baseUri = $options['base_uri'];
		}

		$this->guzzle = new GuzzleClient([
			'base_uri' => $baseUri,
			'timeout' => 5.0,
			'headers' => [
				'Accept' => 'application/json, text/javascript, */*',
				'User-Agent' => 'WebScraper.io PHP SDK v1.1.0',
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

	public function put($uri = null, array $options = []) {

		$response =  $this->request("PUT", $uri, $options);
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

		$response = $this->backoffRequest($method, $uri, $options);

		$statusCode = $response->getStatusCode();
		if($statusCode !== 200) {
			throw new WebScraperApiException('Unexpected status code $statusCode', $statusCode);
		}

		return $response;
	}

	/**
	 * Make request to passed endpoint. If backoff sleep is enabled and request failed because of Too Many Requests
	 * it will sleep till api rate limit is updated and then make request again.
	 *
	 * @param string $method
	 * @param mixed $uri
	 * @param array $options
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 * @throws WebScraperApiException
	 */
	private function backoffRequest($method, $uri, array $options) {

		$allowedAttempts = $this->useBackoffSleep ? 3 : 1;
		$attempt = 1;

		do {
			try {
				return $this->guzzle->request($method, $uri, $options);
			} catch (RequestException $e) {
				$statusCode = $e->getCode();

				// Last attempt or Other error
				if ($attempt === $allowedAttempts || $statusCode !== 429) {
					throw new WebScraperApiException('Unexpected status code {$statusCode}. Message: ' . $e->getMessage(), $statusCode);
				}

				$retry = $e->getResponse()->getHeader('Retry-After');
				if ($retry !== null) {
					// Add 1 sec offset to be sure rate limits are reset
					sleep(intval($retry[0]) + 1);
				}

				$attempt++;
			}
		} while ($attempt <= $allowedAttempts);
	}
}
