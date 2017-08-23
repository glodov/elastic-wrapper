<?php

namespace ElasticWrapper;

use Elasticsearch\ClientBuilder;

class Search
{
	private $client;
	private $index;
	private $type;
	private $query;
	private $filter;
	private $model;

	// private $page_size;
	// private $page;
	// private $term;
	private $count;

	static private $globalIndex;

	public function __construct($index = null)
	{
		$this->index = $index ? $index : static::$globalIndex;

		$this->client = ClientBuilder::create()->build();
	}

	/**
	 * Returns count of records matches current query.
	 * 
	 * @access public
	 * @return integer Amount of records.
	 */
	public function count()
	{
		if (null !== $this->count) {
			return $this->count;
		}
		$params = $this->getParams();
		$response = $this->client->count($params);
		$this->count = $response['count'];
		return $this->count;
	}

	public function match($term, $where)
	{
		if (is_array($where)) {
			$this->query = [
				'multi_match' => [
					'query'  => $term,
					'fields' => $where
				]
			];
		} else {
			$this->query = [
				'match' => [
					$where => $term
				]
			];
		}
		return $this;
	}

	public function filter($name, $value = null)
	{
		if (is_array($name)) {
			$this->filter = [
				'term' => []
			];
			foreach ($name as $key => $value) {
				$this->filter['term'][$key] = $value;
			}
		} else {
			$this->filter([$name => $value]);
		}
		return $this;
	}

	public function setModel($model)
	{
		if (!method_exists($model, 'onElasticFetch')) {
			throw new Exception('Model must have onElasticFetch() method to handle results');
		}
		$this->model = $model;
	}

	public function results($params = null)
	{
		if (null === $params) {
			$params = $this->getParams();
		}
		$response = $this->client->search($params);
		return $this->resultsFromResponse($response);
	}

	public function resultsFromResponse($response)
	{
		if (!isset($response['hits']['hits'])) {
			return [];
		}
		$result = [];
		$class = null;
		if ($this->model) {
			$class = get_class($this->model);
		}
		foreach ($response['hits']['hits'] as $item) {
			if ($class) {
				$model = new $class;
				$model->onElasticFetch($item);
				$result[] = $model;
			} else {
				$result[] = $item;
			}
		}
		return $result;		
	}

	public function getParams()
	{
		$type = $this->type;
		if (!$type && $this->model) {
			if (method_exists('getElasticType', $this->model)) {
				$type = call_user_func([$this->model, 'getElasticType']);
			} else {
				$type = get_class($this->model);
			}
		}
		$params = [
			'index' => $this->index,
			'type'  => $type,
			'body'  => []
		];
		if ($this->query && $this->filter) {
			$params['body']['query'] = [
				'bool' => [
					'must'   => $this->query,
					'filter' => $this->filter
				]
			];
		} else if ($this->query) {
			$params['body']['query'] = $this->query;
		}
		return $params;		
	}

	static public function setIndex($index)
	{
		static::$globalIndex = $index;
	}
}