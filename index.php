<?php

   
class AktivityTourRegistrace{
        private $rok_zavodu;
        private $id_zavodu;
        
        public function __construct($rok_zavodu,$id_zavodu){
            $this->rok_zavodu = $rok_zavodu;
            $this->id_zavodu = $id_zavodu;
            $this->Apidata();
            $this->Router();
        }
        
        
        private function Router(){
            $str = '<div class="container">';
            if(isset($_GET['action'])){
                switch($_GET['action']){
                    case 'vyber_podzavodu':
                        $str .= $this->VyberPodzavodu($_SESSION['apidata']);
                        if($_GET['poradi_podzavodu'] == 1){
                            $str .= $this->FormularJednotlivci($_SESSION['apidata'],true);
                        }
                        elseif($_GET['poradi_podzavodu'] == 2){
                            $str .= $this->FormularTymy($_SESSION['apidata']);
                        }
                        elseif($_GET['poradi_podzavodu'] == 3){
                            $str .= $this->FormularJednotlivci($_SESSION['apidata'],false);
                        }
                    break;
                    case 'odeslat_prihlasku' :
                        $this->OdeslatPrihlasku();
                    break;
                }
            }
            else{
                $str .= $this->VyberPodzavodu($_SESSION['apidata']);
            }
            
            $str .= "</div>"; //konec container;
            echo $str;
        }
        
