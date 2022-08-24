<?php

namespace Tests\ApiClient;

use Tests\TestCase;
use WebScraper\ApiClient\Client;
use WebScraper\ApiClient\WebScraperApiException;

class ClientTest extends TestCase {

	/**
	 * @var Client
	 */
	private $client;

	private $sitemap;

	public function setUp(): void {

		$apiToken = getenv('WEBSCRAPER_API_TOKEN');

		if ($apiToken === false) {
			$this->markTestSkipped('Skip test');
		}

		$this->setSitemap('test-sitemap.json');

		$this->client = new Client([
			'token' => $apiToken,
			'base_uri' => getenv('WEBSCRAPER_API_BASE_URI'),
		]);
	}

	public function createSitemap() {

		$client = $this->client;

		$sitemap = $this->sitemap;
		$response = $client->createSitemap($sitemap);
		return $response;
	}

	public function createScrapingJob() {

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

	public function testUpdateSitemap() {

		$client = $this->client;

		$sitemap = $this->sitemap;
		$createResponse = $client->createSitemap($sitemap);
		$sitemapId = $createResponse['id'];

		// change sitemap attribute
		$updatedSitemap = $sitemap;
		$updatedSitemap['startUrl'] = ['https://changed-id-for-update.com'];

		$updateResponse = $client->updateSitemap($sitemapId, $updatedSitemap);
		$this->assertEquals("ok", $updateResponse);

		$remoteSitemap = $client->getSitemap($sitemapId);

		$this->assertEquals([
			'id' => $sitemapId,
			'name' => $sitemap['_id'],
			'sitemap' => json_encode($updatedSitemap, JSON_UNESCAPED_SLASHES),
		], $remoteSitemap);
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

		$sitemaps = iterator_to_array($client->getSitemaps());
		$this->assertGreaterThan(0, count($sitemaps));
	}

	public function testGetSitemapsManualPagination() {

		$client = $this->client;

		// first create sitemap
		$this->createSitemap();

		$totalRecordsFound = 0;
		$iterator = $client->getSitemaps();
		$page = 1;
		do {
			$records = $iterator->getPageData($page);
			$totalRecordsFound += count($records);
			$page++;
		} while($page <= $iterator->getLastPage());

		$recordCountFromIterator = count(iterator_to_array($client->getSitemaps()));

		$this->assertEquals($recordCountFromIterator, $totalRecordsFound);
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
		$scrapingJobCreated = $this->createScrapingJob();
		$scrapingJob = $client->getScrapingJob($scrapingJobCreated['id']);

		unset($scrapingJob['time_created']);

		$this->assertEquals([
			'id' => $scrapingJobCreated['id'],
			'sitemap_name' => $initialSitemap['_id'],
			'status' => 'waiting-to-be-scheduled',
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
			'custom_id' => null,
			'scraping_duration' => 0,
		], $scrapingJob);
	}

	public function testGetScrapingJobs() {

		$client = $this->client;

		// first create sitemap
		$this->createScrapingJob();

		$scrapingJobs = iterator_to_array($client->getScrapingJobs());
		$this->assertGreaterThan(0, count($scrapingJobs));
	}

	public function testGetScrapingJobsManualPagination() {

		$client = $this->client;

		// first create sitemap
		$this->createScrapingJob();

		$totalRecordsFound = 0;
		$iterator = $client->getScrapingJobs();
		$page = 1;
		do {
			$scrapingJobs = $iterator->getPageData($page);
			$totalRecordsFound += count($scrapingJobs);
			$page++;
		} while($page <= $iterator->getLastPage());

		$recordCountFromIterator = count(iterator_to_array($client->getScrapingJobs()));

		$this->assertEquals($recordCountFromIterator, $totalRecordsFound);
	}

	public function testGetScrapingJobsBySitemap() {

		$client = $this->client;

		$scrapingJob = $this->createScrapingJob();

		// create additional scraping job
		$this->sitemap['_id'] = $this->sitemap['_id'].'extra';
		$this->createScrapingJob();

		$scrapingJobs = iterator_to_array($client->getScrapingJobs($scrapingJob['sitemap_id']));
		$this->assertEquals(1, count($scrapingJobs));
	}

	public function testDeleteScrapingJob() {

		$client = $this->client;

		// first create scraping job
		$scrapingJob = $this->createScrapingJob();

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
		$scrapingJob = $this->createScrapingJob();

		$outputFile = tempnam('/tmp', "webscraper_io_client_test_");
		unlink($outputFile);
		$client->downloadScrapingJobCSV($scrapingJob['id'], $outputFile);
		$this->assertFileExists($outputFile);
		$this->assertEquals('text/plain', mime_content_type($outputFile));
	}

	public function testDownloadScrapingJobJSON() {

		$client = $this->client;

		// first create scraping job
		$scrapingJob = $this->createScrapingJob();

		$outputFile = tempnam('/tmp', "webscraper_io_client_test_");
		unlink($outputFile);
		$client->downloadScrapingJobJSON($scrapingJob['id'], $outputFile);
		$this->assertFileExists($outputFile);
		// Empty because scraping job not finished and data are empty
		$this->assertEquals('application/x-empty', mime_content_type($outputFile));
	}

	public function testDownloadScrapingJobXLSX() {

		$client = $this->client;

		// first create scraping job
		$scrapingJob = $this->createScrapingJob();

		$outputFile = tempnam('/tmp', "webscraper_io_client_test_");
		unlink($outputFile);
		$client->downloadScrapingJobXLSX($scrapingJob['id'], $outputFile);
		$this->assertFileExists($outputFile);
		$this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', mime_content_type($outputFile));
	}

	public function testGetAccountInformation() {

		$client = $this->client;
		$accountInfo = $client->getAccountInfo();

		$this->assertGreaterThan(0, $accountInfo['page_credits']);
	}

	public function testGetProblematicUrls() {
		$this->setSitemap('test-sitemap-with-empty.json');

		$client = $this->client;
		$scrapingJobId = $this->createScrapingJob()['id'];

		do {
			sleep(60);
			$runningScrapingJob = $client->getScrapingJob($scrapingJobId);
		} while(!in_array($runningScrapingJob['status'], ['finished', 'shelved']));

		$problematicUrlsIterator = $client->getProblematicUrls($scrapingJobId);

		$expectedData = [
			['type' => 'empty', 'url' => 'https://webscraper.io/test-sites/e-commerce/static/computers/laptops'],
		];

		$this->assertEquals($expectedData, iterator_to_array($problematicUrlsIterator));

		$this->assertEquals($expectedData, $problematicUrlsIterator->getPageData(1));

		$this->assertEquals([], $problematicUrlsIterator->getPageData(2));
	}

	public function testGetScrapingJobDataQuality() {

		$client = $this->client;
		$createdScrapingJob = $this->createScrapingJob();

		// Wait till scraping job finishes
		do {
			sleep(10);
			$status = $client->getScrapingJob($createdScrapingJob['id'])['status'];
		} while($status !== 'finished');

		$dataQuality = $client->getScrapingJobDataQuality($createdScrapingJob['id']);
		$this->assertEquals([
			'min_record_count' => [
				'got' => 1,
				'expected' => 1,
				'success' => true,
			],
			'max_failed_pages_percent' => [
				'got' => 0,
				'expected' => 5,
				'success' => true,
			],
			'max_empty_pages_percent' => [
				'got' => 0,
				'expected' => 5,
				'success' => true,
			],
			'min_column_records' => [
				'title' => [
					'got' => 100,
					'expected' => 95,
					'success' => true,
				],
			],
			'overall_data_quality_success' => true,
		], $dataQuality);
	}

	public function testEnableDisableSitemapScheduler() {

		$client = $this->client;
		$sitemap = $this->sitemap;
		$createResponse = $client->createSitemap($sitemap);
		$sitemapId = $createResponse['id'];
		$postData = [
			"cron_minute" => "*/10",
			"cron_hour" => "*",
			"cron_day" => "*",
			"cron_month" => "*",
			"cron_weekday" => "*",
			"request_interval" => 2100,
			"page_load_delay" => 2200,
			"cron_timezone" => "Europe/Riga",
			"driver" => "fast",
			"proxy" => 1
		];

		$enableResponse = $client->enableSitemapScheduler($sitemapId, $postData);

		$this->assertEquals("ok", $enableResponse);
		$this->assertEquals(array_merge(['scheduler_enabled' => true], $postData), $client->getSitemapScheduler($sitemapId));

		$disableResponse = $client->disableSitemapScheduler($sitemapId);

		$this->assertEquals("ok", $disableResponse);
		$this->assertEquals(array_merge(['scheduler_enabled' => false], $postData), $client->getSitemapScheduler($sitemapId));
	}

	private function setSitemap($jsonFile) {

		$dir = realpath(dirname(__FILE__));
		$sitemapStr = file_get_contents($dir . '../../' . $jsonFile);
		$sitemap = json_decode($sitemapStr, true);
		$sitemap['_id'] = str_replace('.', '_', uniqid("test_", true));
		$this->sitemap = $sitemap;
	}
}
