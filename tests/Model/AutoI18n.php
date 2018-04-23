<?php

namespace Models;

use ElasticWrapper\ModelI18nInterface;
use ElasticWrapper\I18nEnum;

class AutoI18n implements ModelI18nInterface
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

	public function onElasticIndex($locale)
	{
		//TODO: add function for get locale data
		return [
			'id'    => $this->id,
			'body'  => [
				'vendor' => $this->vendor, //$this->i18n($locale)->vendor,
				'model'  => $this->model,
				'year'   => $this->year
			]
		];
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
