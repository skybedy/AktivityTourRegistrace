<?php

   
class AktivityTourVypisPrihlasek{
    private $udaje_zavodu;
    private $udaje_podzavodu;
        
        public function __construct(){
            $udaje_zavodu = Array();
            $udaje_zavodu['rok_zavodu'] = 2020;
            $udaje_zavodu['id_zavodu'] = 11;
            $udaje_zavodu['nazev_zavodu'] = 'beh-novojicinskym-parkem-2020';
            $udaje_zavodu['stranka'] = 'registrace-'.$udaje_zavodu['nazev_zavodu'].'-test';
            $udaje_zavodu['vseobecne_podminky'] = 'https://api.timechip.cz/public/doc/'.$udaje_zavodu['nazev_zavodu'].'_vseobecne_podminky.pdf';
            $udaje_zavodu['pravidla_podminky'] = 'https://api.timechip.cz/public/doc/'.$udaje_zavodu['nazev_zavodu'].'_pravidla_podminky.pdf';
            $udaje_podzavodu = Array();
            $udaje_podzavodu[1] = Array('typ_prihlasky' => 1,'tricka' => 0,'vlny' => 0);
            $udaje_podzavodu[2] = Array('typ_prihlasky' => 1,'tricka' => 0,'vlny' => 0);
            $this->VypisPrihlasek();
            
        }
        
        private function Apidata(){
            $endpoint = 'https://api.timechip.cz/prihlasky/vypis-prihlasek/2020/11';
            $apifile = @file_get_contents($endpoint,NULL,NULL,0);
            $apidata = json_decode($apifile);
            return $apidata;
        }
        
       
        
        private function VypisPrihlasek(){
         
            $apidata = $this->Apidata();
            $str = "";
            foreach($apidata AS $foreval){
                $startovni_cas = '';
                
                if($foreval2->vlna){
                    $startovni_cas =  '<td>Startovní čas</td>';
                }
                
                
                
                if($foreval->typ_zavodnika == 1){
                    $str .= '<h4>'.$foreval->nazev_podzavodu.'</h4>';
                    $str .= '<table>';
                    
                    $tdvlny = "";
                    if($this->udaje_podzavodu[$val->poradi_podzavodu]['vlny'] == 1){
                        $tdvlny = '<th>Vlna</th>';
                    }
                    $str .= '<thead><tr><th>Jméno</th><th>Tým/Bydliště</th><th>Kategorie</th>'.$tdvlny.'<th>Startovné</th></tr></thead>';
                    foreach($foreval->jednotlivci AS $foreval2){
                        $str .= '<tr>';
                        $str .=  '<td>'.$foreval2->prijmeni_1.' '.$foreval2->jmeno_1.'</td>';
                        $str .=  '<td>'.$foreval2->prislusnost.'</td>';
                        $str .=  '<td>'.$foreval2->nazev_kategorie.'</td>';
                        if($foreval2->vlna){
                          $str .=  '<td>'.$foreval2->vlna.'</td>';
                        }
                        $str .=  '<td>'.$foreval2->startovne.'</td>';
                        $str .= '</tr>';
                    }
                  $str .= '</table>';
                }
                
                elseif($foreval->typ_zavodnika == 2){
                //print_r($foreval);
                $str .= '<h4>'.$foreval->nazev_podzavodu.'</h4>';
                $str .= '<table>';
                  $str .= '<thead><tr><th>Tým</th><th>Kategorie</th><th>Členové týmu</th><th>Start</th><th>Startovné</th></tr></thead>';
                  foreach($foreval->tymy AS $foreval3){
                      $i = 1; 
                      foreach($foreval3->clenove_tymu AS $foreval4){
                      if($i == 1){
                          $str .= '<tr>';
                          $str .= '<td rowspan="'.$foreval3->pocet_clenu_tymu.'" class="rowspan">'.$foreval3->nazev_tymu.'</td>';
                          $str .= '<td rowspan="'.$foreval3->pocet_clenu_tymu.'" class="rowspan">'.$foreval3->nazev_kategorie.'</td>';
                          $str .= '<td>'.$foreval4->jmeno_1.' '.$foreval4->prijmeni_1.'</td>';
                          $str .= '<td rowspan="'.$foreval3->pocet_clenu_tymu.'" class="rowspan">'.$foreval3->startovni_cas.'</td>';
                          $str .= '<td rowspan="'.$foreval3->pocet_clenu_tymu.'" class="rowspan">'.$foreval3->zaplaceno.'</td>';
                          $str .= '</tr>';
                         }
                         else{
                          $str .= '<tr>';
                          $str .= '<td>'.$foreval4->jmeno_1.' '.$foreval4->prijmeni_1.'</td>';
                          $str .= '</tr>';

                         }
                        $i++;
                     }

                    //$str .= $foreval2->nazev_tymu;    




                }
                $str .= '</table>';
                }

            }

            echo $str;
              
          }

        
        
    
  }   
    
    new AktivityTourVypisPrihlasek();
        
?>