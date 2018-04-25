<?php

namespace Models;

use ElasticWrapper\IndexI18nInterface;
use ElasticWrapper\I18nEnum;

class IndexAutoI18n implements IndexI18nInterface
{
    public $id;
    public $vendor;
    public $model;
    public $year;
    public $type;

    public function onElasticFetch(array $data)
    {
        $this->id = $data['_id'];
        foreach ($data['_source'] as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getElasticType()
    {
        return 'autos';
    }

    public function getElasticMappings($locale)
    {
        //TODO: add analyzer
        return [
            'autos' => [
                'properties' => [
                    'type' => [
                        'type' => 'text'
                    ],
                    'vendor' => [
                        'type' => 'text',
                        'analyzer' => I18nEnum::decodeLanguage($locale)
                    ],
                    'model' => [
                        'type' => 'text',
                        'analyzer' => I18nEnum::decodeLanguage($locale)
                    ],
                    'year' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];
    }
}
