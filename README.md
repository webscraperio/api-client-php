# WebScraper.io PHP API client

API client for cloud.webscraper.io. The cloud based scraper is a managed 
scraper for the free Web Scraper Chrome extension. 
Visit https://cloud.webscraper.io/api to acquire API key.

## Installation

Install the API client with composer.
```bash
composer require webscraperio/api-client-php
```

You might also need a CSV parser library. Visit http://csv.thephpleague.com/ 
for more information.
```bash
composer require league/csv
```

## Usage

Web Scraper Cloud API documentation can be found on [webscraper.io]

## Changelog

### v1.2.0
 
 * added enableSitemapScheduler($sitemapId, $schedulerConfig) method
 * added disableSitemapScheduler($sitemapId) method
 * added getScrapingJobDataQuality($scrapingJobId) method

### v1.1.0

* Drop support for "End of life" PHP versions 5.6, 7.0, 7.2. Minimum required version is PHP 7.3

### v0.3.0
 
 * added updateSitemap($sitemapId) method
 * added backoff mechanism
 * createScrapingJob($scrapingJobConfig) now has `start_urls` and `custom_id` 
 optional fields
 * `custom_id` field is now returned in getScrapingJob($scrapingJobId),  
 getScrapingJobs() and createScrapingJob($scrapingJobConfig) responses.

### v0.2.0

 * getScrapingJobs() and getSitemaps() now return iterators
 * getScrapingJobs($sitemapId) can filter by sitemap


[webscraper.io]: https://www.webscraper.io/documentation/api
