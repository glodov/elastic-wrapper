<?php

namespace ElasticWrapper;

interface ModelI18nInterface
{
	public function onElasticFetch(array $data);
	public function onElasticIndex($locale);
	public function getElasticType();
	public function getElasticMappings($locale);
}
