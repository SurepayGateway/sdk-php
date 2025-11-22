<!DOCTYPE html>
<html>
<header>

</header>

<body>
    <h1>index.php</h1>
    <?php

    require_once 'gateway/gatewayCfg.php';
    require_once 'gateway/gatewaySdk.php';

    // Check whether OpenSSL support is enabled
    phpinfo();


    // initialize this configuration
    // verNo gateway Api Version Number, default: v1
    // apiUrl gateway Api Url
    // appId in developer settings : App Id
    // key in developer settings : Key
    // secret in developer settings : secret
    // serverPubKey in developer settings : Server Public Key
    // privateKey in developer settings : Private Key
    // gatewayCfg::init($verNo, $apiUrl, $appId, $key, $secret, $serverPubKey, $privateKey);

    // Here is an example of a deposit
    // return deposit result: code=1,message=,transactionId=12817291,paymentUrl=https://www.xxxx...
    $depositResult = gatewaySdk::deposit('10001', 1.06, 'MYR', 'TNG_MY', 'gateway Test', 'gateway@hotmail.com', '0123456789');
    echo $depositResult;

    // Here is an example of a withdraw
    // return withdraw result: code=1,message=,transactionId=12817291
    $withdrawResult = gatewaySdk::withdraw('10012', 1.06, 'MYR', 'CIBBMYKL', 'gateway Test', '234719327401231','', 'gateway@hotmail.com', '0123456789');
    echo $withdrawResult;

    // Here is an example of a detail
    // return detail result:code=1,message=,transactionId=,amount=,fee=
    $detailResult = gatewaySdk::detail('10921', 1);
    echo $detailResult;

    // Decrypt the encrypted information in the callback
    $jsonstr = gatewaySdk::symDecrypt("encryptedData .........");
    echo $jsonstr;
    ?>
</body>

</html>