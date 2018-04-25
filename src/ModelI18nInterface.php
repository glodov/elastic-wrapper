<?php

namespace ElasticWrapper;

interface ModelI18nInterface
{
    public function onElasticIndex($locale);
    public function getType();
}
