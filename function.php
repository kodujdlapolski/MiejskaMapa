<?php
$config = require('../config.php');

function getMapType($type)
{
    global $config;
    return $config['type_map'][$type];
}

function getData($type)
{
    global $config;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['url'] . $config['url_param'] . $type);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = array();
    $headers[] = 'Origin: ' . $config['url'];
    $headers[] = 'Accept-Encoding: gzip, deflate';
    $headers[] = 'Accept-Language: en-US,en;q=0.8,pl;q=0.6,ru;q=0.4';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    $headers[] = 'Accept: */*';
    $headers[] = 'Referer: ' . $config['url'] . '/mapaApp1/mapa';
    $headers[] = 'Connection: keep-alive';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch,CURLOPT_ENCODING , 'gzip');
    $dataJson = curl_exec($ch);
    curl_close ($ch);

    $dataJson = str_replace(
        array(',]', '",,"', 'id:"', 'name:"', 'gtype:"', 'imgurl:"', ',rh:', ',x:', ',y:', ',width:', ',height:', ',area:', ',attrs:', 'attrnames:', 'themeMBR:', 'isWholeImg:'),
        array(']', '","', '"id":"', '"name":"', '"gtype":"', '"imgurl":"', ',"rh":', ',"x":', ',"y":', ',"width":', ',"height":', ',"area":', ',"attrs":', '"attrnames":', '"themeMBR":', '"isWholeImg":'),
        $dataJson
    );
//    echo $dataJson;die;
    return json_decode($dataJson);
}

function convertGeo($geoData)
{
    global $config;

    $str = '';
    foreach ($geoData as $item) {
        $str .= $item[0] . "\t" . $item[1] . "\r\n";
    }
    file_put_contents($config['tmp_dir_path'] . '/data.in', $str);
    exec($config['convert_cmd'] . '< ' . $config['tmp_dir_path'] . '/data.in > ' . $config['tmp_dir_path'] . '/data.out', $o, $r);

    $newGeoData = array();
    foreach (explode("\r\n", file_get_contents($config['tmp_dir_path'] . '/data.out')) as $k => $newGeo) {
        if (!$newGeo) {
            continue;
        }
        $co = explode("\t", $newGeo);
        $newGeoData[] = array((float)trim($co[0]), (float)trim($co[1]));
    }
    return $newGeoData;
}

function getGeoJson($dataJson, $convert)
{
    $geo = array(
        'type' => 'FeatureCollection',
//        'crs' => array(
//            'type' => 'name',
//            'properties' => array(
//                'name' => 'urn:ogc:def:crs:EPSG:2178'
//            )
//        ),
        'features' => array()
    );
    $toConvert = array();
    foreach ($dataJson->foiarray as $points) {
        $toConvert[] = array($points->x, $points->y);

        $newprop = array();
        if (isset($points->name)) {
            foreach (explode("\n", $points->name) as $prop) {
                $propA = explode(':', $prop);
                $newprop[clearString(array_shift($propA), false, false, true, false)] = trim(implode(':', $propA));
            }
        }
        $geo['features'][] = array(
            'type' => 'Feature',
            'id' => '',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array((float)$points->x,(float)$points->y)
            ),
            'properties' => $newprop
        );
    }

    if ($convert) {
        foreach (convertGeo($toConvert) as $k => $newGeo) {
            $geo['features'][$k]['geometry']['coordinates'] = $newGeo;
        }
    }
    return $geo;
}

function format($format, $geojson)
{
    switch ($format) {
        case 'php':
            var_export($geojson);
            break;
        case 'json':
        default:
            echo json_encode($geojson);
            break;
    }
}

function clearString($string, $polishCharacters = false, $allowSpaces = true, $lowerCase = true, $encodeUrl = false)
{
    $unPretty = array('/ä/', '/ö/', '/ü/', '/Ä/', '/Ö/', '/Ü/', '/ß/',
        '/Š/','/Ž/','/š/','/ž/','/Ÿ/','/Ŕ/','/Á/','/Â/','/Ă/','/Ä/','/Ĺ/','/Ç/','/Č/','/É/','/Ę/','/Ë/','/Ě/','/Í/','/Î/','/Ď/','/Ń/',
        '/Ň/','/Ô/','/Ő/','/Ö/','/Ř/','/Ů/','/Ú/','/Ű/','/Ü/','/Ý/','/ŕ/','/á/','/â/','/ă/','/ä/','/ĺ/','/ç/','/č/','/é/','/ę/',
        '/ë/','/ě/','/í/','/î/','/ď/','/ň/','/ô/','/ő/','/ö/','/ř/','/ů/','/ú/','/ű/','/ü/','/ý/','/˙/',
        '/Ţ/','/ţ/','/Đ/','/đ/','/ß/','/Œ/','/œ/','/ľ/');

    $pretty   = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss',
        'S','Z','s','z','Y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N',
        'O','O','O','O','O','U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e',
        'e','i','i','i','i','o','o','o','o','o','u','u','u','u','y','y',
        'TH','th','DH','dh','ss','OE','oe','u');

    if (! $polishCharacters) {
        $unPrettyAdd = array('/ą/', '/Ą/', '/ć/', '/Ć/', '/ę/', '/Ę/', '/ł/', '/Ł/' ,'/ń/', '/Ń/', '/ó/', '/Ó/', '/ś/', '/Ś/', '/ź/', '/Ź/', '/ż/', '/Ż/');
        $prettyAdd = array('a', 'A', 'c', 'C', 'e', 'E', 'l', 'L', 'n', 'N', 'o', 'O', 's', 'S', 'z', 'Z', 'z', 'Z');

        $unPretty = array_merge($unPretty, $unPrettyAdd);
        $pretty = array_merge($pretty, $prettyAdd);
    }

    $string = preg_replace($unPretty, $pretty, $string);

    if ($lowerCase) {
        $string = mb_strtolower($string);
    }

    if (! $allowSpaces) {
        $string = str_replace(" ", "_", $string);
    }

    //usuń wszytko co jest niedozwolonym znakiem
    $string = preg_replace('/[^0-9a-zA-Z_,\.\- ąĄćĆęĘłŁńŃóÓśŚźŹżŻ]+/', '', $string);

    // zredukuj liczbę podkreśleń do jednego obok siebie
    $string = preg_replace('/[_]+/', '_', $string);

    // usuwamy możliwe podkreślenia na początku i końcu
    $string = trim($string, '_');
    $string = stripslashes($string);

    if ($encodeUrl) {
        $string = urlencode($string);
    }

    return $string;
}