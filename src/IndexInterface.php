<?php

namespace ElasticWrapper;

interface IndexInterface
{
    public function onElasticFetch(array $data);
    public function getElasticType();
    public function getElasticMappings();
}
