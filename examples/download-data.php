<?php

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