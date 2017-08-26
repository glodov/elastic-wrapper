<?php

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/Model/Auto.php';

use ElasticWrapper\Index;

$file = __DIR__ . '/auto.csv';
$fp = fopen($file, 'r');
if (!$fp) {
	printf("Could not open file %s\n", $file);
}


$index = new Index();
$index->addModel(new Auto);
$index->create('indexed-auto');

$count = 0;
while (false !== ($line = fgetcsv($fp, 2048, ',', '"'))) {
	$count++;
}
rewind($fp);
$i = 0;
while (false !== ($line = fgetcsv($fp, 2048, ',', '"'))) {
	if (!$i++) {
		continue;
	}
	$model = new Auto;
	$model->id     = (int) $line[0];
	$model->year   = (int) $line[1];
	$model->vendor = $line[2];
	$model->model  = $line[3];

	$version = $index->save($model);
	printf(
		"%5s %-20s %-20s %4s %7s %5s%%     \r", 
		$model->id, 
		$model->vendor, 
		$model->model, 
		$model->year,
		$version ? 'OK[' . $version . ']' : 'FAILED',
		number_format(100 * $i / $count, 1)
	);
}
print("\n");

fclose($fp);

printf("%d items indexed\n", $i);