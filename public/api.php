<?php
header('Content-Type: text/html; charset=utf-8');

include('../function.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

//error_reporting(0);
//ini_set('display_errors', 0);

$type = getMapType(isset($_GET['type']) ? $_GET['type'] : '');
$format = isset($_GET['format']) ? $_GET['format'] : $config['default_format'];

$dataJson = getData($type);
$geojson = getGeoJson($dataJson, $config['convert_enabled']);
format($format, $geojson);

