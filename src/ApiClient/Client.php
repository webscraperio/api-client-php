<?php

namespace WebScraper\ApiClient;

class Client {

	/**
	 * @var HttpClient
	 */
	private $httpClient;

	public function __construct($options) {

		$this->token = $options['token'];
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
	 * @return mixed
	 */
	public function getSitemaps() {

		$response = $this->httpClient->get('sitemaps');
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
	 * @return []
	 */
	public function getScrapingJobs() {

		$response = $this->httpClient->get('scraping-jobs');
		return $response;
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

	/**
	 * Download scraping jobs data in a file
	 *
	 * @param $scrapingJobId
	 * @param $outputFile
	 */
	public function downloadScrapingJobCSV($scrapingJobId, $outputFile) {

		$this->httpClient->requestRaw('GET', "scraping-job/{$scrapingJobId}/csv", [
			'headers'        => ['Accept-Encoding' => 'gzip'],
			'timeout' => 60.0,
			'save_to' => $outputFile,
		]);
	}
}