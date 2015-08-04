<?php

class Element
{
    public $id;
    public $locWgs = array('x' => null, 'y' => null);
    public $locOrg = array('x' => null, 'y' => null);
    public $category;
    public $name;
    public $prop = array();
    public $description;
    public $source;
    public $date;
}

class Options
{
    /**
     * @var array|null
     */
    protected $_options = array();


    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->_options = $options;
    }

    /**
     * @param array $options
     * @return Options
     */
    public function setAll(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return Options
     */
    public function set($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAll()
    {
        return $this->_options;
    }

    /**
     * @param $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }
        return $defaultValue;
    }
}

interface Reader_Interface
{
    public function getData();
}

abstract class Reader_Abstract implements Reader_Interface
{
    /**
     * @var Options
     */
    protected $_options;


    /**
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $this->_options = new Options($options);
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->_options;
    }

    abstract public function getData();
}

class Reader_WarszawaUM extends Reader_Abstract
{
    const SOURCE_NAME = 'Warszawa UM';


    /**
     * @return array
     */
    public function getData()
    {
        $elements = array();
        foreach ($this->getOptions()->get('type_map') as $category => $type) {
            $rawData = $this->_readRawData($type);
            $data = $this->_jsonToArray($rawData);
            $categoryElements = $this->_prepareData($data, $category);
            $elements = array_merge($elements, $categoryElements);
        }
        return $elements;
    }

    /**
     * @param $data
     * @param $category
     * @return array
     */
    protected function _prepareData($data, $category)
    {
        if (!isset($data->foiarray)) {
            return array();
        }

        $collection = array();
        $toConvert = array();
        $k = 0;
        foreach ($data->foiarray as $point) {
            $element = $this->_getElementFromPoints($point, $category);
            $collection[$k] = $element;
            $toConvert[$k] = $element->locOrg;
            $k++;
        }

        $converter = new Converter_Geo($this->getOptions()->getAll());
        foreach ($converter->convert($toConvert) as $k => $new) {
            $collection[$k]->locWgs = $new;
        }
        return $collection;
    }

    /**
     * @param $point
     * @param $category
     * @return Element
     */
    protected function _getElementFromPoints($point, $category)
    {
        $newprop = array();
        if (isset($point->name)) {
            foreach (explode("\n", $point->name) as $prop) {
                $propArray = explode(':', $prop);
                $propName = array_shift($propArray);
                $propName = Utils::clearString($propName, false, false, true, false);
                $propValue = trim(implode(':', $propArray));
                $newprop[$propName] = $propValue;
            }
        }

        $element = new Element();
        $element->id = sha1(self::SOURCE_NAME . '-' . $category . '-' . $point->x . '-' . $point->y);
        $element->category = $category;
        $element->date = date('Y-m-d H:i:s');
        $element->name = isset($newprop['nazwa']) ? $newprop['nazwa'] : '';
        $element->description = implode(', ', $newprop);
        $element->prop = $newprop;
        $element->locOrg = array(
            'x' => (float)$point->x,
            'y' => (float)$point->y
        );
        $element->locWgs = '';
        $element->source = self::SOURCE_NAME;

        return $element;
    }

    /**
     * @param $type
     * @return mixed
     */
    protected function _readRawData($type)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl($type));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_ENCODING , 'gzip');

        $headers = array(
            'Origin: ' . $this->_getOrigin(),
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: en-US,en;q=0.8,pl;q=0.6,ru;q=0.4',
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: */*',
            'Referer: ' . $this->_getOrigin() . '/mapaApp1/mapa',
            'Connection: keep-alive'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $rawData = curl_exec($ch);
        curl_close ($ch);

        return $rawData;
    }

    /**
     * @param $type
     * @return string
     */
    protected function _getUrl($type)
    {
        return $this->getOptions()->get('url') . $this->getOptions()->get('url_param') . $type;
    }

    /**
     * @return mixed
     */
    protected function _getOrigin()
    {
        return $this->getOptions()->get('url');
    }

    /**
     * @param $json
     * @return mixed
     */
    protected function _jsonToArray($json)
    {
        $json = str_replace(
            array(',]', '",,"', 'id:"', 'name:"', 'gtype:"', 'imgurl:"', ',rh:', ',x:', ',y:', ',width:', ',height:', ',area:', ',attrs:', 'attrnames:', 'themeMBR:', 'isWholeImg:'),
            array(']', '","', '"id":"', '"name":"', '"gtype":"', '"imgurl":"', ',"rh":', ',"x":', ',"y":', ',"width":', ',"height":', ',"area":', ',"attrs":', '"attrnames":', '"themeMBR":', '"isWholeImg":'),
            $json
        );
        return json_decode($json);
    }
}

