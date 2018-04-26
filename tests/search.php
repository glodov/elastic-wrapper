<?php

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/Model/Auto.php';
include __DIR__ . '/Model/AutoI18n.php';
include __DIR__ . '/Model/IndexAutoI18n.php';

use ElasticWrapper\Search;
use ElasticWrapper\SearchI18n;
use ElasticWrapper\Paginator;
use Models\Auto;
use Models\AutoI18n;
use Models\IndexAutoI18n;
function showResults($paginator)
{
    printf(" Found %d entries\n", $paginator->count);

    foreach ($paginator->results() as $item) {
        printf(
            "%d %s %s (%4s)\n",
            $item->id,
            $item->vendor,
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

Search::setIndex('indexed-auto-');

$search = new Search();
$search->setModel(new Auto);

$term = 'mini';
$search->match($term, ['vendor', 'model']);
$paginator = new Paginator($search, 500, 1, 4);
printf("Search for term [%s]\n", $term);
showResults($paginator);
print("\n");


$search = new Search();
$search->setModel(new Auto);

$search->match($term, ['vendor', 'model'])->filter('year', [2010, 2011, 2012])->sort('year')->sort('_score');
// $search->match($term, ['vendor', 'model'])->sort('_score');
print(json_encode($search->getParams(), JSON_PRETTY_PRINT));
$paginator = new Paginator($search, 500, 1, 4);
printf("Search for term [%s] filtered by [vendor=austin]\n", $term);

showResults($paginator);

$search = new SearchI18n('indexed-auto', ['nl', '']);
$search->setModel(new IndexAutoI18n);
$term = 'mini';
$search->match($term, ['vendor', 'model^3'])->filter('year', [1996, 2001, 2008, 2009, 2010])->sort('_score')->sort('year');
// $search->match($term, ['vendor', 'model'])->sort('_score');
print(json_encode($search->getParams(), JSON_PRETTY_PRINT));
$paginator = new Paginator($search, 500, 1, 4);
printf("Search for term [%s] filtered by [vendor=austin] in index 'indexed-auto-, indexed-auto-nl'\n", $term);
showResults($paginator);

$search->setLocale('nl');
$paginator = new Paginator($search, 500, 1, 4);
printf("Search for term [%s] filtered by [vendor=austin] in index 'indexed-auto-nl'\n", $term);
showResults($paginator);

$search->setLocale('en');
$paginator = new Paginator($search, 500, 1, 4);
printf("Search for term [%s] filtered by [vendor=austin] in index 'indexed-auto-en'\n", $term);
showResults($paginator);

$search->setLocale('*');
$paginator = new Paginator($search, 500, 1, 4);
printf("Search for term [%s] filtered by [vendor=austin] in index 'indexed-auto-*'\n", $term);
showResults($paginator);
