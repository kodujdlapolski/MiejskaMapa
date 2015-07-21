<?php
return $config = array(
    'url' => 'http://mapa.um.warszawa.pl',
    'url_param' => '/mapviewer/foi?request=getfoi&version=1.0&bbox=7486857.110767802:5775420.213250488:7518720.091537033:5802523.578635103&width=965&height=821&clickable=yes&area=yes&dstsrid=2178&cachefoi=yes&tid=98_841140&aw=no&theme=dane_wawa.',
    'tmp_dir_path' => '../tmp',
    'convert_enabled' => true,
//    'convert_cmd' => 'proj +proj=tmerc +lat_0=0 +lon_0=18.95833333333333 +k=0.999983 +x_0=237000 +y_0=-4700000 +ellps=krass +towgs84=33.4,-146.6,-76.3,-0.359,-0.053,0.844,-0.84 +units=m +no_defs  -f %12.6f -I',
    'convert_cmd' => 'proj +proj=tmerc +lat_0=0 +lon_0=21 +k=0.999923 +x_0=7500000 +y_0=0 +ellps=GRS80 +units=m +no_defs -f %12.6f -I',
    'type_map' => array(
        'cmentarze' => 'REL_CMEN_TOOLTIP',
        'hydro' => 'HYDRO_WODA_100_LETNIA',
        'handel' => 'HANDEL_STANOWISKA_POINT_1_7',
        'targowiska' => 'HANDEL_TARGOWISKA_POINT',

        'ambasady' => 'A_AMBASADY',
        'biura_urzedu' => 'A_BIURA_URZEDU',
        'jednostki_publiczne' => 'A_INNE_JEDNOSTKI_PUBL',
        'jednostki_urzedu' => 'A_JEDNOSTKI_ORG_URZEDU',
        'konsulaty' => 'A_KONSULATY',
        'poczta' => 'A_PLACOWKI_POCZTOWE',
        'zus' => 'A_PLACOWKI_ZUS',
        'sady' => 'A_SADY',
        'urzedy_dzielnic' => 'A_URZEDY_DZIELNIC',
        'usc' => 'A_USC',
        'urzedy_skarbowe' => 'A_URZEDY_SKARBOWE',

        'policja' => 'BEZP_POLICJA',
        'straz_miejska' => 'BEZP_STRAZ_MIEJSKA',
        'straz_miejska_zasieg' => 'BEZP_STRAZ_MIEJSKA_ZASIEGI',
        'straz_pozarna' => 'BEZP_PSP',

        'uczelnie_wyzsze' => 'E_UCZELNIE_WYZSZE',
        'zawodowe' => 'E_SZK_ZAWODOWE',
        'technika' => 'E_TECHNIKA',
        'podstawowe' => 'E_SZK_PODSTAWOWE',
        'licea' => 'E_LICEA',
        'policealne' => 'E_SZK_POLICEALNE',
        'przedszkola' => 'E_PRZEDSZKOLA',
        'gimnazja' => 'E_GIMNAZJA',
    ),
    'default_format' => 'json'
);