interface Converter_Interface
{
    public function convert($data);
}

abstract class Converter_Abstract implements Converter_Interface
{
    /**
     * @var Options
     */
    protected $_options;


    /**
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $this->_options = new Options($options);
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->_options;
    }

    abstract public function convert($data);
}

class Converter_Geo extends Converter_Abstract
{
    /**
     * @param $data
     * @return array
     */
    public function convert($data)
    {
        $fileInPath = $this->_getDataInFilePath();
        $fileOutPath = $this->_getDataOutFilePath();

        $this->_saveOldGeoToFile($data, $fileInPath);
        exec($this->getOptions()->get('convert_cmd') . ' < ' . $fileInPath . ' > ' . $fileOutPath, $output, $return);
        return $this->_getNewGeoFromFile($fileOutPath);
    }

    /**
     * @param $data
     * @param $filePath
     */
    protected function _saveOldGeoToFile($data, $filePath)
    {
        $str = '';
        foreach ($data as $item) {
            $str .= $item['x'] . "\t" . $item['y'] . "\r\n";
        }
        file_put_contents($filePath, $str);
    }

    /**
     * @param $filePath
     * @return array
     */
    protected function _getNewGeoFromFile($filePath)
    {
        $newGeoData = array();
        foreach (explode("\r\n", file_get_contents($filePath)) as $k => $newGeo) {
            if (!$newGeo) {
                continue;
            }
            $co = explode("\t", $newGeo);
            $newGeoData[$k] = array(
                'x' => (float)trim($co[0]),
                'y' => (float)trim($co[1])
            );
        }
        return $newGeoData;
    }

    /**
     * @return string
     */
    protected function _getDataInFilePath()
    {
        return $this->getOptions()->get('tmp_dir_path') . '/' . $this->getOptions()->get('tmp_file_in');
    }

    /**
     * @return string
     */
    protected function _getDataOutFilePath()
    {
        return $this->getOptions()->get('tmp_dir_path') . '/' . $this->getOptions()->get('tmp_file_out');
    }
}

interface Saver_Interface
{
    public function save($data);
}

abstract class Saver_Abstract implements Saver_Interface
{
    /**
     * @var Options
     */
    protected $_options;


    /**
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $this->_options = new Options($options);
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->_options;
    }

    abstract public function save($data);
}

class Saver_Mongo extends Saver_Abstract
{
    /**
     * @param $data
     */
    public function save($data)
    {
        $collection = $this->_getCollection();

        foreach ($data as $element) {
            $this->_saveElement($element, $collection);
        }
    }

    /**
     * @return MongoCollection
     */
    protected function _getCollection()
    {
        $mongoConfig = $this->getOptions()->get('mongo');

        $mongo = new MongoClient($mongoConfig['server']);
        $db = $mongo->{$mongoConfig['db']};
        return $db->{$mongoConfig['collection']};
    }

    /**
     * @param Element $element
     * @param $collection
     */
    protected function _saveElement(Element $element, $collection)
    {
        $document = array(
            '_id' => $element->id,
            'locWgs' => array(
                'type' => 'Point',
                'coordinates' => array(
                    $element->locWgs['x'],
                    $element->locWgs['y']
                )
            ),
            'locOrg' => array(
                'type' => 'Point',
                'coordinates' => array(
                    $element->locOrg['x'],
                    $element->locOrg['y']
                )
            ),
            'category' => $element->category,
            'name' => $element->name,
            'prop' => $element->prop,
            'description' => $element->description,
            'source' => $element->source,
            'date' => $element->date
        );
        try {
            $collection->save($document);
        } catch (MongoException $e) {
//            echo $e;
//            var_dump($document);
        }
    }
}

