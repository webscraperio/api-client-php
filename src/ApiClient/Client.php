<?php

namespace WebScraper\ApiClient;

use Psr\Http\Message\ResponseInterface;

class Client {

	/**
	 * @var HttpClient
	 */
	private $httpClient;

	public function __construct($options) {

		$this->httpClient = new HttpClient($options);
	}

	/**
	 * Create sitemap
	 *
	 * @param array $sitemap
	 * @return mixed
	 */
	public function createSitemap(array $sitemap) {

		$response = $this->httpClient->post('sitemap', [
			'json' => $sitemap,
		]);
		return $response;
	}

	/**
	 * Get sitemap
	 *
	 * @param $sitemapId
	 * @return mixed
	 */
	public function getSitemap($sitemapId) {

		$response = $this->httpClient->get("sitemap/{$sitemapId}");
		return $response;
	}

	/**
	 * Get sitemaps
	 *
	 * @return PaginationIterator
	 */
	public function getSitemaps() {

		$iterator = new PaginationIterator($this->httpClient, 'sitemaps');
		return $iterator;
	}

	public function updateSitemap($sitemapId, array $sitemap) {

		$response = $this->httpClient->put("sitemap/{$sitemapId}", [
			'json' => $sitemap,
		]);
		return $response;
	}

	/**
	 * Delete sitemap
	 *
	 * @param $sitemapId
	 * @return mixed
	 */
	public function deleteSitemap($sitemapId) {

		$response = $this->httpClient->delete("sitemap/{$sitemapId}");
		return $response;
	}

	/**
	 * Create scraping job
	 *
	 * @param $scrapingJobConfig
	 * @return mixed
	 */
	public function createScrapingJob($scrapingJobConfig) {

		$response = $this->httpClient->post('scraping-job', [
			'json' => $scrapingJobConfig,
		]);
		return $response;
	}

	/**
	 * get scraping jobs
	 *
	 * @param null $sitemapId
	 * @return PaginationIterator
	 */
	public function getScrapingJobs($sitemapId = null) {

		$options = [];
		if($sitemapId) {
			$options['query']['sitemap_id'] = $sitemapId;
		}

		$iterator = new PaginationIterator($this->httpClient, 'scraping-jobs', $options);
		return $iterator;
	}

	/**
	 * Get scraping job
	 *
	 * @param $scrapingJobId
	 * @return  []
	 */
	public function getScrapingJob($scrapingJobId) {

		$response = $this->httpClient->get("scraping-job/{$scrapingJobId}");
		return $response;
	}

	/**
	 * Delete scraping job
	 *
	 * @param $scrapingJobId
	 * @return mixed
	 */
	public function deleteScrapingJob($scrapingJobId) {

		$response = $this->httpClient->delete("scraping-job/{$scrapingJobId}");
		return $response;
	}
	
	public function downloadScrapingJobCSV(int $scrapingJobId, string $outputFile, bool $raw = false): ResponseInterface {

		return $this->httpClient->downloadRequest(
			$this->downloadScrapingJobUri($scrapingJobId, 'csv', $raw),
			$outputFile
		);
	}

	public function downloadScrapingJobJSON(int $scrapingJobId, string $outputFile, bool $raw = false): ResponseInterface {

		return $this->httpClient->downloadRequest(
			$this->downloadScrapingJobUri($scrapingJobId, 'json', $raw),
			$outputFile
		);
	}

	public function downloadScrapingJobXLSX(int $scrapingJobId, string $outputFile,  bool $raw = false): ResponseInterface {

		return $this->httpClient->downloadRequest(
			$this->downloadScrapingJobUri($scrapingJobId, 'xlsx', $raw),
			$outputFile
		);
	}

	/**
	 * Get Account information. Main purpose of this is to retrieve page
	 * credit amount
	 *
	 * @return mixed
	 */
	public function getAccountInfo() {

		$response = $this->httpClient->get('account');
		return $response;
	}

	/**
	 * Get problematic urls
	 *
	 * @return PaginationIterator
	 */
	public function getProblematicUrls($scrapingJobId) {

		$iterator = new PaginationIterator($this->httpClient, "scraping-job/{$scrapingJobId}/problematic-urls");
		return $iterator;
	}

	/**
	 * Get scraping job
	 *
	 * @return  mixed
	 */
	public function getScrapingJobDataQuality(int $scrapingJobId) {

		$response = $this->httpClient->get("scraping-job/{$scrapingJobId}/data-quality");
		return $response;
	}

	/**
	 * Enable sitemap scheduler
	 *
	 * @return mixed
	 */
	public function enableSitemapScheduler(int $sitemapId, array $schedulerConfig) {

		$response = $this->httpClient->post("sitemap/{$sitemapId}/enable-scheduler", [
			'json' => $schedulerConfig,
		]);
		return $response;
	}

	/**
	 * Disable sitemap scheduler
	 *
	 * @return mixed
	 */
	public function disableSitemapScheduler(int $sitemapId) {

		$response = $this->httpClient->post("sitemap/{$sitemapId}/disable-scheduler");
		return $response;
	}

	/**
	 * Get sitemap scheduler
	 *
	 * @return mixed
	 */
	public function getSitemapScheduler(int $sitemapId) {

		$response = $this->httpClient->get("sitemap/{$sitemapId}/scheduler");
		return $response;
	}

	private function downloadScrapingJobUri(int $scrapingJobId, string $format, bool $raw): string {

		$query = $raw ? '?raw=true' : '';
		return "scraping-job/{$scrapingJobId}/{$format}{$query}";
	}
}