        private function OdeslatPrihlasku(){
            $str = "";
            $url = 'https://api.timechip.cz/prihlasky/ulozit-prihlasku/'.$this->rok_zavodu.'/'.$this->id_zavodu.'/';
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($_POST)
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === FALSE) { /* Handle error */ }
            //var_dump($result);
            $result = json_decode($result);
            if($result->status == 'OK'){
                $str .= 'Děkujeme za přihlášení, na email '.$_POST['email'].' byla odeslána zpráva s dalšími informacemi.<br />
                V případě, že vám e-mail nepřijde (zkontrolujte si i složku s nevyžádanou poštou), nenajdete se ve výpisu přihlášek, nebo narazíte na jiný problém, kontaktujte nás prosím prostřednictvím e-mailu na <a href="mailto:info@timechip.cz">info@timechip.cz</a>';
            }
            else{
                $str .= 'Vznikl nějaký problém, kontaktujte nás prosím na prostřednictvím e-mailu na <a href="mailto:info@timechip.cz">info@timechip.cz</a>';
            }
            unset($_SESSION['apidata']);
            echo $str;
        }
        
        
        

        
      
      
      
      

        
        
        
        private function FormularJednotlivci($apidata,$tricka){
            $str = "";
            $str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=odeslat_prihlasku" id="registration_form" method="post">';
            $str .= '<input type="hidden" name="typ_prihlasky" value="1" />';
            $str .= '<input type="hidden" name="action" value="odeslat_prihlasku" />';
            $str .= '<input type="hidden" name="event_order" value="1" />';
            
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="firstname">Jméno<span class="red"> *</span></label>';
            $str .= '<input type="text" name="firstname" class="form-control required" placeholder="Firstname" value="" />';
            $str .= '</div></div>';
  
            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="surname">Příjmení<span class="red"> *</span></label>';
            $str .= '<input  type="text" name="surname" class="form-control required" placeholder="Příjmení / Surname" value="" />';
            $str .= '</div></div></div>';
            
            
            $str .= '<div class="form-group">';
            $str .= '<label for="team">Název Týmu nebo místo bydliště<span class="red"> *</span></label>';
            $str .= '<input  type="text" name="team" class="form-control required" placeholder="Oddíl nebo místo bydliště / Team or city of residence" value="" />';
            $str .= '</div>';
            
            
            
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="rok_narozeni">Rok narození / Birth Year<span class="red"> *</span></label>';
            $str .= '<select name="rok_narozeni" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Rok narození / Year of birth</option>';
            for($j=1920;$j<=2019;$j++){
                $str .= '<option value="'.$j.'" '.((isset($udaje['rok_narozeni']) && $udaje['rok_narozeni'] == $j) ? 'selected="selected"' : '').'>'.$j.'</option>';
            } 
            $str .= '</select>';
            $str .= '</div></div>';
            
            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="pohlavi">Pohlaví / Gender<span class="red"> *</span></label>';
            $str .= '<select name="pohlavi" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Pohlaví / Gender</option>';
            $str .= '<option value="M">Muž</option>';
            $str .= '<option value="Z">Žena</option>';
            $str .= '</select>';
            $str .= '</div></div></div>';
            

            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="phone1">Telefonní číslo<span class="red"> *</span></label>';
            $str .= '<input type="tel" name="phone1" class="form-control required" placeholder="Telefon / Phone" value="" />';
            $str .= '</div></div>';
            
            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="email">E-mail<span class="red"> *</span></label>';
            $str .= '<input  type="email" name="email" class="required form-control" placeholder="E-mail" value="" />';
            $str .= '</div></div></div>';
            
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="phone1">Tričko<span class="red"> *</span></label>';
            $str.= $this->VyberTricek(1,null);
            $str .= '</div></div>';

            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="email">Stát<span class="red"> *</span></label>';
            $str .= $this->SeznamStatu(1,null);

            $str .= '</div></div></div>';
            /*
            if($tricka){
                $str.= $this->VyberTricek(1,null);
            }
            $str .= $this->VyberVlny($apidata->vlny,1);*/
            $str .= '<div class="form-group">';
            $str .= '<label for="vzkaz">Vzkaz pořadateli<span class="red"> *</span></label>';
            $str .= '<textarea placeholder="Vzkaz pořadateli, máte-li nějaký / Message for the organizer" class="form-control" name="vzkaz" cols="40" rows="5"></textarea>';
            $str .= '</div>';
            $str .= '<button type="submit" class="form-control button-submit">REGISTROVAT SE A ZAPLATIT</button>';
            $str .= '</form>';
            return $str;
        }
    
    
    
        function VyberVlny($apidata,$typ_prihlasky){
            $str = "";
            $typ_prihlasky == 1 ? $name = 'vlna' : $name = 'tym_vlna'; 
            $str .= '<label>Výběr vlny<span class="povinné">*</span></label>';
            $str .= '<select name="'.$name.'">';
            $str .= '<option value="" selected disabled>Vlna</option>';
            foreach($apidata as $key => $val){
                $str .= '<option value="'.$key.'" />'.$key.', Počet volných míst = '.$val.'</option>';
            }
            $str .= '</select>';
            return $str;
         }
    
        private function VyberTricek($typ_prihlasky,$i){  
            $str = "";
            $tricka = Array();
            $tricka['S'] = 'S';
            $tricka['M'] = 'M';
            $tricka['L'] = 'L';
            $tricka['XL'] = 'XL';
            $tricka['XXL'] = 'XXL';
            $typ_prihlasky == 1 ? $name = 'tricko' : $name = 'tricko_'.$i; 
            $str .= '<select name="'.$name.'" class="form-control placeholder  required">';
            $str .= '<option value="" selected disabled>Tričko / Shirt (+250 Kč, volitelné)</option>';
            $str .= '<option value="bez">Bez trička</option>';
            foreach($tricka as $key => $val){
                $str .= '<option value="'.$val.'">'.$key.'</option>';
            }
            $str .= '</select>';
            return $str;
        }

        private function SeznamStatu($typ_prihlasky,$i){       
            $str = "";     
            $stat = Array();
            $stat['CZE'] = 'Česká republika';
            $stat['DEU'] = 'Germany';
            $stat['HUN'] = 'Hungary';
            $stat['FRA'] = 'France';
            $stat['ITA'] = 'Italy';
            $stat['KEN'] = 'Kenya';
            $stat['POL'] = 'Poland';
            $stat['KOR'] = 'Republic of Korea';
            $stat['RUS'] = 'Russia';
            $stat['SRB'] = 'Serbia';
            $stat['SVK'] = 'Slovenská republika';
            $stat['SVN'] = 'Slovenia';
            $stat['SWE'] = 'Sweden';
            $stat['UKR'] = 'Ukraine';
            $stat['GBR'] = 'United Kingdom';
            $stat['USA'] = 'United States';
            $typ_prihlasky == 1 ? $name = 'stat' : $name = 'stat_'.$i; 
            $str .= '<select name="'.$name.'" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Stát / Country</option>';
            foreach($stat as $key => $val){
                $str .= '<option value="'.$key.'">'.$val.'</option>';
            }
            $str .= '</select>';
            return $str; 
        }

        private function VyberPodzavodu($apidata) {
            $str ="";
            $poradi_podzavodu = false;
            if(isset($_GET['poradi_podzavodu'])){
                $poradi_podzavodu =  $_GET['poradi_podzavodu'];
            }
           // $str .= '<h2>Výběr závodu</h2>';
            $str .= '<div class="form-group">';
            $str .= '<label for="id_zavodu">Vyberte závod<span class="povinné"> *</span></label>';
            $str .= '<select class="form-control" id="vyber_zavodu" onchange="window.location = \'https://www.aktivitytour.cz/registrace-winter-hei-run-2?action=vyber_podzavodu&poradi_podzavodu=\'+this.options[this.selectedIndex].value+\'\';" name="vyber_zavodu">';
            $str .= '<option  value="" selected disabled>Vyberte, který se závodů chcete absolvovat</option>';
            $i = 1;
            foreach($apidata->podzavody as $data){
                $str .= '<option value="'.$data->poradi_podzavodu.'"';
                 if($i == $poradi_podzavodu){
                        $str .= ' selected="selected"';
                  }
                $str .= '">'.$data->nazev.'</option>';
                $i++;
            }
            $str .= '</select>'; 
            $str .= '</div>';
            return $str; 
        }
        
        
        private function Apidata(){
            
            $endpoint = 'https://api.timechip.cz/prihlasky/'.$this->rok_zavodu.'/'.$this->id_zavodu;
            $apifile = @file_get_contents($endpoint,NULL,NULL,0);
            $apidata = json_decode($apifile);
            $_SESSION['apidata'] = $apidata;  
        }
        
        private function FormularTymy($apidata){
            $str = "";
            $pocet_clenu_tymu = false;
            if(isset($_GET['pocet_clenu_tymu'])){
                $pocet_clenu_tymu = $_GET['pocet_clenu_tymu'];
            }
         
            $str .= '<h4>Počet členů týmu</h4>';
            $str .= '<select class="form-control" id="pocet_clenu_tymu" onchange="window.location = \'https://www.aktivitytour.cz/registrace-winter-hei-run-2?action=vyber_podzavodu&poradi_podzavodu=2&pocet_clenu_tymu=\'+this.options[this.selectedIndex].value+\'\';" name="pocet_clenu_tymu">';
            $str .= '<option value="" selected disabled>Zadejte počet členů týmu</option>';
            for($i=4;$i<=20;$i++){
                $str .= '<option value="'.$i.'"';
                if($pocet_clenu_tymu == $i){
                    $str .= ' selected="selected"';
                }
                $str .= '>'.$i.'</option>';
            }
            $str .= '</select>';
            
            $str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=odeslat_prihlasku" id="registration_form" method="post">';
            $str .= '<input type="hidden" name="typ_prihlasky" value="2" />';
            $str .= '<input type="hidden" name="pocet_clenu" value="'.$pocet_clenu_tymu.'" />';
            $str .= '<input type="hidden" name="action" value="odeslat_prihlasku" />';
            $str .= '<input type="hidden" name="event_order" value="2" />';
            if($pocet_clenu_tymu >= 4){
                $str .= '<label>Výběr kategorie <span class="povinné">*</span></label>';
                $str .= '<select name="id_kategorie" class="form-control required placeholder">';
                $str .= '<option value="" selected disabled>Výběr kategorie</option>';
                foreach($apidata->kategorie AS $val){
                    if($val->poradi_podzavodu == 2){
                        $str .= '<option value="'.$val->id_kategorie.'">'.$val->nazev_kategorie.'</option>';
                    }
                }
                $str .= '</select>';                             

                $str .= '<label for="team">Název týmu <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="nazev_tymu" class="form-control required" placeholder="Název týmu" value="';
                $str .= (isset($_SESSION['nazev_tymu'])) ? ($_SESSION['nazev_tymu']) : ('');
                $str .= '" />';

                $str .= '<label for="jmeno">Jméno kapitána týmu <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="jmeno" class="form-control required" placeholder="Jméno kapitána týmu" value="';
                $str .= (isset($_SESSION['jmeno'])) ? ($_SESSION['jmeno']) : ('');
                $str .= '" />';

                $str .= '<label for="prijmeni">Příjmení kapitána týmu <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="prijmeni" class="form-control required" placeholder="Příjmení kapitána týmu" value="';
                $str .= (isset($_SESSION['prijmeni'])) ? ($_SESSION['prijmeni']) : ('');
                $str .= '" />';

                $str .= '<label for="email">E-mail <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="email" class="form-control required" placeholder="E-mail" value="';
                $str .= (isset($_SESSION['email'])) ? ($_SESSION['email']) : ('');
                $str .= '" />';

                $str .= '<label for="telefon_1">Telefon <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="telefon_1" class="form-control required" placeholder="Telefon" value="';
                $str .= (isset($_SESSION['telefon_1'])) ? ($_SESSION['telefon_1']) : ('');
                $str .= '" />';
                
                $str .= $this->VyberVlny($apidata->vlny,2);






                for($i = 1;$i <=  $pocet_clenu_tymu;$i++){

                    $str .= '<h4>Zavodnik '.$i. '</h4>';
                    $str .= '<label for="firstname_'.$i.'">Jméno<span class="povinné">*</span></label>';
                    $str .= '<input type="text" name="firstname_'.$i.'" class="form-control required" placeholder="Jméno" value="';
                    $str .= (isset($_SESSION['firstname_'.$i])) ? ($_SESSION['firstname_'.$i]) : ('');
                    $str .= '" />';

                    $str .= '<label for="surname_'.$i.'">Příjmení<span class="povinné">*</span></label>';
                    $str .= '<input type="text" name="surname_'.$i.'" class="form-control required" placeholder="Příjmení" value="';
                    $str .= (isset($_SESSION['surname_'.$i])) ? ($_SESSION['surname_'.$i]) : ('');
                    $str .= '" />';

                    $str .=  '<label for="rok_narozeni_'.$i.'">Rok narození<span class="povinné">*</span></label>';
                    $str .=  '<select name="rok_narozeni_'.$i.'" class="form-control required placeholder">';  
                    $str .=  '<option value="" selected disabled>Rok narození</option>';  
                    for($k=1920;$k<=2020;$k++){
                        $str .= '<option value="'.$k.'" '.((isset($udaje['rok_narozeni_'.$i]) && $udaje['rok_narozeni_'.$i] == $k) ? 'selected="selected"' : '').'>'.$k.'</option>';
                    }
                    $str .= '</select>';
                    $str .= '<label for="pohlavi_'.$i.'">Pohlavi<span class="povinné">*</span></label>';
                    $str .= '<select name="pohlavi_'.$i.'" class="form-control required placeholder">';  
                    $str .= '<option value="">Pohlaví</option>';  
                    $str .= '<option value="M"'.((isset($udaje['pohlavi_'.$i]) && $udaje['pohlavi_'.$i] == 'M') ? ' selected="selected"' : '').'>Muž</option>';  
                    $str .= '<option value="Z"'.((isset($udaje['pohlavi_'.$i]) && $udaje['pohlavi_'.$i] == 'Z') ? ' selected="selected"' : '').'>Žena</option>';  
                    $str .= '</select>';
                    $str .= '<label for="mail_'.$i.'">E-mail<span class="povinné">*</span></label>';
                    $str .= '<input type="text" name="mail_'.$i.'" class="form-control required" placeholder="E-mail" value="" />';

                    $str .= '<label for="stat_'.$i.'">Stát<span class="povinné">*</span></label>';
                    $str .= $this->SeznamStatu(2,$i);
                    $str .= '<label for="tricko_'.$i.'">Tričko<span class="povinné">*</span></label>';
                    $str.= $this->VyberTricek(2,$i);

                  }
                $str .= '<textarea placeholder="Vzkaz pořadateli, máte-li nějaký / Message for the organizer" class="form-control" name="vzkaz" cols="40" rows="5"></textarea>';
                $str .= '<button type="submit" class="form-control btn btn-primary">Odeslat</button>';
                $str .= '</form>';

              }
            return $str;
          }
          

        
        
    
  }   
    
    new AktivityTourRegistrace(2020,11);
        
?>