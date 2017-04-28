<?php

namespace Tests\WebScraper\Api;

use Tests\TestCase;
use WebScraper\ApiClient\Client;
use WebScraper\ApiClient\WebScraperApiException;

class ClientTestCase extends TestCase {

	/**
	 * @var Client
	 */
	private $client;

	private $sitemap;

	public function setUp() {

		// sitemap for testing
		$dir = realpath(dirname(__FILE__));
		$sitemapStr = file_get_contents($dir.'../../test-sitemap.json');
		$sitemap = json_decode($sitemapStr, true);
		$sitemap['_id'] = str_replace('.', '_', uniqid("test_", true));
		$this->sitemap = $sitemap;

		$this->client = new Client([
			'token' => getenv('WEBSCRAPER_API_TOKEN'),
			'base_uri' => getenv('WEBSCRAPER_API_BASE_URI'),
		]);
	}

	public function createSitemap() {

		$client = $this->client;

		$sitemap = $this->sitemap;
		$response = $client->createSitemap($sitemap);
		return $response;
	}

	public function createScrapingjob() {

		$client = $this->client;

		$sitemapId = $this->createSitemap()['id'];
		$response = $client->createScrapingJob([
			'sitemap_id' => $sitemapId,
			'driver' => 'fast',
			'page_load_delay' => 2000,
			'request_interval' => 2000,
		]);

		$response['sitemap_id'] = $sitemapId;

		return $response;
	}

	public function testCreateSitemap() {

		$client = $this->client;

		$sitemap = $this->sitemap;
		$response = $client->createSitemap($sitemap);

		$this->assertTrue(isset($response['id']));
	}

	public function testGetSitemap() {

		$client = $this->client;

		$initialSitemap = $this->sitemap;
		$sitemapId = $this->createSitemap()['id'];
		$sitemap = $client->getSitemap($sitemapId);

		$this->assertEquals([
			'id' => $sitemapId,
			'name' => $initialSitemap['_id'],
			'sitemap' => json_encode($initialSitemap, JSON_UNESCAPED_SLASHES),
		], $sitemap);
	}

	public function testGetSitemaps() {

		$client = $this->client;

		// first create sitemap
		$this->createSitemap();

		$sitemaps = $client->getSitemaps();
		$this->assertGreaterThan(0, count($sitemaps));
	}

	public function testDeleteSitemap() {

		$client = $this->client;

		// first create sitemap
		$sitemap = $this->createSitemap();

		// delete sitemap
		$response = $client->deleteSitemap($sitemap['id']);

		$this->assertEquals("ok", $response);

		// check sitemap deleted
		try {
			$client->getSitemap($sitemap['id']);
			$this->fail("error not thrown");
		}
		catch(WebScraperApiException $e) {
			$this->assertEquals(404, $e->getCode());
		}
	}

	public function testCreateScrapingJob() {

		$client = $this->client;

		$sitemapId = $this->createSitemap()['id'];
		$response = $client->createScrapingJob([
			'sitemap_id' => $sitemapId,
			'driver' => 'fast',
			'page_load_delay' => 2000,
			'request_interval' => 2000,
		]);

		$this->assertTrue(isset($response['id']));
	}

	public function testGetScrapingJob() {

		$client = $this->client;

		$initialSitemap = $this->sitemap;
		$scrapingJobCreated = $this->createScrapingjob();
		$scrapingJob = $client->getScrapingJob($scrapingJobCreated['id']);

		unset($scrapingJob['time_created']);

		$this->assertEquals([
			'id' => $scrapingJobCreated['id'],
			'sitemap_name' => $initialSitemap['_id'],
			'status' => 'scheduling',
			'sitemap_id' => $scrapingJobCreated['sitemap_id'],
			'test_run' => 0,
			'jobs_scheduled' => 0,
			'jobs_executed' => 0,
			'jobs_failed' => 0,
			'jobs_empty' => 0,
			'stored_record_count' => 0,
			'request_interval' => 2000,
			'page_load_delay' => 2000,
			'driver' => 'fast',
			'scheduled' => 0,
		], $scrapingJob);
	}

	public function testGetScrapingJobs() {

		$client = $this->client;

		// first create sitemap
		$this->createScrapingjob();

		$scrapingJobs = $client->getScrapingJobs();
		$this->assertGreaterThan(0, count($scrapingJobs));
	}

	public function testDeleteScrapingJob() {

		$client = $this->client;

		// first create scraping job
		$scrapingJob = $this->createScrapingjob();

		// delete scraping job
		$response = $client->deleteScrapingJob($scrapingJob['id']);

		$this->assertEquals("ok", $response);

		// check sitemap deleted
		try {
			$client->getScrapingJob($scrapingJob['id']);
			$this->fail("error not thrown");
		}
		catch(WebScraperApiException $e) {
			$this->assertEquals(404, $e->getCode());
		}
	}

	public function testDownloadScrapingJobCSV() {

		$client = $this->client;

		// first create scraping job
		$scrapingJob = $this->createScrapingjob();

		$outputFile = tempnam('/tmp', "webscraper_io_client_test_");
		unlink($outputFile);
		$client->downloadScrapingJobCSV($scrapingJob['id'], $outputFile);
		$this->assertFileExists($outputFile);
	}
}