<?php
    $udaje_zavodu = Array();
    $udaje_zavodu['rok_zavodu'] = 2020;
    $udaje_zavodu['id_zavodu'] = 11;
    $udaje_zavodu['nazev_zavodu'] = 'hei-run-2020';
    $udaje_zavodu['stranka'] = 'registrace-'.$udaje_zavodu['nazev_zavodu'];
    $udaje_zavodu['vseobecne_podminky'] = 'https://api.timechip.cz/public/doc/'.$udaje_zavodu['nazev_zavodu'].'_vseobecne_podminky.pdf';
    $udaje_zavodu['pravidla_podminky'] = 'https://api.timechip.cz/public/doc/'.$udaje_zavodu['nazev_zavodu'].'_pravidla_podminky.pdf';
    $udaje_podzavodu = Array();
    $udaje_podzavodu[1] = Array('typ_prihlasky' => 1,'tricka' => 1,'vlny' => 1);
    $udaje_podzavodu[2] = Array('typ_prihlasky' => 2,'tricka' => 1,'vlny' => 1);
    $udaje_podzavodu[3] = Array('typ_prihlasky' => 1,'tricka' => 0,'vlny' => 0);
    
    new AktivityTourRegistrace($udaje_zavodu,$udaje_podzavodu);
?>