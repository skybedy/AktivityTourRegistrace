<?php

// include agmo libraries
//require_once dirname(__FILE__).'/lib/AgmoPaymentsSimpleDatabase.php';
//require_once dirname(__FILE__).'/lib/AgmoPaymentsSimpleProtocol.php';
require_once dirname(__FILE__).'/lib/AgmoPaymentsSimpleDatabase.php';
require_once dirname(__FILE__).'/lib/AgmoPaymentsSimpleProtocol.php';

// include configuration
require_once 'config.php';

// initialize payments data object

$paymentsDatabase = new AgmoPaymentsSimpleDatabase(
    dirname(__FILE__).'/data',
    $config['merchant'],
    $config['test']
);


// initialize payments protocol object
$paymentsProtocol = new AgmoPaymentsSimpleProtocol(
    $config['paymentsUrl'],
    $config['merchant'],
    $config['test'],
    $config['secret'],
    $config['paymentsUrl2']
);
