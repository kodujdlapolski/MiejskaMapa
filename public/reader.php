<?php
header('Content-Type: text/html; charset=utf-8');

include('../function.php');
$config = require('../config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

//error_reporting(0);
//ini_set('display_errors', 0);

$reader = new Reader_WarszawaUM(array_merge($config['reader']['warszawa_um'], $config['reader']['basic']));
$data = $reader->getData();

$saver = new Saver_Mongo($config['saver']['mongo']);
$saver->save($data);