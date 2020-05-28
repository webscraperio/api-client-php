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

### Initialize client
```php
$client = new Client([
    'token' => 'paste api token here',
]);
```

**Handle API request limit**

(Default) If API request limit is reached and 429 response code is returned then client will be automatically put to sleep and will make request again when API request limits are restored.

This Behavior can be disabled and throw exception instead of sleep.
```php
$client = new Client([
    'token' => 'paste api token here',
    'use_backoff_sleep' => false,
]);
```

**Create Sitemap**
```php
$sitemapJSON = '
{
  "_id": "webscraper-io-landing",
  "startUrl": [
    "http://webscraper.io/"
  ],
  "selectors": [
    {
      "parentSelectors": [
        "_root"
      ],
      "type": "SelectorText",
      "multiple": false,
      "id": "title",
      "selector": "h1",
      "regex": "",
      "delay": ""
    }
  ]
}
';

$sitemap = json_decode($sitemapJSON, true);
$response = $client->createSitemap($sitemap);
```
Output:
```php
['id' => 123]
```

**Get Sitemap**

```php
$sitemap = $client->getSitemap($sitemapId);
```

Output:
```php
[
    'id' => 123,
    'name' => 'webscraper-io-landing',
    'sitemap' => '{
        "_id": "webscraper-io-landing",
        "startUrl": [
          "http://webscraper.io/"
        ],
        "selectors": [
          {
            "parentSelectors": [
              "_root"
            ],
            "type": "SelectorText",
            "multiple": false,
            "id": "title",
            "selector": "h1",
            "regex": "",
            "delay": ""
          }
        ]
    }', // note sitemap won't be pretty printed
]
```

**Get Sitemaps**

```php
$sitemaps = $client->getSitemaps();
```

Output (Iterator):
```php
[
    [
        'id' => 123,
        'name' => 'webscraper-io-landing',
    ],
    [
        'id' => 124,
        'name' => 'webscraper-io-landing2',
    ],
]
```

```php
// iterate through all sitemaps
$sitemaps = $client->getSitemaps();
foreach($sitemaps as $sitemap) {
    var_dump($sitemap);
}

// iterate throuh all sitemaps while manually handling pagination
$iterator = $client->getSitemaps();
$page = 1;
do {
    $sitemaps = $iterator->getPageData($page);
    foreach($sitemaps as $sitemap) {
        var_dump($sitemap);
    }
    $page++;
} while($page <= $iterator->getLastPage());
```


**Delete Sitemap**

```php
$client->deleteSitemap(123);
```

Output:
```php
"ok"
```

**Create Scraping Job**

```php
$client->createScrapingJob([
    'sitemap_id' => 123,
    'driver' => 'fast', // 'fast' or 'fulljs'
    'page_load_delay' => 2000,
    'request_interval' => 2000,
]);
```

Output:
```php
['id' => 500]
```

**Get Scraping Job**

Note. You can also receive a push notification that a scraping job has 
finished. Pinging the API to await when the scraping job has finished isn't 
the correct way to do it.

```php
$client->getScrapingJob(500);
```

Output:
```php
[
    'id' => 500,
    'sitemap_name' => 'webscraper-io-landing',
    'status' => 'scheduling',
    'sitemap_id' => 123,
    'test_run' => 0,
    'jobs_scheduled' => 0,
    'jobs_executed' => 0,
    'jobs_failed' => 0,
    'jobs_empty' => 0,
    'stored_record_count' => 0,
    'request_interval' => 2000,
    'page_load_delay' => 2000,
    'driver' => 'fast',
    'scheduled' => 0, // scraping job was started by scheduler
    'time_created' => '1493370624', // unix timestamp
]
```

**Get Scraping Jobs**

```php
$client->getScrapingJobs($sitemapId = null);
```

Output (Iterator):
```php
[
    [
        'id' => 500,
        'sitemap_name' => 'webscraper-io-landing',
        ...
    ],
    [
        'id' => 501,
        'sitemap_name' => 'webscraper-io-landing',
        ...
    ],
]
```

```php
// iterate through all scraping jobs
$scrapingJobs = $client->getScrapingJobs();
foreach($scrapingJobs as $scrapingJob) {
    var_dump($scrapingJob);
}

// iterate through all scraping jobs while manually handling pagination
$iterator = $client->getScrapingJobs();
$page = 1;
do {
    $scrapingJobs = $iterator->getPageData($page);
    foreach($scrapingJobs as $scrapingJob) {
        var_dump($scrapingJob);
    }
    $page++;
} while($page <= $iterator->getLastPage());
```

**Download Scraping Job JSON**

Note! A good practice would be to move the download/import task to a queue job.
Here is a good example of a queue system - https://laravel.com/docs/5.8/queues

```php
require "../vendor/autoload.php";

use WebScraper\ApiClient\Client;
use WebScraper\ApiClient\Reader\JsonReader;

$apiToken = "API token here";
$scrapingJobId = 500; // scraping job id here

// initialize API client
$client = new Client([
	'token' => $apiToken,
]);

// download file locally
$outputFile = "/tmp/scrapingjob{$scrapingJobId}.json";
$client->downloadScrapingJobJSON($scrapingJobId, $outputFile);

// read data from file with built in JSON reader
$reader = new JsonReader($outputFile);
$rows = $reader->fetchRows();
foreach($rows as $row) {
	echo "ROW: ".json_encode($row)."\n";
}

// remove temporary file
unlink($outputFile);

// delete scraping job because you probably don't need it
$client->deleteScrapingJob($scrapingJobId);
```

**Delete Scraping Job**

```php
$client->deleteScrapingJob(500);
```

Output:
```php
"ok"
```

**Get Account information**

```php
$client->getAccountInfo();
```

Output:
```php
[
	'email' => 'user@example.com',
	'firstname' => 'John',
	'lastname' => 'Deere',
	'page_credits' => 500,
]
```

## Changelog

### v0.2.0

 * getScrapingJobs() and getSitemaps() now return iterators
 * getScrapingJobs($sitemapId) can filter by sitemap