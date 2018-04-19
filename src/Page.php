<?php

namespace ElasticWrapper;

class Page
{
	public $no;
	public $label;

	public $active = false;

	public $first = false;
	public $last  = false;
	public $prev  = false;
	public $next  = false;

	public function __construct($no, $label = null)
	{
		$chars = [
			'first' => '«',
			'last'  => '»',
			'prev'  => '‹',
			'next'  => '›'
		];
		$this->no = $no;
		$this->label = isset($chars[$label]) ? $chars[$label] : $no;

		if (isset($chars[$label])) {
			$this->$label = true;
		}
	}

	public function __toString()
	{
		return (string) $this->label;
	}
}
