<?php

spl_autoload_register(function($classname) {
	$path = __DIR__."/../src/".str_replace("IconBuilder/", "", str_replace("\\", "/", $classname)).".php";
	if (file_exists($path)) {
		require $path;
	}
});

error_reporting(~ E_NOTICE);

$builder = new \IconBuilder\IconBuilder();

try {
	if ($argc < 2) {
		throw new Exception("Please provide an output filename");
	}
	if ($argc < 3) {
		throw new Exception("You need at least one input file name");
	}

	$count = 0;
	for ($i = 1; $i < $argc - 1; $i++) {
		$count++;
		$builder->addImageFromFilename($argv[$i]);
	}


	$builder->saveAsIco($argv[$i]);
	echo "Outputting $count images into ".$argv[$i]."\n";
} catch (\Exception $e) {
	echo "\033[31mError: \033[0m".$e->getMessage()."\n";
	exit(1);
}