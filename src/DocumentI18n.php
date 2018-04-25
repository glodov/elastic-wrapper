<?php

namespace ElasticWrapper;

class DocumentI18n
{
  /**
   * Index
   * @var object
   */
  private $index;

  public function __construct($index)
  {
    $this->index = $index;
  }

  /**
   * Get document by ID
   * @param  object $model must implements ModelInterface and required id value
   * @return mixed         array with document fields OR FALSE if document not found
   */
  public function get($model)
  {
    $options = $this->checkOptions($model);

    if (isset($options['body'])) {
      unset($options['body']);
    }
    $response = [];
    try {
      $response = $this->index->client->get($options);
    } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
      $response['found'] = false;
    }
    return isset($response['_source']) ? $response['_source'] : false;
  }
  /**
   * Adds a document to index
   * @param  object $model must implements ModelInterface
   * @return mixed         number of document version OR FALSE
   */
  public function save($model)
  {
    $options = $this->checkOptions($model);

    $response = $this->index->client->index($options);
    return isset($response['_version']) ? $response['_version'] : false;
  }
  /**
   * Deleting document by ID from index
   * @param  object $model must implements ModelInterface and required id value
   * @return mixed         "deleted" when document was be delete OR FALSE if document not found
   */
  public function drop($model)
  {
    $options = $this->checkOptions($model);

    if (isset($options['body'])) {
      unset($options['body']);
    }
    $response = [];
    try {
      $response = $this->index->client->delete($options);
    } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
      $response['result'] = false;
    }
    return isset($response['result']) ? $response['result'] : false;
  }

  private function checkOptions($model)
  {
    if (!is_object($model)) {
      throw new Exception('$model must be an object');
    }
    if ($model instanceof ModelInterface) {
      $options = call_user_func([$model, 'onElasticIndex']);
    } elseif ($model instanceof ModelI18nInterface) {
      $options = call_user_func([$model, 'onElasticIndex'], $this->index->getLocale());
    } else {
      throw new Exception(
        sprintf(
          '%s must inherit the interface ModelInterface or ModelI18nInterface',
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
      if ($model instanceof ModelI18nInterface) {
        $options['index'] = $this->index->getName();
      } elseif ($model instanceof ModelInterface) {
        $options['index'] = $this->index->name;
      }
    }
    if (!isset($options['type'])) {
      if ($model instanceof ModelInterface || $model instanceof ModelI18nInterface) {
        $options['type'] = $this->index->models['0']->getElasticType(); //call_user_func([$model, 'getElasticType']);
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
