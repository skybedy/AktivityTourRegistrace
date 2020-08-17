<?php
session_start();
$transid = false;
if(isset($_GET['id'])) $transid = $_GET['id'];

$url = 'https://api.timechip.cz/prihlasky/ziskani-typu-prihlasky/2020/11/'.$transid;
$result = file_get_contents($url);
if ($result === FALSE) { /* Handle error */ }
if($result == 1){
   $url = 'https://api.timechip.cz/prihlasky/update-zaplaceno-jednotlivec/2020/11/'.$_SESSION['data_jednotlivec']['id_prihlasky']; 
}
elseif($result == 2){
   $url = 'https://api.timechip.cz/prihlasky/update-zaplaceno-tym/2020/11/'.$_SESSION['data_tym']['id_prihlasky']; 
}




$result1 = file_get_contents($url);
if ($result1 === FALSE) { /* Handle error */ }
$result1 = json_decode($result1,true);



if($result1['status'] == 'OK'){
    session_destroy();
    header('Location: https://www.aktivitytour.cz/dekujeme');
}
else{
    echo '<p>Něco se pokazilo</p>';
}
