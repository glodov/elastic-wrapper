<?php

namespace ElasticWrapper;

interface IndexI18nInterface
{
    public function onElasticFetch(array $data);
    public function getElasticType();
    public function getElasticAnalysis($locale);
    public function getElasticMappings($locale);
}
