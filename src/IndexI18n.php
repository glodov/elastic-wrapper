<?php

namespace ElasticWrapper;

class IndexI18n extends Index
{

	/**
	 * preffix for language
	 * @var [type]
	 */
	public $locale;

	private $shards = 1;
	private $replicas = 0;
	private $models = [];

	public function __construct($name, $locale = null)
	{
		$this->locale = $locale;

		parent::__construct($name);
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
	}

	public function create()
	{
		$params = [
			'index' => $this->name . '-' . $this->locale,
		];
		if ($this->client->indices()->exists($params)) {
			//index exists
			return false;
		}

		$params['body'] = [
			'settings' => [
				'number_of_shards'   => $this->shards,
				'number_of_replicas' => $this->replicas
			]
		];

		$mappings = [];
		foreach ($this->models as $model) {
			if ($model instanceof ModelI18nInterface) {
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
		return isset($response['acknowledged']) && $response['acknowledged'];
	}

	public function delete()
	{
		$params = [
			'index' => $this->name . '-' . $this->locale,
		];
		if ($this->client->indices()->exists($params)) {
			$this->client->indices()->delete($params);
			return true;
		} else {
			return false;
		}
	}
}
