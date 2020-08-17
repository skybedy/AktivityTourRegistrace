<?php
    $udaje_zavodu = Array();
    $udaje_zavodu['rok_zavodu'] = 2020;
    $udaje_zavodu['id_zavodu'] = 11;
    $udaje_zavodu['nazev_zavodu'] = 'beh-novojicinskym-parkem-2020';
    $udaje_zavodu['stranka'] = 'registrace-'.$udaje_zavodu['nazev_zavodu'];
    $udaje_zavodu['vseobecne_podminky'] = false;
    $udaje_zavodu['pravidla_podminky'] = NULL;
    $udaje_podzavodu = Array();
    $udaje_podzavodu[1] = Array('typ_prihlasky' => 1,'tricka' => 0,'vlny' => 0);
    $udaje_podzavodu[2] = Array('typ_prihlasky' => 1,'tricka' => 0,'vlny' => 0);
    
    new AktivityTourRegistrace($udaje_zavodu,$udaje_podzavodu);
?>