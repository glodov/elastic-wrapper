<?php

namespace ElasticWrapper;

use Elasticsearch\ClientBuilder;

class SearchI18n extends Search
{

	/**
	 * preffix for language. Use ISO 3166-1 alpha-2 standart.
	 * @var string OR array
	 */
	private $locale;

	private $separator = '-';

	public function __construct($index = null, $locale = null)
	{
		$this->index = $index;
		$this->locale = $locale;

		$this->client = ClientBuilder::create()->build();
	}

	public function setLocale($locale)
	{
		$this->locale = strtolower($locale);
	}

	public function setSeparator($separator)
	{
		$this->separator = $separator;
	}
	
	public function splitIndex($locale)
	{
		return $this->index . $this->separator . $locale;
	}

	public function getIndex()
	{
		if (empty($this->locale)) {
			return $this->index . $this->separator;
		} else {
			if (is_array($this->locale)) {
				$indexes = array_map([$this, 'splitIndex'], $this->locale);
				return implode(',', $indexes);
			} else {
				return $this->index . $this->separator . $this->locale;
			}
		}
	}
}
