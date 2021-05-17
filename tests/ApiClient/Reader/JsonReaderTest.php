<?php


namespace Tests\ApiClient\Reader;

use Tests\TestCase;
use WebScraper\ApiClient\Reader\JsonReader;

/**
 * @coversDefaultClass \WebScraper\ApiClient\Reader\JsonReader
 */
class JsonReaderTest extends TestCase {

	public function fetchRowsProvider() {

		return [
			[
				[
					['a' => 'a'],
				]
				, false
			],
			[
				[
					['a' => 'a'],
				]
				, true
			],
			[
				[
					['a' => 'a'],
					['a' => 'b'],
				]
				, false
			],
			[
				[
					['a' => 'a'],
					['a' => 'b'],
				]
				, true
			],
			[
				[]
				, false
			],
			[
				[]
				, true
			],
		];
	}

	/**
	 * @dataProvider fetchRowsProvider
	 * @covers ::fetchRows()
	 * @covers ::enableCompressionIfNeeded()
	 */
	public function testFetchRows($sourceData, $compress) {

		// create temporary json file
		$tmpFile = tempnam("/tmp", "json-reader-test");
		$fh = fopen($compress ? "compress.zlib://{$tmpFile}" : $tmpFile, "w");
		foreach ($sourceData as $row) {
			fwrite($fh, json_encode($row) . "\n");
		}
		fclose($fh);

		$reader = new JsonReader($tmpFile, $compress);
		$rows = $reader->fetchRows();
		$data = iterator_to_array($rows);
		$this->assertEquals($sourceData, $data);
		unlink($tmpFile);
	}
}