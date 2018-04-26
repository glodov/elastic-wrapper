<?php

namespace Models;

use ElasticWrapper\ModelInterface;

class Auto implements ModelInterface
{
    public $id;
    public $vendor;
    public $model;
    public $year;

    public function onElasticFetch(array $data)
    {
        $this->id = $data['_id'];
        foreach ($data['_source'] as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function onElasticIndex()
    {

        return [
            'id'    => $this->id,
            'body'  => [
                'vendor' => $this->vendor,
                'model'  => $this->model,
                'year'   => $this->year
            ]
        ];
    }

    public function getElasticType()
    {
        return 'autos';
    }

    public function getElasticMappings()
    {
        return [
            'autos' => [
                'properties' => [
                    'vendor' => [
                        'type' => 'text'
                    ],
                    'model' => [
                        'type' => 'text'
                    ],
                    'year' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];
    }
    public function getType()
    {
        return 'auto';
    }
}
