<?php

   
class AktivityTourRegistrace{
        private $rok_zavodu;
        private $id_zavodu;
        private $podzavod = Array();
        private $vseobecne_podminky_url;
        private $pravidla_podminky_url;
        private $stranka;
        private $cislo_uctu = '5588644001/5500';
        
        public function __construct($udaje_zavodu,$udaje_podzavodu){
            //na produkci odstranit !!!!!!
           // ini_set('display_errors', 1);
            //ini_set('display_startup_errors', 1);
            //error_reporting(E_ALL);
           
            
            session_start();
            $this->rok_zavodu = $udaje_zavodu['rok_zavodu'];
            $this->id_zavodu = $udaje_zavodu['id_zavodu'];
            $this->vseobecne_podminky_url = $udaje_zavodu['vseobecne_podminky']; 
            $this->pravidla_podminky_url = $udaje_zavodu['pravidla_podminky']; 
            $this->stranka = $udaje_zavodu['stranka'];
            if(isset($_GET['poradi_podzavodu'])){
                $this->podzavod = $udaje_podzavodu[$_GET['poradi_podzavodu']];
            }
            $this->Apidata();
            $this->Router(); 
        }
        
        
        private function Router(){
            $str = '<div class="container-fluid">';
            if(isset($_GET['action'])){
                switch($_GET['action']){
                    case 'vyber_podzavodu':
                        $str .= $this->VyberPodzavodu($_SESSION['apidata']);
                        if($this->podzavod['typ_prihlasky'] == 1){
                            $str .= $this->FormularJednotlivci($_SESSION['apidata']);
                        }
                        elseif($this->podzavod['typ_prihlasky'] == 2){
                            $str .= $this->FormularTymy($_SESSION['apidata']);
                        }
                    break;
                    case 'zkontrolovat_prihlasku':
                        switch ($_POST['typ_prihlasky']){
                            case 1;
                                $this->KontrolaPrihlaskyJednotlivci();
                            break;
                            case 2;
                                $this->KontrolaPrihlaskyTym();
                            break;
                        }
                        
                    break;
                    case 'ulozeni_na_server' :
                        $this->UlozeniNaServer();
                        
                        
                        //$this->Comgate($this->KontrolaStartovneho());
                    break;
                }
            }
            else{
                $str .= $this->VyberPodzavodu($_SESSION['apidata']);
            }
            
            $str .= "</div>"; //konec container;
            echo $str;
        }
        
