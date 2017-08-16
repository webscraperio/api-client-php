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
$sitemap = $client->getSitemaps();
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
$client->getScrapingJobs();
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

**Download Scraping Job CSV**

Note! A good practice would be to move the download/import task to a queue job.
Here is a good example of a queue system - https://laravel.com/docs/5.4/queues

The example uses League/CSV package. Install it with composer:
```bash
composer require league/csv
```

```php
$outputFile = "/tmp/scrapingjob500.csv";
$client->downloadScrapingJobCSV($scrapingJob['id'], $outputFile);

// import into database
use League\Csv\Reader;

// read data from csv file
$records = Reader::createFromPath($outputFile)->fetchAssoc();

// Note. 
// A good practice would be to do a batch insert when inserting data into database
foreach($records as $record) {
    // ...
}

// remove temporary file
unlink($outputFile);

// delete scraping job because you probably don't need it
$client->deleteScrapingJob(500);
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

