<?php

namespace WebScraper\ApiClient\Reader;

class JsonReader {

	/**
	 * @var string
	 */
	private $filePath;

	/**
	 * @var bool
	 */
	private $compressed;


	/**
	 * JsonReader constructor.
	 * @param string $filePath
	 * @param bool $compressed
	 * @codeCoverageIgnore
	 */
	public function __construct($filePath, $compressed = false) {

		$this->filePath = $filePath;
		$this->compressed = $compressed;
	}

	public function fetchRows() {

		$filePath = $this->filePath;

		$fh = fopen($filePath, "r");
		$this->enableCompressionIfNeeded($fh);
		while (($line = fgets($fh)) !== false) {

			$record = json_decode($line, true);
			yield $record;
		}
		fclose($fh);
	}

	public function enableCompressionIfNeeded($fh) {

		if ($this->compressed) {
			stream_filter_append($fh, 'zlib.inflate', STREAM_FILTER_ALL, ['window' => 31]);
		}
	}
}