<?php

namespace ElasticWrapper;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;

class Index
{
	private $client;
	private $index;

	private $shards = 1;
	private $replicas = 0;
	private $models = [];

	public function __construct($index = null)
	{
		$this->index = $index;

		$this->client = ClientBuilder::create()->build();
	}

	public function save($model)
	{
		$options = $this->checkOptions($model);

		$response = $this->client->index($options);

		return isset($response['_version']) ? $response['_version'] : false;
	}

	public function drop($model)
	{
		$options = $this->checkOptions($model);

		if (isset($options['body'])) {
			unset($options['body']);
		}
		$response = [];
		try {
			$response = $this->client->delete($options);
		} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
			$response['found'] = false;
		}
		return isset($response['found']) ? $response['found'] : false;
	}

	public function addModel($model)
	{
		$this->models[] = $model;
	}

	public function deleteIndex($index = null)
	{
		$index = $index ? $index : $this->index;
		$this->index = $index;
		$params = [
			'index' => $index,
		];
		if ($this->client->indices()->exists($params)) {
			$this->client->indices()->delete($params);
			return true;
		} else {
			return false;
		}
	}

	public function createIndex($index = null)
	{
		$index = $index ? $index : $this->index;
		$this->index = $index;
		$params = [
			'index' => $index,
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
			if ($model instanceof ModelInterface) {
				$res = call_user_func([$model, 'getElasticMappings']);
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

	private function checkOptions($model)
	{
		if (!is_object($model)) {
			throw new Exception('$model must be an object');
		}
		if ($model instanceof ModelInterface) {
			$options = call_user_func([$model, 'onElasticIndex']);
		} else {
			throw new Exception(
				sprintf(
					'%s must inherit the interface ModelInterface',
					get_class($model)
				)
			);
		}
		
		if (!is_array($options)) {
			throw new Exception(
				sprintf(
					'%s->onElasticIndex() must return array of index options',
					get_class($model)
				)
			);
		}
		if (!isset($options['index'])) {
			$options['index'] = $this->index;
		}
		if (!isset($options['type'])) {
			if ($model instanceof ModelInterface) {
				$options['type'] = call_user_func([$model, 'getElasticType']);
			} else {
				$options['type'] = get_class($model);
			}
		}
		if (!isset($options['index'])) {
			throw new Exception('index option [index] must be defined');
		}
		if (!isset($options['type'])) {
			throw new Exception('index option [type] must be defined');
		}
		if (empty($options['id'])) {
			throw new Exception('index option [id] must be defined');
		}
		if (empty($options['body']) || !is_array($options['body'])) {
			throw new Exception('index option [body] must be defined and be an array type');
		}
		return $options;
	}
}
