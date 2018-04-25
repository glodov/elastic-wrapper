<?php

namespace ElasticWrapper;

interface IndexI18nInterface
{
    public function onElasticFetch(array $data);
    public function getElasticType();
    public function getElasticMappings($locale);
}
