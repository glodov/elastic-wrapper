<?php

namespace ElasticWrapper;

interface ModelInterface
{
	public function onElasticFetch(array $data);
	public function onElasticIndex();
	public function getElasticType();
	public function getElasticMappings();
}
