<?php

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/Model/Auto.php';
include __DIR__ . '/Model/AutoI18n.php';

use ElasticWrapper\Index;
use ElasticWrapper\IndexI18n;
use ElasticWrapper\Document;
use Models\Auto;
use Models\AutoI18n;

$file = __DIR__ . '/auto-loc.csv';
$fp = fopen($file, 'r');
if (!$fp) {
	printf("Could not open file %s\n", $file);
}

$index = new IndexI18n('indexed-auto');
$index->addModel(new AutoI18n);
printf(
	"Index %s. Create: %d. Delete: %d.\n",
	'indexed-auto',
	$index->create(),
	$index->delete()
);

$index = new IndexI18n('indexed-auto', 'nl');
$index->addModel(new AutoI18n);
printf(
	"Index %s. Create: %d. Delete: %d.\n",
	'indexed-auto',
	$index->create(),
	$index->delete()
);

$index = new IndexI18n('indexed-auto', 'en');
$index->addModel(new AutoI18n);
printf(
	"Index %s. Create: %d. Delete: %d.\n",
	'indexed-auto',
	$index->create(),
	$index->delete()
);

$index = new IndexI18n('indexed-auto');
$index->addModel(new Auto);
$response = $index->create();

$index = new IndexI18n('indexed-auto', 'nl');
$index->addModel(new AutoI18n);
$response = $index->create();

$index->setLocale('en');
$response = $index->create();


$document = new Document($index);

$count = 0;
while (false !== ($line = fgetcsv($fp, 2048, ',', '"'))) {
	$count++;
}
rewind($fp);
$i = 0;
$countLocale = new stdClass();
$countLocale->nl = 0;
$countLocale->en = 0;
while (false !== ($line = fgetcsv($fp, 2048, ',', '"'))) {
	if (!$i++) {
		continue;
	}
	$model = new AutoI18n;

	$model->id     = (int) $line[0];
	$model->year   = (int) $line[1];
	$model->vendor = $line[2];
	$model->model  = $line[3];

	$index->setLocale($line[4]);
	if (!empty($line[4])) {
		$countLocale->{$line[4]}++;
	}
	$version = $document->save($model);

	printf(
		"%5s %-20s %-20s %4s %7s %5s%%     \r",
		$model->id,
		$model->vendor,
		$model->model,
		$model->year,
		$version ? 'OK[' . $version . ']' : 'FAILED',
		number_format(100 * $i / $count, 1)
	);
	$id = (int) $line[0];
}
print("\n");
var_dump($countLocale);
print("\n");
$model = new AutoI18n;
$index->setLocale('en');
$model->id = $id;
print("Get document $model->id: ");
var_dump($document->get($model));

print("\n");
//print("Delete document $model->id: ");
//var_dump($document->drop($model));
//print("\n");

fclose($fp);

printf("%d items indexed\n", $i);