        private function PrevodGlobalniPromnenne($globalni_promenna){
            return $globalni_promenna;
        }
        
        
        private function UlozeniNaServer(){
            $str = "";
            isset($_GET['typ_prihlasky']) ? $typ_prihlasky = $this->PrevodGlobalniPromnenne($_GET['typ_prihlasky']) : $typ_prihlasky = false;
            
            if($typ_prihlasky == 1){
                $data = 'data_jednotlivec';
            }
            elseif($typ_prihlasky == 2){
                $data = 'data_tym';
            }
            //print_r($_SESSION[$data]);
            
            $url = 'https://api.timechip.cz/prihlasky/ulozit-prihlasku/'.$this->rok_zavodu.'/'.$this->id_zavodu.'/';
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($_SESSION[$data])
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === FALSE) { /* Handle error */ }
          //  var_dump($result);
           // print_r($result);
            $result = json_decode($result);
            if($result->status == 'OK'){
                $id_prihlasky = $result->id_prihlasky;
                if($typ_prihlasky == 1){
                   $_SESSION['data_jednotlivec']['id_prihlasky'] = $id_prihlasky; 
                }
                elseif($typ_prihlasky == 2){
                   $_SESSION['data_tym']['id_prihlasky'] = $id_prihlasky; 
                }
                
                //$str .= 'Děkujeme za přihlášení, na email '.$_POST['email'].' byla odeslána zpráva s dalšími informacemi.<br />
                //V případě, že vám e-mail nepřijde (zkontrolujte si i složku s nevyžádanou poštou), nenajdete se ve výpisu přihlášek, nebo narazíte na jiný problém, kontaktujte nás prosím prostřednictvím e-mailu na <a href="mailto:info@timechip.cz">info@timechip.cz</a>';
                header('location: https://aktivitytour.cz/wp-content/plugins/insert-php-code-snippet/comgate/payment.php?method=ALL&email='.$_SESSION[$data]['email'].'&price='.$_SESSION[$data]['castka'].'&currency='.$_SESSION[$data]['mena'].'&id_prihlasky='.$id_prihlasky.'&typ_prihlasky='.$typ_prihlasky.'&rok_zavodu='.$this->rok_zavodu.'&id_zavodu='.$this->id_zavodu);
            }
            else{
                $str .= 'Vznikl nějaký problém, kontaktujte nás prosím na prostřednictvím e-mailu na <a href="mailto:info@timechip.cz">info@timechip.cz</a>';
            }
            //session_destroy(); // na produkci
            echo $str;
        }
        
        private function StartovneJednotlivec(){
            $result = Array();
            isset($_POST['tricko']) ? $tricko = '/'.$_POST['tricko'] : $tricko = "";
            $url = 'https://api.timechip.cz/prihlasky/startovne/jednotlivec/'.$this->rok_zavodu.'/'.$this->id_zavodu.'/'.$_POST['event_order'].$tricko;
            $result = file_get_contents($url);
            if ($result === FALSE) { /* Handle error */ }
            $result = json_decode($result,true);
            $_SESSION['data_jednotlivec']['castka'] = $result['castka'];
            $_SESSION['data_jednotlivec']['mena'] = $result['mena'];
            return $result;
        }
        
        private function StartovneTym(){
            $result = Array();
            $tricka = "";
            for($i = 1;$i <= $_POST['pocet_clenu'];$i++){
                $tricka .= '&tricko_'.$i.'='.$_POST['tricko_'.$i];
            }
            $url = 'https://api.timechip.cz/prihlasky/startovne/tym/'.$this->rok_zavodu.'/'.$this->id_zavodu.'/'.$_POST['event_order'].'?pocet_clenu='.$_POST['pocet_clenu'].$tricka;
            $result = file_get_contents($url);
            if ($result === FALSE) { /* Handle error */ }
            $result = json_decode($result,true);
            $_SESSION['data_tym']['castka'] = $result['castka'];
            $_SESSION['data_tym']['mena'] = $result['mena'];
            return $result;
        }
        
        
        
        
        private function NazevKategorie(){
            $str = "";
            foreach($_SESSION['apidata']->kategorie AS $val){
                if(isset($_POST['id_kategorie'])){
                    if($val->id_kategorie == $_POST['id_kategorie']){
                         $str .= $val->nazev_kategorie;
                    }
                }
            }
            return $str;
        }
        
        
        
        
        private function KontrolaPrihlaskyTym(){
            $_SESSION['data_tym'] = $_POST;
            $startovne = Array();
            $startovne = $this->StartovneTym();
            $str = '<table class="table table-striped">';
            if(isset($_POST['nazev_tymu'])) $str .= $this->NevimJak("Tým",$_POST['nazev_tymu']);
            if(isset($_POST['nazev_kategorie'])) $str .= $this->NevimJak("Kategorie",$_POST['nazev_kategorie']);
            if($this->NazevKategorie()) $str .= $this->NevimJak("Kategorie",$this->NazevKategorie());
            
            if(isset($_POST['jmeno'])) $str .= $this->NevimJak("Jméno kapitána",$_POST['jmeno']);
            if(isset($_POST['prijmeni'])) $str .= $this->NevimJak("Příjmení kapitána",$_POST['prijmeni']);
            if(isset($_POST['email'])) $str .= $this->NevimJak("E-mail",$_POST['email']);
            if(isset($_POST['telefon_1'])) $str .= $this->NevimJak("Telefon",$_POST['telefon_1']);
            if(isset($_POST['vlna'])) $str .= $this->NevimJak("Vlna",$_POST['vlna']);

            for($i = 1;$i <= $_POST['pocet_clenu'];$i++){
                $str .= '<tr><td colspan = "2">Zavodník '.$i."</td></tr>";
                if(isset($_POST['firstname_'.$i])) $str .= $this->NevimJak("Jméno",$_POST['firstname_'.$i]);
                if(isset($_POST['surname_'.$i])) $str .= $this->NevimJak("Příjmení",$_POST['surname_'.$i]);
                if(isset($_POST['rok_narozeni_'.$i])) $str .= $this->NevimJak("Ročník",$_POST['rok_narozeni_'.$i]);
                if(isset($_POST['pohlavi_'.$i])) $str .= $this->NevimJak("Pohlaví",$_POST['pohlavi_'.$i]);
                if(isset($_POST['mail_'.$i])) $str .= $this->NevimJak("E-mail",$_POST['mail_'.$i]);
                if(isset($_POST['stat_'.$i])) $str .= $this->NevimJak("Stát",$_POST['stat_'.$i]);
                if(isset($_POST['tricko_'.$i])) $str .= $this->NevimJak("Tričko",$_POST['tricko_'.$i]);
            }
            $str .= '<tr><td>Startovné</td><td>'.$startovne['castka'].' '.$startovne['mena'].'</td></tr>';
            $str .= '</table>';
            $str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=vyber_podzavodu&poradi_podzavodu='.$_POST['event_order'].'&pocet_clenu_tymu='.$_POST['pocet_clenu'].'" method="post"><button type="submit" class="form-control btn btn-danger">Opravit</button></form>';
            //$str .= '<form style="margin-top:10px" action="https://aktivitytour.cz/wp-content/plugins/insert-php-code-snippet/comgate/payment.php" method="post">';
            $str .= '<form style="margin-top:10px" action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=ulozeni_na_server&typ_prihlasky='.$_POST['typ_prihlasky'].'" id="" method="post">';
            $str .= '<input type="hidden" name="method" value="ALL" />';
            $str .= '<input type="hidden" name="price" value="'.$startovne['castka'].'" />';
            $str .= '<input type="hidden" name="currency" value="'.$startovne['mena'].'" />';
            $str .= '<input type="hidden" name="email" value="'.$_POST['email'].'" />';
            $str .= '<button type="submit" class="form-control btn btn-success">Zaregistrovat a zaplatit</button></form>';
            echo $str;
        }
        
        
        
        
        
        
        
        private function KontrolaPrihlaskyJednotlivci(){
            $_SESSION['data_jednotlivec'] = $_POST;
            $startovne = Array();
            $startovne = $this->StartovneJednotlivec();
            $str = '<table class="table table-striped">';
            if(isset($_POST['firstname'])) $str .= $this->NevimJak("Jméno",$_POST['firstname']);
            if(isset($_POST['surname'])) $str .= $this->NevimJak("Příjmení",$_POST['surname']);
            if(isset($_POST['pohlavi'])) $str .= $this->NevimJak("Pohlaví",$_POST['pohlavi']);
            if(isset($_POST['team'])) $str .= $this->NevimJak("Tým/Bydliště",$_POST['team']);
            if(isset($_POST['rok_narozeni'])) $str .= $this->NevimJak("Ročník",$_POST['rok_narozeni']);
            if(isset($_POST['stat'])) $str .= $this->NevimJak("Stát",$_POST['stat']);
            if(isset($_POST['tricko'])) $str .= $this->NevimJak("Tricko",$_POST['tricko']);
            if(isset($_POST['vlna'])) $str .= $this->NevimJak("Vlna",$_POST['vlna']);
            if(isset($_POST['phone1'])) $str .= $this->NevimJak("Telefon",$_POST['phone1']);
            if(isset($_POST['email'])) $str .= $this->NevimJak("E-mail",$_POST['email']);
            $str .= '<tr><td>Startovné</td><td>'.$startovne['castka'].' '.$startovne['mena'].'</td></tr>';
            $str .= '</table>';
            $str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=vyber_podzavodu&poradi_podzavodu='.$_POST['event_order'].'" method="post"><button type="submit" class="form-control btn btn-danger">Opravit</button></form>';


            $str .= '<form style="margin-top:10px" action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=ulozeni_na_server&typ_prihlasky='.$_POST['typ_prihlasky'].'" id="" method="post">';
            

//$str .= '<form style="margin-top:10px" action="https://aktivitytour.cz/wp-content/plugins/insert-php-code-snippet/comgate/payment.php" method="post">';
            $str .= '<input type="hidden" name="method" value="ALL" />';
            $str .= '<input type="hidden" name="price" value="'.$startovne['castka'].'" />';
            $str .= '<input type="hidden" name="currency" value="'.$startovne['mena'].'" />';
            $str .= '<input type="hidden" name="email" value="'.$_POST['email'].'" />';
            $str .= '<button type="submit" class="form-control btn btn-success">Zaregistrovat a zaplatit</button></form>';
            echo $str;
        }
        
        
        
        
        
        
        
        private function NevimJak($item,$val){
            $str = "";
            if($item == 'Pohlaví'){
                if($val == "M"){
                    $val = 'Muž';
                }
                else{
                    $val = "Žena";
                }
            }
            $str .=(!empty($val)) ? ("<tr><td>$item</td><td>$val</td></tr>") : ("");
            return $str;
        }

        
        private function FormVal($val){
            $str = "";
            $str .= (isset($val) ? ($val) : (''));
            echo $val;
        }


       // private function FormularJednotlivci($apidata,$tricka,$vlny){
        private function FormularJednotlivci($apidata){
          //  print_r($_SESSION);
            $str = "";
            $data_jednotlivec = Array();
            (isset($_SESSION['data_jednotlivec'])) ? ($data_jednotlivec = $_SESSION['data_jednotlivec']) : ($data_jednotlivec = NULL);
            $str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=zkontrolovat_prihlasku&poradi_podzavodu='.$_GET['poradi_podzavodu'].'" id="registration_form" method="post">';
           // $str .= '<form action="https://aktivitytour.cz/wp-content/plugins/insert-php-code-snippet/comgate/payment.php" id="registration_form" method="post">';
            $str .= '<input type="hidden" name="typ_prihlasky" value="1" />';
            $str .= '<input type="hidden" name="neposilat_potvrzovaci_mail" value="true" />';
            $str .= '<input type="hidden" name="event_order" value="'.$_GET['poradi_podzavodu'].'" />';
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="firstname">Jméno<span class="red"> *</span></label>';
            $str .= '<input type="text" name="firstname" class="form-control required" placeholder="Firstname" value="'.((isset($data_jednotlivec['firstname'])) ? ($data_jednotlivec['firstname']) : ("")).'" />';
            $str .= '</div></div>';
            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="surname">Příjmení<span class="red"> *</span></label>';
            $str .= '<input  type="text" name="surname" class="form-control required" placeholder="Příjmení / Surname" value="'.((isset($data_jednotlivec['surname'])) ? ($data_jednotlivec['surname']) : ("")).'" />';
            $str .= '</div></div></div>';
            $str .= '<div class="form-group">';
            $str .= '<label for="team">Název Týmu nebo místo bydliště<span class="red"> *</span></label>';
            $str .= '<input  type="text" name="team" class="form-control required" placeholder="Oddíl nebo místo bydliště / Team or city of residence" value="'.((isset($data_jednotlivec['team'])) ? ($data_jednotlivec['team']) : ("")).'" />';
            $str .= '</div>';
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="rok_narozeni">Rok narození / Birth Year<span class="red"> *</span></label>';
            $str .= '<select name="rok_narozeni" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Rok narození / Year of birth</option>';
            for($j=1921;$j<=2019;$j++){
                $str .= '<option value="'.$j.'" '.((isset($data_jednotlivec['rok_narozeni']) && $data_jednotlivec['rok_narozeni'] == $j) ? 'selected="selected"' : '').'>'.$j.'</option>';
            } 
            $str .= '</select>';
            $str .= '</div></div>';
            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="pohlavi">Pohlaví / Gender<span class="red"> *</span></label>';
            $str .= '<select name="pohlavi" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Pohlaví / Gender</option>';
            $str .= '<option value="M" '.((isset($data_jednotlivec['pohlavi']) && $data_jednotlivec['pohlavi'] == 'M') ? ('selected="selected"') : ('')).'>Muž</option>';
            $str .= '<option value="Z" '.((isset($data_jednotlivec['pohlavi']) && $data_jednotlivec['pohlavi'] == 'Z') ? ('selected="selected"') : ('')).'>Žena</option>';
            $str .= '</select>';
            $str .= '</div></div></div>';
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="phone1">Telefonní číslo<span class="red"> *</span></label>';
            $str .= '<input type="tel" name="phone1" class="form-control required" placeholder="Telefon / Phone" value="'.((isset($data_jednotlivec['phone1'])) ? ($data_jednotlivec['phone1']) : ("")).'" />';
            $str .= '</div></div>';
            $str .= '<div class="col-md-6"><div class="form-group">';
            $str .= '<label for="email">E-mail<span class="red"> *</span></label>';
            $str .= '<input  type="email" name="email" class="required form-control" placeholder="E-mail" value="'.((isset($data_jednotlivec['email'])) ? ($data_jednotlivec['email']) : ("")).'" />';
            $str .= '</div></div></div>';
            $str .= '<div class="row"><div class="col-md-6"><div class="form-group">';
            $str .= '<label for="">Stát<span class="red"> *</span></label>';
            $str .= $this->SeznamStatuJednotlivec();
            $str .= '</div></div>';
            if($this->podzavod['tricka']){
                $str .= '<div class="col-md-6"><div class="form-group">';
                $str .= '<label for="email">Tričko<span class="red"> *</span></label>';
                $str.= $this->VyberTricekJednotlivec($this->podzavod['typ_prihlasky'], null);
                $str .= '</div></div>';
            }
            $str .= '</div>';
            
   
            
            if($this->podzavod['vlny']){
                //je třeba dodělat
                $str .= $this->VyberVlny($apidata->vlny,$this->podzavod['typ_prihlasky']);           
                
            }
            
            $str .= '<hr>';
            $str .= '<div class="checkbox"><label><input type="checkbox" name="souhlas_osobni_udaje" class="required" /> Souhlasím s poskytnutím osobních údajů pro potřeby této registrace</label></div>';
          //  if(!empty($this->vseobecne_podminky_url)){
           //     $str .= '<div class="checkbox"><label><input type="checkbox" name="souhlas_vseobecne_podminky" class="required" /> Souhlasím se všeobecnými podmínkami, ke stažení <a target="_blank" href="'.$this->vseobecne_podminky_url.'">ZDE</a></label></div>';
            //}
            //$str .= '<div class="checkbox"><label><input type="checkbox" name="souhlas_podminky_zavodu" class="required" /> Souhlasím s pravidly a podmínkami, ke stažení <a target="_blank"  href="'.$this->pravidla_podminky_url.'">ZDE</a></label></div>';
            $str .= '<hr>';
            //$str .= '<div class="checkbox"><label><input type="checkbox" name="" class="required" /> ONLINE platba kartou</label></div>';
            //$str .= '<div class="checkbox"><label><input type="checkbox" name="" class="required" /> Zaplatit bankovním převodem</label></div>';
            //$str .= '<hr>';
            $str .= '<div class="form-group">';
            $str .= '<label for="vzkaz">Vzkaz pořadateli</label>';
            $str .= '<textarea placeholder="Vzkaz pořadateli, máte-li nějaký / Message for the organizer" class="form-control" name="vzkaz" cols="40" rows="5"></textarea>';
            $str .= '</div>';
            //$str .= '<button type="submit" class="form-control button-submit">REGISTROVAT SE A ZAPLATIT</button>';
            $str .= '<button type="submit" class="form-control button-submit">POKRAČOVAT</button>';
            $str .= '</form>';
            return $str;
        }
    
    
    
        function VyberVlny($apidata,$typ_prihlasky){ //pokud bude fungovat i pro tymy, upravit
            $str = "";
            $typ_prihlasky == 1 ? $name = 'vlna' : $name = 'tym_vlna'; 
            
            
            $str .= '<div class="form-group"><label>Výběr vlny<span class="povinné">*</span></label>';
            $str .= '<select name="vlna" class="form-control placeholder  required">';
            $str .= '<option value="" selected disabled>Vlna</option>';
            foreach($apidata as $key => $val){
                $str .= '<option value="'.$key.'"';
                    if($typ_prihlasky == 1){
                        if(isset($_SESSION['data_jednotlivec']['vlna'])){
                            $str .= ($_SESSION['data_jednotlivec']['vlna'] == $key) ? (' selected = "selected"') : ("");
                        }
                    }
                    elseif($typ_prihlasky == 2){
                        if(isset($_SESSION['data_tym']['vlna'])){
                            $str .= ($_SESSION['data_tym']['vlna'] == $key) ? (' selected = "selected"') : ("");
                        }
                    }
                $str .= '/>'.$key.', Počet volných míst = '.$val.'</option>';
            }
            $str .= '</select></div>';
            return $str;
         }
    
        private function VelikostiTricek(){
            $tricka = Array();
            $tricka['bez'] = 'Bez trička';
            $tricka['S'] = 'S';
            $tricka['M'] = 'M';
            $tricka['L'] = 'L';
            $tricka['XL'] = 'XL';
            $tricka['XXL'] = 'XXL';  
            return $tricka;
        }
         
         
         
        private function VyberTricekJednotlivec(){  
            $tricka = $this->VelikostiTricek();
            $str = "";
            $str .= '<select name="tricko" class="form-control placeholder  required">';
            $str .= '<option value="" selected disabled>Tričko / Shirt (+250 Kč, volitelné)</option>';
            foreach($tricka as $key => $val){
                $str .= '<option value="'.$key.'"';
                    if(isset($_SESSION['data_jednotlivec']['tricko'])){
                        $str .= ($_SESSION['data_jednotlivec']['tricko'] == $key) ? (' selected = "selected"') : ("");
                    }
                $str .= '>'.$val.'</option>';
            }
            $str .= '</select>';
            return $str;
        }

        private function VyberTricekTym($i){  
            $tricka = $this->VelikostiTricek();
            $str = "";
            $str .= '<select name="tricko_'.$i.'" class="form-control placeholder  required">';
            $str .= '<option value="" selected disabled>Tričko / Shirt (+250 Kč, volitelné)</option>';
            foreach($tricka as $key => $val){
                $str .= '<option value="'.$key.'"';
                    if(isset($_SESSION['data_tym']['tricko_'.$i])){
                        $str .= ($_SESSION['data_tym']['tricko_'.$i] == $key) ? (' selected = "selected"') : ("");
                    }
                $str .= '>'.$val.'</option>';
            }
            $str .= '</select>';
            return $str;
        }
        
        
        
        
        

        private function ZkratkyStatu(){
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
            return $stat;
        }

        private function SeznamStatuJednotlivec(){   
            $str = "";
            $stat = $this->ZkratkyStatu();
            
            $str .= '<select name="stat" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Stát / Country</option>';
            foreach($stat as $key => $val){
                $str .= '<option value="'.$key.'"';
                        if(isset($_SESSION['data_jednotlivec']['stat'])){
                            $str .= ($_SESSION['data_jednotlivec']['stat'] == $key) ? (' selected = "selected"') : ("");
                        }
                $str .= '>'.$val.'</option>';
            }
            $str .= '</select>';
            return $str; 
        }
        
        private function SeznamStatuTym($i){   
            $str = "";
            $stat = $this->ZkratkyStatu();
            
            $str .= '<select name="stat_'.$i.'" class="form-control required placeholder">';
            $str .= '<option value="" selected disabled>Stát / Country</option>';
            foreach($stat as $key => $val){
                $str .= '<option value="'.$key.'"';
                        if(isset($_SESSION['data_tym']['stat_'.$i])){
                            $str .= ($_SESSION['data_tym']['stat_'.$i] == $key) ? (' selected = "selected"') : ("");
                        }
                $str .= '>'.$val.'</option>';
            }
            $str .= '</select>';
            return $str; 
        }
        
        
        
        private function VyberPodzavodu($apidata) {
            $str = '<h4>Vyplńte formulář k registraci</h4>';
            $poradi_podzavodu = false;
            if(isset($_GET['poradi_podzavodu'])){
                $poradi_podzavodu =  $_GET['poradi_podzavodu'];
            }
           // $str .= '<h2>Výběr závodu</h2>';
            $str .= '<div class="form-group">';
            $str .= '<label for="id_zavodu">Vyberte závod<span class="povinné"> *</span></label>';
            $str .= '<select class="form-control" id="vyber_zavodu" onchange="window.location = \'https://www.aktivitytour.cz/'.$this->stranka.'?action=vyber_podzavodu&poradi_podzavodu=\'+this.options[this.selectedIndex].value+\'#vyber_zavodu\';" name="vyber_zavodu">';
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
            $data_tym = Array();
            (isset($_SESSION['data_tym'])) ? ($data_tym = $_SESSION['data_tym']) : ($data_tym = NULL);
            $pocet_clenu_tymu = false;
            if(isset($_GET['pocet_clenu_tymu'])){
                $pocet_clenu_tymu = $_GET['pocet_clenu_tymu'];
            }
         
            $str .= '<div class="form-group"><label>Počet členů týmu</label>';
            $str .= '<select class="form-control" id="pocet_clenu_tymu" onchange="window.location = \'https://www.aktivitytour.cz/'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=vyber_podzavodu&poradi_podzavodu=2&pocet_clenu_tymu=\'+this.options[this.selectedIndex].value+\'\';" name="pocet_clenu_tymu">';
            $str .= '<option value="" selected disabled>Zadejte počet členů týmu</option>';
            for($i=2;$i<=20;$i++){
                $str .= '<option value="'.$i.'"';
                if($pocet_clenu_tymu == $i){
                    $str .= ' selected="selected"';
                }
                $str .= '>'.$i.'</option>';
            }
            $str .= '</select></div>';
            
            //$str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=odeslat_prihlasku" id="registration_form" method="post">';
            //$str .= '<form action="comgate/payment.php" id="registration_form" method="post">';

            $str .= '<form action="https://www.aktivitytour.cz'.parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH).'?action=zkontrolovat_prihlasku&poradi_podzavodu='.$_GET['poradi_podzavodu'].'" id="registration_form" method="post">';
            $str .= '<input type="hidden" name="typ_prihlasky" value="2" />';
            $str .= '<input type="hidden" name="pocet_clenu" value="'.$pocet_clenu_tymu.'" />';
            $str .= '<input type="hidden" name="event_order" value="'.$_GET['poradi_podzavodu'].'" />';
            $str .= '<input type="hidden" name="neposilat_potvrzovaci_mail" value="true" />';

            if($pocet_clenu_tymu >= 2){
                $str .= '<div class="form-group"><label for="team">Název týmu <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="nazev_tymu" class="form-control required" placeholder="Název týmu" value="';
                $str .= isset($data_tym['nazev_tymu']) ? $data_tym['nazev_tymu'] : ("");
                $str .= '" /></div>';
                
                $str .= '<div class="form-group"><label>Výběr kategorie <span class="povinné">*</span></label>';
                $str .= '<select name="id_kategorie" class="form-control required placeholder">';
                $str .= '<option value="" selected disabled>Výběr kategorie</option>';
                foreach($apidata->kategorie AS $val){
                    if($val->poradi_podzavodu == 2){ //tohle je třeba vymyslet jinak
                        $str .= '<option value="'.$val->id_kategorie.'"';
                        if($val->id_kategorie == $data_tym['id_kategorie']){
                            $str .= ' selected="selected"';
                        }
                        $str .= '>'.$val->nazev_kategorie.'</option>';
                    }
                }
                $str .= '</select></div>';  
                
                $str .= '<div class="form-group"><label for="jmeno">Jméno kapitána týmu <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="jmeno" class="form-control required" placeholder="Jméno kapitána týmu" value="';
                $str .= isset($data_tym['jmeno']) ? $data_tym['jmeno'] : ("");
                $str .= '" /></div>';

                $str .= '<div class="form-group"><label for="prijmeni">Příjmení kapitána týmu <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="prijmeni" class="form-control required" placeholder="Příjmení kapitána týmu" value="';
                $str .= isset($data_tym['prijmeni']) ? $data_tym['prijmeni'] : ("");
                $str .= '" /></div>';

                $str .= '<div class="form-group"><label for="email">E-mail <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="email" class="form-control required" placeholder="E-mail" value="';
                $str .= isset($data_tym['email']) ? $data_tym['email'] : ("");
                $str .= '" /></div>';

                $str .= '<div class="form-group"><label for="telefon_1">Telefon <span class="povinné">*</span></label>';
                $str .= '<input type="text" name="telefon_1" class="form-control required" placeholder="Telefon" value="';
                $str .= isset($data_tym['telefon_1']) ? $data_tym['telefon_1'] : ("");
                $str .= '" /></div>';
                
                $str .= $this->VyberVlny($apidata->vlny,2);
                $str .= '<hr>';

                for($i = 1;$i <=  $pocet_clenu_tymu;$i++){

                    $str .= '<h4 style="text-decoration:underline;margin-top:50px">Zavodnik '.$i. '</h4>';
                    $str .= '<div class="form-group"><label for="firstname_'.$i.'">Jméno<span class="povinné">*</span></label>';
                    $str .= '<input type="text" name="firstname_'.$i.'" class="form-control required" placeholder="Jméno" value="';
                    $str .= (isset($data_tym['firstname_'.$i])) ? ($data_tym['firstname_'.$i]) : ('');
                    $str .= '" /></div>';

                    $str .= '<div class="form-group"><label for="surname_'.$i.'">Příjmení<span class="povinné">*</span></label>';
                    $str .= '<input type="text" name="surname_'.$i.'" class="form-control required" placeholder="Příjmení" value="';
                    $str .= (isset($data_tym['surname_'.$i])) ? ($data_tym['surname_'.$i]) : ('');
                    $str .= '" /></div>';

                    $str .=  '<div class="form-group"><label for="rok_narozeni_'.$i.'">Rok narození<span class="povinné">*</span></label>';
                    $str .=  '<select name="rok_narozeni_'.$i.'" class="form-control required placeholder">';  
                    $str .=  '<option value="" selected disabled>Rok narození</option>';  
                    for($k=1921;$k<=2020;$k++){
                        $str .= '<option value="'.$k.'" '.((isset($data_tym['rok_narozeni_'.$i]) && $data_tym['rok_narozeni_'.$i] == $k) ? 'selected="selected"' : '').'>'.$k.'</option>';
                    }
                    $str .= '</select></div>';
                    $str .= '<div class="form-group"><label for="pohlavi_'.$i.'">Pohlavi<span class="povinné">*</span></label>';
                    $str .= '<select name="pohlavi_'.$i.'" class="form-control required placeholder">';  
                    $str .= '<option value="">Pohlaví</option>';  
                    $str .= '<option value="M"'.((isset($data_tym['pohlavi_'.$i]) && $data_tym['pohlavi_'.$i] == 'M') ? ' selected="selected"' : '').'>Muž</option>';  
                    $str .= '<option value="Z"'.((isset($data_tym['pohlavi_'.$i]) && $data_tym['pohlavi_'.$i] == 'Z') ? ' selected="selected"' : '').'>Žena</option>';  
                    $str .= '</select></div>';
                    $str .= '<div class="form-group"><label for="mail_'.$i.'">E-mail<span class="povinné">*</span></label>';
                    $str .= '<input type="text" name="mail_'.$i.'" class="form-control required" placeholder="E-mail" value="';
                    $str .= (isset($data_tym['mail_'.$i])) ? ($data_tym['mail_'.$i]) : ('');
                    $str .= '" /></div>';
                    $str .= '<div class="form-group"><label for="stat_'.$i.'">Stát<span class="povinné">*</span></label>';
                    $str .= $this->SeznamStatuTym($i);
                    
                    $str .= '</div>';

                    $str .= '<div class="form-group"><label for="tricko_'.$i.'">Tričko<span class="povinné">*</span></label>';
                    $str.= $this->VyberTricekTym($i);
                    $str .= '</div>';

                  }
                  
                $str .= '<div class="form-group"><label for="vzkaz">Vzkaz pořadateli</label>';  
                $str .= '<textarea placeholder="Vzkaz pořadateli, máte-li nějaký / Message for the organizer" class="form-control" name="vzkaz" cols="40" rows="5">';
                if(isset($data_tym['vzkaz'])) $str .= $data_tym['vzkaz'];
                $str .= '</textarea></div>';
                $str .= '<button type="submit" class="form-control button-submit">POKRAČOVAT</button>';
                $str .= '</form>';

              }
            return $str;
          }
  }         
?>