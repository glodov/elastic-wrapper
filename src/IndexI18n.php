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

    /**
     * one model for one index
     * @var array
     */
    public $model = [];

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

    public function setModel($model)
    {
        $this->model = $model;
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
        $response = [];
        if ($this->client->indices()->exists($params)) {
            //index exists
            if ($this->model instanceof IndexI18nInterface) {
                $putParams =    [
                    'index' => $this->getName(),
                    'type'  => call_user_func([$this->model, 'getElasticType']),
                    'body'  => call_user_func([$this->model, 'getElasticMappings'], $this->locale)
                ];
                $response = $this->client->indices()->putMapping($putParams);
            }
        } else {
            //create index
            $params['body'] = [
                'settings' => [
                    'number_of_shards'   => $this->shards,
                    'number_of_replicas' => $this->replicas
                ]
            ];

            if ($this->model instanceof IndexI18nInterface) {
                $res = call_user_func([$this->model, 'getElasticAnalysis'], $this->locale);
                if ($res) {
                    $params['body']['settings']['analysis'] = $res;
                }
            }
            $mappings = [];

            if ($this->model instanceof IndexI18nInterface) {
                $res = call_user_func([$this->model, 'getElasticMappings'], $this->locale);
                $mappings = array_replace_recursive($mappings, $res);
            }

            if (!empty($mappings)) {
                $params['body']['mappings'] = $mappings;
            }

            $response = $this->client->indices()->create($params);
        }
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

    private function getMappings()
    {
        $params = [
            'index' => $this->getName()
        ];
        $response = $this->client->indices()->getMapping($params);
        return $response[$this->getName()]['mappings'];
    }

    public function issetMappings($type)
    {
        $mappings = $this->getMappings();
        if (isset($mappings[$type])) {
            return true;
        } else {
            return false;
        }
    }
}
