<?php

namespace ElasticWrapper;

use Elasticsearch\ClientBuilder;

class Search
{
	public $client;
	public $index;
	public $type;
	public $query;
	public $filter;
	public $sort;
	public $model;

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
		//if (null !== $this->count) {
		//	return $this->count;
		//}
		$params = $this->getParams();
		if (isset($params['body']['sort'])) {
			unset($params['body']['sort']);
		}
		if (isset($params['sort'])) {
			unset($params['sort']);
		}
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
			$plural = false;
			foreach ($name as $value) {
				if (is_array($value)) {
					$plural = true;
					break;
				}
			}
			$term = $plural ? 'terms' : 'term';
			$this->filter = [
				$term => []
			];
			foreach ($name as $key => $value) {
				$this->filter[$term][$key] = $value;
			}
		} else {
			$this->filter([$name => $value]);
		}
		return $this;
	}

	public function sort($field, $options = 'asc')
	{
		if (!is_array($this->sort)) {
			$this->sort = [];
		}
		if (in_array($field, ['_doc', '_score'])) {
			$this->sort[] = $field;
		} else {
			$this->sort[] = [
				$field => $options
			];
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
			if (method_exists($this->model, 'getElasticType')) {
				$type = call_user_func([$this->model, 'getElasticType']);
			} else {
				$type = get_class($this->model);
			}
		}
		$params = [
			'index' => $this->getIndex(),
			'type'  => $type,
		];
		if ($this->query && $this->filter) {
			$params['body'] = [
				'query' => [
					'bool' => [
						'must'   => $this->query,
						'filter' => $this->filter
					]
				]
			];
		} elseif ($this->query) {
			$params['body'] = [
				'query' => $this->query
			];
		} elseif ($this->filter) {
			$params['body'] = [
				'query' => [
					'bool' => [
						'filter' => $this->filter
					]
				]
			];
		}
		if ($this->sort) {
			if (isset($params['body'])) {
				$params['body']['sort'] = $this->sort;
			} else {
				$params['body'] = [
					'sort' => $this->sort
				];
			}
		}
		return $params;
	}

	public static function setIndex($index)
	{
		static::$globalIndex = $index;
	}

	public function getIndex()
	{
		if (empty(static::$globalIndex)) {
			return $this->index;
		} else {
			return static::$globalIndex;
		}
	}
}
