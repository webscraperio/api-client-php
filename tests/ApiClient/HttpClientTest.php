<?php

namespace Tests\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use ReflectionObject;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;
use WebScraper\ApiClient\HttpClient;
use WebScraper\ApiClient\WebScraperApiException;

/**
 * @coversDefaultClass \WebScraper\ApiClient\HttpClient
 */
class HttpClientTest extends TestCase {

	/**
	 * @covers ::request()
	 * @covers ::requestRaw()
	 * @covers ::backoffRequest()
	 */
	public function testRequest() {

		$responses = [
			new Response(200, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 199], json_encode(['success' => true, 'message' => '200 Success']))
		];

		$httpClient = $this->setMockHttpClient($responses);
		$response = $httpClient->request('GET', '/', []);

		$this->assertEquals(
			['success' => true, 'message' => '200 Success'],
			$response
		);
	}

	/**
	 * @covers ::backoffRequest()
	 */
	public function testBackoffSleepWith429() {

		$responses = [
			new Response(429, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 0, 'Retry-After' => 1], '429 Error'),
			new Response(200, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 199], json_encode(['success' => true, 'message' => '200 Success'])),
		];

		$httpClient = $this->setMockHttpClient($responses);

		$response = $httpClient->request('GET', '/', []);

		$this->assertEquals(
			['success' => true, 'message' => '200 Success'],
			$response
		);
	}

	/**
	 * @covers ::backoffRequest()
	 */
	public function testBackoffSleepFor500After429() {

		$responses = [
			new Response(429, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 0, 'Retry-After' => 1], '429 Error'),
			new Response(500, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 199], '500 Error'),
		];

		$httpClient = $this->setMockHttpClient($responses);

		try {
			$httpClient->request('GET', '/', []);
			$this->fail('Error not thrown');
		} catch(WebScraperApiException $e) {
			$this->assertEquals(500, $e->getCode());
		}
	}

	/**
	 * @covers ::backoffRequest()
	 */
	public function testBackoffSleepLimit() {

		$responses = [
			new Response(429, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 0, 'Retry-After' => 1], '429 Error First Time'),
			new Response(429, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 0, 'Retry-After' => 1], '429 Error Second Time'),
			new Response(429, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 0, 'Retry-After' => 1], '429 Error Third Time'),
			new Response(200, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 199], json_encode(['success' => true, 'message' => '200 Success'])),
		];

		$httpClient = $this->setMockHttpClient($responses);

		try {
			$httpClient->request('GET', '/', []);
			$this->fail('Error not thrown');
		} catch(WebScraperApiException $e) {
			$this->assertEquals(429, $e->getCode());
		}
	}

	/**
	 * @covers ::backoffRequest()
	 */
	public function testBackoffSleepOff() {

		$responses = [
			new Response(429, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 0, 'Retry-After' => 1], '429 Error'),
			new Response(200, ['X-RateLimit-Limit' => 200, 'X-RateLimit-Remaining' => 199], json_encode(['success' => true, 'message' => '200 Success'])),
		];

		$httpClient = $this->setMockHttpClient($responses, false);

		try {
			$httpClient->request('GET', '/', []);
			$this->fail('Error not thrown');
		} catch(WebScraperApiException $e) {
			$this->assertEquals(429, $e->getCode());
		}
	}

	/**
	 * @param array $options
	 * @param Response[] $mockResponses
	 * @return HttpClient
	 * @throws \ReflectionException
	 */
	private function setMockHttpClient(array $mockResponses, $backoffSleep = true) {

		$httpClient = new HttpClient([
			'token' => null,
			'use_backoff_sleep' => $backoffSleep,
		]);

		$mockHandler = new MockHandler($mockResponses);

		$handlerStack = HandlerStack::create($mockHandler);
		$this->overrideObjectProperty($httpClient, 'guzzle', new GuzzleClient(['handler' => $handlerStack]));

		return $httpClient;
	}

	/**
	 * @param $object
	 * @param string $propertyName
	 * @param $value
	 * @throws \ReflectionException
	 */
	private function overrideObjectProperty($object, $propertyName, $value) {

		$reflector = new ReflectionObject($object);
		$property = $reflector->getProperty($propertyName);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	/**
	 * @covers ::requestRaw()
	 * @covers ::downloadRequest()
	 */
	public function testDownloadRequest() {

		$responses = [
			new Response(200, [], 'row')
		];

		$httpClient = $this->setMockHttpClient($responses);
		$httpClient->downloadRequest('/download/csv', 'tests/downloaded-file.csv');

		$this->assertFileExists('tests/downloaded-file.csv');
		$this->assertEquals('row', file_get_contents('tests/downloaded-file.csv'));

		unlink('tests/downloaded-file.csv');
	}
}
