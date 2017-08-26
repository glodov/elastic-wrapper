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
		if (!is_object($model)) {
			throw new Exception('$model must be an object');
		}
		if (!method_exists($model, 'onElasticIndex')) {
			throw new Exception(
				sprintf(
					'%s must have onElasticIndex method which returns array of index options', 
					get_class($model)
				)
			);
		}
		$options = call_user_func([$model, 'onElasticIndex']);
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
			if (method_exists($model, 'getElasticType')) {
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

		$response = $this->client->index($options);
		return isset($response['_version']) ? $response['_version'] : false;
	}

	public function addModel($model)
	{
		$this->models[] = $model;
	}

	public function create($index = null)
	{
		$index = $index ? $index : $this->index;
		$this->index = $index;
		$params = [
			'index' => $index,
		];
		$response = [];
		try {
			$response = $this->client->indices()->getSettings($params);		
		} catch (Missing404Exception $e) {
			// index not found
		}
		if (isset($response[$index])) {
			$this->client->indices()->delete($params);
		}

		$params['body'] = [
			'settings' => [
				'number_of_shards'   => $this->shards,
				'number_of_replicas' => $this->replicas
			]
		];

		$mappings = [];
		foreach ($this->models as $model) {
			if (method_exists($model, 'getElasticMappings')) {
				$res = call_user_func([$model, 'getElasticMappings']);
				$mappings = array_replace_recursive($mappings, $res);
			}
		}

		if (!empty($mappings)) {
			$params['body']['mappings'] = $mappings;
		}
		$response = $this->client->indices()->create($params);
		return isset($response['acknowledged']) && $response['acknowledged'];
	}
}