class Utils
{
    /**
     * @param $string
     * @param bool|false $polishCharacters
     * @param bool|true $allowSpaces
     * @param bool|true $lowerCase
     * @param bool|false $encodeUrl
     * @return mixed|string
     */
    static public function clearString($string, $polishCharacters = false, $allowSpaces = true, $lowerCase = true, $encodeUrl = false)
    {
        $unPretty = array('/ä/', '/ö/', '/ü/', '/Ä/', '/Ö/', '/Ü/', '/ß/',
            '/Š/', '/Ž/', '/š/', '/ž/', '/Ÿ/', '/Ŕ/', '/Á/', '/Â/', '/Ă/', '/Ä/', '/Ĺ/', '/Ç/', '/Č/', '/É/', '/Ę/', '/Ë/', '/Ě/', '/Í/', '/Î/', '/Ď/', '/Ń/',
            '/Ň/', '/Ô/', '/Ő/', '/Ö/', '/Ř/', '/Ů/', '/Ú/', '/Ű/', '/Ü/', '/Ý/', '/ŕ/', '/á/', '/â/', '/ă/', '/ä/', '/ĺ/', '/ç/', '/č/', '/é/', '/ę/',
            '/ë/', '/ě/', '/í/', '/î/', '/ď/', '/ň/', '/ô/', '/ő/', '/ö/', '/ř/', '/ů/', '/ú/', '/ű/', '/ü/', '/ý/', '/˙/',
            '/Ţ/', '/ţ/', '/Đ/', '/đ/', '/ß/', '/Œ/', '/œ/', '/ľ/');

        $pretty = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss',
            'S', 'Z', 's', 'z', 'Y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N',
            'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e',
            'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y',
            'TH', 'th', 'DH', 'dh', 'ss', 'OE', 'oe', 'u');

        if (!$polishCharacters) {
            $unPrettyAdd = array('/ą/', '/Ą/', '/ć/', '/Ć/', '/ę/', '/Ę/', '/ł/', '/Ł/', '/ń/', '/Ń/', '/ó/', '/Ó/', '/ś/', '/Ś/', '/ź/', '/Ź/', '/ż/', '/Ż/');
            $prettyAdd = array('a', 'A', 'c', 'C', 'e', 'E', 'l', 'L', 'n', 'N', 'o', 'O', 's', 'S', 'z', 'Z', 'z', 'Z');

            $unPretty = array_merge($unPretty, $unPrettyAdd);
            $pretty = array_merge($pretty, $prettyAdd);
        }

        $string = preg_replace($unPretty, $pretty, $string);

        if ($lowerCase) {
            $string = mb_strtolower($string);
        }

        if (!$allowSpaces) {
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
}


//
//function getGeoJson($dataJson, $convert)
//{
//    $geo = array(
//        'type' => 'FeatureCollection',
////        'crs' => array(
////            'type' => 'name',
////            'properties' => array(
////                'name' => 'urn:ogc:def:crs:EPSG:2178'
////            )
////        ),
//        'features' => array()
//    );
//    $toConvert = array();
//    foreach ($dataJson->foiarray as $points) {
//        $toConvert[] = array($points->x, $points->y);
//
//        $newprop = array();
//        if (isset($points->name)) {
//            foreach (explode("\n", $points->name) as $prop) {
//                $propA = explode(':', $prop);
//                $newprop[Utils::clearString(array_shift($propA), false, false, true, false)] = trim(implode(':', $propA));
//            }
//        }
//        $geo['features'][] = array(
//            'type' => 'Feature',
//            'id' => '',
//            'geometry' => array(
//                'type' => 'Point',
//                'coordinates' => array((float)$points->x,(float)$points->y)
//            ),
//            'properties' => $newprop
//        );
//    }
//
//    if ($convert) {
//        foreach (convertGeo($toConvert) as $k => $newGeo) {
//            $geo['features'][$k]['geometry']['coordinates'] = $newGeo;
//        }
//    }
//    return $geo;
//}