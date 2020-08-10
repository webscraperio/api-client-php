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

### v0.2.0

 * getScrapingJobs() and getSitemaps() now return iterators
 * getScrapingJobs($sitemapId) can filter by sitemap


[webscraper.io]: https://www.webscraper.io/documentation/api
