<?php

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/Model/Auto.php';

use ElasticWrapper\Search;
use ElasticWrapper\Paginator;

function showResults($paginator) {
	printf(" Found %d entries\n", $paginator->count);

	foreach ($paginator->results() as $item) {
		printf(
			"  %s %s (%4s)\n", 
			// $item->id, 
			strtoupper($item->vendor), 
			$item->model, 
			$item->year
		);
	}

	print(' ');
	foreach ($paginator->items(true) as $page) {
		if ($page->active) {
			printf('[%s] ', $page);
		} else {
			printf('%s ', $page);
		}
	}
	print("\n");	
}

Search::setIndex('indexed-auto');

$term = 'mini';
$search = new Search();
$search->setModel(new Auto);

$search->match($term, ['vendor', 'model'])->filter('year', [2010, 2011, 2012])->sort('year')->sort('_score');
$paginator = new Paginator($search, 5, 1, 4);
printf("Search for term [%s] filtered by [vendor=austin]\n", $term);
showResults($paginator);
