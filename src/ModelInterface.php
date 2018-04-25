<?php

namespace ElasticWrapper;

interface ModelInterface
{
    public function onElasticIndex();
    public function getType();
}
