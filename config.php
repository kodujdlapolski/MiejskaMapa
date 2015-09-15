<?php
return $config = array(
    'saver' => array(
        'mongo' => array(
            'server' => 'mongodb://localhost:27017',
            'db' => 'maps',
            'collection' => 'warszawa'
        )
    ),
    'reader' => array(
        'basic' => array(
            'tmp_dir_path' => '../tmp',
            'tmp_file_in' => 'data.in',
            'tmp_file_out' => 'data.out',
            'convert_enabled' => true,
//    'convert_cmd' => 'proj +proj=tmerc +lat_0=0 +lon_0=18.95833333333333 +k=0.999983 +x_0=237000 +y_0=-4700000 +ellps=krass +towgs84=33.4,-146.6,-76.3,-0.359,-0.053,0.844,-0.84 +units=m +no_defs  -f %12.6f -I',
            'convert_cmd' => 'proj +proj=tmerc +lat_0=0 +lon_0=21 +k=0.999923 +x_0=7500000 +y_0=0 +ellps=GRS80 +units=m +no_defs -f %12.6f -I'
        ),
        'warszawa_um' => array(
            'url' => 'http://mapa.um.warszawa.pl',
            'url_param' => '/mapviewer/foi?request=getfoi&version=1.0&bbox=7486857.110767802:5775420.213250488:7518720.091537033:5802523.578635103&width=965&height=821&clickable=yes&area=yes&dstsrid=2178&cachefoi=yes&tid=98_841140&aw=no&theme=dane_wawa.',
            'type_map' => array(
                'publiczne_cmentarze' => 'REL_CMEN_TOOLTIP',
                'hydro' => 'HYDRO_WODA_100_LETNIA',
                'handel_stanowiska' => 'HANDEL_STANOWISKA_POINT_1_7',
                'handel_targowiska' => 'HANDEL_TARGOWISKA_POINT',
                'religia_koscioly' => 'REL_KOSCIOLY_KAPLICE',

                'publiczne_ambasady' => 'A_AMBASADY',
                'publiczne_biura_urzedu' => 'A_BIURA_URZEDU',
                'publiczne_jednostki' => 'A_INNE_JEDNOSTKI_PUBL',
                'publiczne_jednostki_urzedu' => 'A_JEDNOSTKI_ORG_URZEDU',
                'publiczne_konsulaty' => 'A_KONSULATY',
                'publiczne_poczta' => 'A_PLACOWKI_POCZTOWE',
                'publiczne_zus' => 'A_PLACOWKI_ZUS',
                'publiczne_sady' => 'A_SADY',
                'publiczne_urzedy_dzielnic' => 'A_URZEDY_DZIELNIC',
                'publiczne_usc' => 'A_USC',
                'publiczne_urzedy_skarbowe' => 'A_URZEDY_SKARBOWE',

                'sluzby_policja' => 'BEZP_POLICJA',
                'sluzby_straz_miejska' => 'BEZP_STRAZ_MIEJSKA',
                'sluzby_straz_miejska_zasieg' => 'BEZP_STRAZ_MIEJSKA_ZASIEGI',
                'sluzby_straz_pozarna' => 'BEZP_PSP',

                'szkola_uczelnie' => 'E_UCZELNIE_WYZSZE',
                'szkola_zawodowe' => 'E_SZK_ZAWODOWE',
                'szkola_technika' => 'E_TECHNIKA',
                'szkola_podstawowe' => 'E_SZK_PODSTAWOWE',
                'szkola_licea' => 'E_LICEA',
                'szkola_policealne' => 'E_SZK_POLICEALNE',
                'szkola_przedszkola' => 'E_PRZEDSZKOLA',
                'szkola_gimnazja' => 'E_GIMNAZJA',

                'rowery_ibombo' => 'ROWERY_IBOMBO',
                'rowery_serwis' => 'ROWERY_SERWISY_ROWEROWE',
                'rowery_stacje' => 'ROWERY_STACJE_ROWEROWE',
                'rowery_stojaki' => 'ROWERY_STOJAKI_ROWEROWE',
                'rowery_towarowe' => 'ROWERY_TOWAROWE'
            )
        )
    ),
    'default_format' => 'json'
);