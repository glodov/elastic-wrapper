<?php

namespace ElasticWrapper;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;

class Index
{

    /**
     * Elasticsearch client
     * @var object
     */
    public $client;

    /**
     * Name of index
     * @var string
     */
    public $name;

    public $shards = 1;
    public $replicas = 0;
    public $models = [];

    public function __construct($name)
    {
        $this->name = $name;
        $this->client = ClientBuilder::create()->build();
    }

    public function addModel($model)
    {
        $this->models[] = $model;
    }

    public function delete()
    {
        $params = [
            'index' => $this->name,
        ];
        if ($this->client->indices()->exists($params)) {
            $this->client->indices()->delete($params);
            return true;
        } else {
            return false;
        }
    }

    public function create()
    {
        $params = [
            'index' => $this->name,
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
}
