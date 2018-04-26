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

    public function getElasticAnalysis($locale)
    {
        $i18n = I18nEnum::decodeLanguage($locale);
        return [
            "filter" => [
                "{$i18n}_stop" => [
                    "type" =>       "stop",
                    "stopwords" =>  "_{$i18n}_"
                ],
                "{$i18n}_stemmer" => [
                    "type" =>       "stemmer",
                    "language" =>   "{$i18n}"
                ]
            ],
            "analyzer" => [
                "{$i18n}" => [
                    "char_filter" => ["html_strip"],
                    "tokenizer" =>  "standard",
                    "filter" => [
                        "lowercase",
                        "{$i18n}_stop",
                        "{$i18n}_stemmer"
                    ]
                ]
            ]
        ];
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
