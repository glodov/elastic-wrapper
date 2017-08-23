<?php

class Auto
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

	public function onElasticIndex()
	{
		return [
			'id'    => $this->id,
			'body'  => [
				'vendor' => $this->vendor,
				'model'  => $this->model,
				'year'   => $this->year
			]
		];
	}	
}