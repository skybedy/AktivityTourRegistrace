<?php


   if(isset($_GET)){
        $_POST = $_GET;
    }

// include agmo libraries

require_once dirname(__FILE__).'/lib/AgmoPaymentsSimpleDatabase.php';
require_once dirname(__FILE__).'/lib/AgmoPaymentsSimpleProtocol.php';

// include configuration
require_once 'config.php';


// initialize payments data object



// initialize payments protocol object

require_once dirname(__FILE__).'/common.php';

try {

    // prepare payment parameters
    $refId = $paymentsDatabase->createNextRefId();
    $price = $_POST['price'];
    $currency = $_POST['currency'];
    

    // create new payment transaction
    $paymentsProtocol->createTransaction(
        'CZ',               // country
        $price,             // price
        $currency,          // currency
        'Payment test',     // label
        $refId,             // refId
        NULL,               // payerId
        'STANDARD',         // vatPL
        'PHYSICAL',         // category
        $_POST['method'],   // method
        '',
        isset($_POST['email']) ? $_POST['email'] : '',
        '',
        '',
        '',
        isset($_POST['preauth']),   // preauth
        (isset($_POST['initRecurring']) && $_POST['initRecurring'] == 'true'),   // preauth
        isset($_POST['initRecurringId']) ? $_POST['initRecurringId'] : null   // preauth
    );
    
    $transId = $paymentsProtocol->getTransactionId();
    


    $url = 'https://api.timechip.cz/prihlasky/transidcomgate/'.$_POST['rok_zavodu'].'/'.$_POST['id_zavodu'];
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query(Array('id_prihlasky' => $_POST['id_prihlasky'],'typ_prihlasky' => $_POST['typ_prihlasky'],'transid' => $transId))
          )
      );
      //echo $url;
      $context  = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      if ($result === FALSE) { /* Handle error */ }
      //var_dump($result);
     // print_r($result);
     // $result = json_decode($result);
      //print_r($result);
    
    
    
    
    

    // save transaction data
    $paymentsDatabase->saveTransaction(
        $transId,       // transId
        $refId,         // refId
        $price,         // price
        $currency,      // currency
        'PENDING'       // status
    );

    // redirect to agmo payments system
    header('location: '.$paymentsProtocol->getRedirectUrl());

}
catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "ERROR\n\n";
    echo $e->getMessage();
}
