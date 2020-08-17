<?php

require_once dirname(__FILE__).'/common.php';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang='cs' xml:lang='cs' xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta http-equiv='content-type' content='text/html; charset=utf-8' />
    <title>Payments protocol simple</title>
</head>
<body>

<?php

    try {
    // get transaction status from my database
    $status = $paymentsDatabase->getTransactionStatus(
        $_GET['id'],   // transId
        $_GET['refId'] // refId
    );
    echo '<h1>'.$status.'</h1>';
}
catch (Exception $e) {
    echo '<h1>ERROR</h1>';
    echo '<p>Cannot check the payment status!</p>';
}

?>

<p><a href="../index.php">next payment</a></p>

</body>
</html>

