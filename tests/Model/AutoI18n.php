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

	public function onElasticIndex($locale)
	{
		//TODO: add function for get locale data
		return [
			'id'    => $this->id,
			'body'  => [
				'type'   => $this->getType(),
				'vendor' => $this->vendor, //$this->i18n($locale)->vendor,
				'model'  => $this->model,
				'year'   => $this->year
			]
		];
	}

	public function getType()
	{
		return 'auto';
	}
}
