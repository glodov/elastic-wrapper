<?php

namespace ElasticWrapper;

use ElasticWrapper\I18nEnum;

class IndexI18n extends Index
{

	/**
	 * preffix for language. Use ISO 3166-1 alpha-2 standart.
	 * @var string
	 */
	private $locale;
	private $separator = '-';

	public function __construct($name, $locale = null)
	{
		$this->locale = strtolower($locale);

		parent::__construct($name);
	}

	public function getName()
	{
		if (empty($this->locale)) {
			return $this->name . $this->separator;
		} else {
			return $this->name . $this->separator . $this->locale;
		}
	}

	public function setLocale($locale)
	{
		$this->locale = strtolower($locale);
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setSeparator($separator)
	{
		$this->separator = $separator;
	}

	public function create()
	{
		$params = [
			'index' => $this->getName(),
		];
		if ($this->client->indices()->exists($params)) {
			//index exists
			return false;
		}

		$params['body'] = [
			'settings' => [
				'number_of_shards'   => $this->shards,
				'number_of_replicas' => $this->replicas,
				'analysis' => $this->getElasticAnalysis($this->locale)
			]
		];

		$mappings = [];
		foreach ($this->models as $model) {
			if ($model instanceof ModelI18nInterface || $model instanceof ModelInterface) {
				//TODO: transmit the language instead of the locale.
				$res = call_user_func([$model, 'getElasticMappings'], $this->locale);
				$mappings = array_replace_recursive($mappings, $res);
			}
		}

		if (!empty($mappings)) {
			$params['body']['mappings'] = $mappings;
		}

		$response = [];
		$response = $this->client->indices()->create($params);
		var_dump($response);
		return isset($response['acknowledged']) && $response['acknowledged'];
	}

	public function delete()
	{
		$params = [
			'index' => $this->getName(),
		];
		if ($this->client->indices()->exists($params)) {
			$this->client->indices()->delete($params);
			return true;
		} else {
			return false;
		}
	}

	public function getElasticAnalysis($locale)
	{
		$i18n = I18nEnum::decodeLanguage($locale);
		return [
			"filter" => [
				"{$i18n}_stop" => [
					"type" =>       "stop",
					"stopwords" =>  "_{$i18n}_"
				],
				"{$i18n}_stemmer" => [
					"type" =>       "stemmer",
					"language" =>   "{$i18n}"
				]
			],
			"analyzer" => [
				"{$i18n}" => [
					"char_filter" => ["html_strip"],
					"tokenizer" =>  "standard",
					"filter" => [
						"lowercase",
						"{$i18n}_stop",
						"{$i18n}_stemmer"
					]
				]
			]
		];
	}
}
