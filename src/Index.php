<?php

namespace ElasticWrapper;

use Elasticsearch\ClientBuilder;

class Index
{
	private $client;
	private $index;

	public function __construct($index)
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
			$options['type'] = get_class($model);
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
}

