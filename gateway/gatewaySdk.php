<?php

/**
 * Summary of gatewaySdk
 */
class gatewaySdk
{
    /**
     * rsa algorithm
     */
    static $ALGORITHM = 'aes-256-cbc';

    /**
     * aes algorithm
     */
    static $HASH_ALGORITHM = 'rsa-sha256';

    /**
     * encrypt auth info
     */
    static $EncryptAuthInfo = '';

    /** user deposit
     * @param $orderId [order number - maxlength(40)]
     * @param $amount [order amount - maxlength(20)]
     * @param $currency [Empty default: MYR - maxlength(16)]
     * @param $payMethod [FPX, TNG_MY, ALIPAY_CN, GRABPAY_MY, BOOST_MY - maxlength(16)]
     * @param $customerName [customer name - maxlength(64)]
     * @param $customerEmail [customer email - maxlength(64)]
     * @param $customerPhone [customer phone - maxlength(20)]
     * @return array [code,message,paymentUrl,transactionId]
     */
    public static function deposit(
        $orderId,
        $amount,
        $currency,
        $payMethod,
        $customerName,
        $customerEmail,
        $customerPhone
    ) {
        $result = array();
        try {
            $token = self::getToken();
            if (self::isnull($token)) return $result;
            $requestUrl = "gateway/"  . gatewayCfg::$VERSION_NO . "/createPayment";
            $cnst = self::generateConstant($requestUrl);
            // If callbackUrl and redirectUrl are empty, take the values ​​of [curl] and [rurl] in the developer center.
            // Remember, the format of json and the order of json attributes must be the same as the SDK specifications.
            // The sorting rules of Json attribute data are arranged from [a-z]
            $bodyJson = "{\"customer\":{\"email\":\"" . $customerEmail . "\",\"name\":\"" . $customerName . "\",\"phone\":\"" . $customerPhone . "\"},\"method\":\"" . $payMethod . "\",\"order\":{\"additionalData\":\"\",\"amount\":\"" . $amount . "\",\"currencyType\":\"" . (self::isnull($currency) ? "MYR" : $currency) . "\",\"id\":\"" . $orderId . "\",\"title\":\"Payment\"}}";
            //$bodyJson = "{\"callbackUrl\":\"https://www.google.com\",\"customer\":{\"email\":\"" . $customerEmail . "\",\"name\":\"" . $customerName . "\",\"phone\":\"" . $customerPhone . "\"},\"method\":\"" . $payMethod . "\",\"order\":{\"additionalData\":\"\",\"amount\":\"" . $amount . "\",\"currencyType\":\"" . (self::isnull($currency) ? "MYR" : $currency) . "\",\"id\":\"" . $orderId . "\",\"title\":\"Payment\"},\"redirectUrl\":\"https://www.google.com\"}";
            $base64ReqBody = self::sortedAfterToBased64($bodyJson);
            $signature = self::createSignature($cnst, $base64ReqBody);
            $encryptData = self::symEncrypt($base64ReqBody);
            $json = ["data" => $encryptData];
            $dict = self::post($requestUrl, $token, $signature, $json, $cnst["nonceStr"], $cnst["timestamp"]);
            if (!self::isnull($dict["code"]) && strval($dict["code"]) == "1" && !self::isnull($dict["encryptedData"])) {
                $decryptedData = self::symDecrypt($dict["encryptedData"]);
                $dict = json_decode($decryptedData,true);
                if (!self::isnull($dict["data"]["paymentUrl"]) && !self::isnull($dict["data"]["transactionId"])) {
                    $result['code'] = "1";
                    $result["message"] = "";
                    $result["paymentUrl"] = $dict["data"]["paymentUrl"];
                    $result["transactionId"] = $dict["data"]["transactionId"];
                    return $result;
                }
            }
            $result["code"] = "0";
            $result["message"] = $dict["message"];
            return $result;
        } catch (Exception $e) {
            $result["code"] = '0';
            $result["message"] = $e->getMessage();
            return $result;
        }
    }

    /** user withdraw
     * @param $orderId [order number - maxlength(40)]
     * @param $amount [order amount - maxlength(20)]
     * @param $currency [Empty default: MYR - maxlength(16)]
     * @param $bankCode [MayBank=MBB,Public Bank=PBB,CIMB Bank=CIMB,Hong Leong Bank=HLB,RHB Bank=RHB,AmBank=AMMB,United Overseas Bank=UOB,Bank Rakyat=BRB,OCBC Bank=OCBC,HSBC Bank=HSBC  - maxlength(16)]
     * @param $cardholder [cardholder - maxlength(64)]
     * @param $accountNumber [account number - maxlength(20)]
     * @param $refName [recipient refName - maxlength(64)]
     * @param $recipientEmail [recipient email - maxlength(64)]
     * @param $recipientPhone [recipient phone - maxlength(20)]
     * @return array [code,message,transactionId]
     */
    public static function  withdraw(
        $orderId,
        $amount,
        $currency,
        $bankCode,
        $cardholder,
        $accountNumber,
        $refName,
        $recipientEmail,
        $recipientPhone
    ) {
        $result = array();
        try {
            $token = self::getToken();
            if (self::isnull($token)) return $result;
            $requestUrl = "gateway/" . gatewayCfg::$VERSION_NO . "/withdrawRequest";
            $cnst = self::generateConstant($requestUrl);
            // payoutspeed contain "fast", "normal", "slow" ,default is : "fast"
            // Remember, the format of json and the order of json attributes must be the same as the SDK specifications.
            // The sorting rules of Json attribute data are arranged from [a-z]
            $bodyJson = "{\"order\":{\"amount\":\"" . $amount . "\",\"currencyType\":\"" . (self::isnull($currency) ? "MYR" : $currency) . "\",\"id\":\"" . $orderId . "\"},\"recipient\":{\"email\":\"" . $recipientEmail . "\",\"methodRef\":\"" . $refName . "\",\"methodType\":\"" . $bankCode . "\",\"methodValue\":\"" . $accountNumber . "\",\"name\":\"" . $cardholder . "\",\"phone\":\"" . $recipientPhone . "\"}}";
            //$bodyJson = "{\"callbackUrl\":\"https://www.google.com\",\"order\":{\"amount\":\"" . $amount . "\",\"currencyType\":\"" . (self::isnull($currency) ? "MYR" : $currency) . "\",\"id\":\"" . $orderId . "\"},\"payoutspeed\":\"normal\",\"recipient\":{\"email\":\"" . $recipientEmail . "\",\"methodRef\":\"" . $refName . "\",\"methodType\":\"" . $bankCode . "\",\"methodValue\":\"" . $accountNumber . "\",\"name\":\"" . $cardholder . "\",\"phone\":\"" . $recipientPhone . "\"}}";
            $base64ReqBody = self::sortedAfterToBased64($bodyJson);
            $signature = self::createSignature($cnst, $base64ReqBody);
            $encryptData = self::symEncrypt($base64ReqBody);
            $json = ["data" => $encryptData];
            $dict = self::post($requestUrl, $token, $signature, $json, $cnst["nonceStr"], $cnst["timestamp"]);
            if (!self::isnull($dict["code"]) && strval($dict["code"]) == "1" && !self::isnull($dict["encryptedData"])) {
                $decryptedData = self::symDecrypt($dict["encryptedData"]);
                $dict = json_decode($decryptedData,true);
                if (!self::isnull($dict["data"]["transactionId"])) {
                    $result["code"] = "1";
                    $result["message"] = "";
                    $result["transactionId"] = $dict["data"]["transactionId"];
                    return $result;
                }
            }
            $result["code"] = "0";
            $result["message"] = $dict["message"];
            return $result;
        } catch (Exception $e) {
            $result["code"] = "0";
            $result["message"] = $e->getMessage();
            return $result;
        }
    }

    /** User deposit and withdrawal details
     * @param $orderId [transaction id]
     * @param $type [1 deposit,2 withdrawal]
     * @return array [code,message,transactionId,amount,fee]
     */
    public static function  detail($orderId, $type)
    {
        $result = array();
        try {
            $token = self::getToken();
            if (self::isnull($token)) return $result;
            $requestUrl = "gateway/" . gatewayCfg::$VERSION_NO . "/getTransactionStatusById";
            $cnst = self::generateConstant($requestUrl);
            // Remember, the format of json and the order of json attributes must be the same as the SDK specifications.
            // The sorting rules of Json attribute data are arranged from [a-z]
            // type : 1 deposit,2 withdrawal
            $bodyJson = "{\"transactionId\":\"" . $orderId . "\",\"type\":" . $type . "}";
            $base64ReqBody = self::sortedAfterToBased64($bodyJson);
            $signature = self::createSignature($cnst, $base64ReqBody);
            $encryptData = self::symEncrypt($base64ReqBody);
            $json = ["data" => $encryptData];
            $dict = self::post($requestUrl, $token, $signature, $json, $cnst["nonceStr"], $cnst["timestamp"]);
            if (!self::isnull($dict["code"]) && $dict["code"] == "1" && !self::isnull($dict["encryptedData"])) {
                $decryptedData = self::symDecrypt($dict["encryptedData"]);
                return json_decode($decryptedData,true);
            }
            $result["code"] = "0";
            $result["message"] = $dict["data"]["message"];
            return $result;
        } catch (Exception $e) {
            $result["code"] = "0";
            $result["message"] = $e->getMessage();
            return $result;
        }
    }

    /** get server token
     * @return string
     */
    private static function getToken()
    {
        $token = "";
        if (self::isnull(self::$EncryptAuthInfo)) {
            $authString = self::stringToBase64(gatewayCfg::$CLIENT_ID . ":" . gatewayCfg::$CLIENT_SECRET);
            self::$EncryptAuthInfo = self::publicEncrypt($authString);
        }
        $json=["data"=>self::$EncryptAuthInfo];
        $keys = array("code", "encryptedToken");
        $dict = self::post("gateway/"  . gatewayCfg::$VERSION_NO . "/createToken", "", "", $json, "", "", $keys);
        if (!self::isnull($dict["code"]) && !self::isnull($dict["encryptedToken"]) && strval($dict["code"]) == "1") {
            $token = self::symDecrypt($dict["encryptedToken"]);
        }
        return $token;
    }

    /** A simple http request method
     * @param $url
     * @param $token
     * @param $signature
     * @param $json
     * @param $nonceStr
     * @param $timestamp
     * @return mixed
     */
    private static function post($url, $token, $signature, $json, $nonceStr, $timestamp)
    {
        if (self::endWith(gatewayCfg::$BASE_URL, "/")) {
            $url = gatewayCfg::$BASE_URL . $url;
        } else {
            $url = gatewayCfg::$BASE_URL . "/" . $url;
        }
        $options = array(
            "http" => array(
                "method" => "POST",
                "header" => array("Content-type:application/json") ,
                "content" =>json_encode($json)
            )
        );
        if (!self::isnull($token) && !self::isnull($signature) && !self::isnull($nonceStr) && !self::isnull($timestamp)) {
            $options["http"]["header"] = array("Content-type:application/json",
                "Authorization:". $token,
                "X-Nonce-Str:". $nonceStr,
                "X-Signature:".$signature,
                "X-Timestamp:".$timestamp);
        }
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result,true);
    }

    /** create a signature
     * @param $cnst
     * @param $base64ReqBody
     * @return string
     */
    private static function createSignature($cnst, $base64ReqBody)
    {
        $dataString = "data=" . $base64ReqBody . "&method=" . $cnst["method"] . "&nonceStr=" . $cnst["nonceStr"] . "&requestUrl=" . $cnst["requestUrl"] . "&signType=" . $cnst["signType"] . "&timestamp=" . $cnst["timestamp"];
        $signature = self::sign($dataString);
        return $cnst["signType"] . " " . $signature;
    }

    /** generate constant
     * @param $requestUrl
     * @return array
     */
    private static function generateConstant($requestUrl)
    {
        return [
            "method" => "post",
            "nonceStr" => self::randomNonceStr(),
            "requestUrl" => $requestUrl,
            "signType" => "sha256",
            "timestamp" => time(),
        ];
    }

    /** random nonceStr
     * @return string
     */
    private static function randomNonceStr()
    {
        $sb = "";
        $chars = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
        for ($i = 1; $i <= 8; $i++) {
            $index = rand(0, count($chars)-1);
            $sb = $sb . $chars[$index];
        }
        $bytes = self::stringToBytes($sb);
        return self::bytesToHex($bytes);
    }

    /** Encrypt data based on the server's public key
     * @param $data data to be encrypted
     * @return string encrypted data
     */
    private static function publicEncrypt($data)
    {
        if(openssl_public_encrypt($data, $encryptData, gatewayCfg::$SERVER_PUB_KEY, OPENSSL_PKCS1_PADDING))
        {
            $encryptBytes = self::stringToBytes($encryptData);
            return self::bytesToHex($encryptBytes);
        }else{
            return '';
        }
    }

    /** Decrypt data according to the interface private key
     * @param $encryptData
     * @return mixed
     */
    private static function privateDecrypt($encryptData)
    {
        openssl_private_decrypt($encryptData, $decrypted, gatewayCfg::$PRIVATE_KEY, OPENSSL_PKCS1_PADDING);
        $json = json_decode($decrypted);
        return $json;
    }

    /** Payment interface data encryption method
     * @param $message
     * @return string
     */
    private static function symEncrypt($message)
    {
        $iv = self::generateIv(gatewayCfg::$CLIENT_SYMMETRIC_KEY);
        $cipherData = openssl_encrypt($message, self::$ALGORITHM, gatewayCfg::$CLIENT_SYMMETRIC_KEY, OPENSSL_RAW_DATA, $iv);
        return self::stringToHex($cipherData);
    }

    /** Payment interface data decryption method
     * @param $encryptedMessage encryptedMessage The data that needs to be encryptedMessage, the result encrypted by symEncrypt can be decrypted
     * @return string Return the data content of utf-8 after decryption
     */
    public static function symDecrypt($encryptedMessage)
    {
        $encryptedText = self::hexToString($encryptedMessage);
        $iv = self::generateIv(gatewayCfg::$CLIENT_SYMMETRIC_KEY);
        $decrypted = openssl_decrypt($encryptedText, self::$ALGORITHM, gatewayCfg::$CLIENT_SYMMETRIC_KEY, OPENSSL_RAW_DATA, $iv);
        if($decrypted){
            return $decrypted;
        }else{
            return "";
        }
    }

    /** private key signature
     * @param $data
     * @return string
     */
    private static function sign($data)
    {
        openssl_sign($data, $signature, gatewayCfg::$PRIVATE_KEY, self::$HASH_ALGORITHM);
        return self::stringToBase64($signature);;
    }

    /** Public key verification signature information
     * @param $data
     * @param $signature
     * @return false|int
     */
    private static function verify($data, $signature)
    {
        return openssl_verify($data, $signature, gatewayCfg::$SERVER_PUB_KEY, self::$HASH_ALGORITHM);
    }

    /** Return base64 after sorting argument list
     * @param $json
     * @return string
     */
    private static function sortedAfterToBased64($json)
    {
        return self::stringToBase64($json);
    }

    /** Generate an IV based on the data encryption key
     * @param $symmetricKey
     * @return string
     */
    private static function generateIv($symmetricKey)
    {
        //$data = md5($symmetricKey,true);
        //$iv = self::stringToBytes($data);
        //return $iv;
        return md5($symmetricKey,true);
    }

    /** string to batys
     * @param $data
     * @return array|false
     */
    private static function stringToBytes($data)
    {
        return unpack('C*', $data);
    }

    /** string to base64
     * @param $data
     * @return string
     */
    private static function stringToBase64($data)
    {
        return base64_encode($data);
    }

    /** base64 to string
     * @param $base64
     * @return false|string
     */
    private static function base64ToString($base64)
    {
        return base64_decode($base64);
    }

    /** String to bytes
     * @param $bytes
     * @return string
     */
    private static function bytesToString($bytes)
    {
        $chars = array_map('chr', $bytes);
        return join($chars);
    }

    /** Bytes to hex
     * @param $bytes
     * @return string
     */
    private static function bytesToHex($bytes)
    {
        $chars = array_map('chr', $bytes);
        $bin = join($chars);
        return bin2hex($bin);
    }

    /** Hex to bytes
     * @param $hex
     * @return array|false
     */
    private static function hexToBytes($hex)
    {
        $string = hex2bin($hex);
        return unpack('C*', $string);
    }

    /** Bytes to base64
     * @param $bytes
     * @return string
     */
    private static function bytesToBase64($bytes)
    {
        $string = self::bytesToString($bytes);
        return self::stringToBase64($string);
    }

    /** Base64 to bytes
     * @param $base64
     * @return array
     */
    private static function base64ToBytes($base64)
    {
        $arr = base64_decode($base64);
        $bytes = array();
        foreach (str_split($arr) as $chr) {
            $bytes[] = sprintf('%08b', ord($chr));
        }
        return $bytes;
    }

    /**
     * String to Hex
     *
     * @param string $string
     * @return string
     */
    private static function stringToHex($string)
    {
        return bin2hex($string);
    }

    /**
     * Hex String to String
     *
     * @param string $hexString
     * @return string 
     */
    private static function hexToString($hexString)
    {
        return hex2bin($hexString);
    }

    /** isnull
     * @param $val
     * @return bool
     */
    private static function isnull($val)
    {
        if ($val == null) return true;
        if (!isset($val)) return true;
        if (empty($val)) return true;
        return false;
    }

    /** end with
     * @param $str
     * @param $pattern
     * @return bool
     */
    private  static  function endWith($str,$pattern) {
        $length = strlen($pattern);
        if ($length == 0) {
            return true;
        }
        return (substr($str, -$length) === $pattern);
    }
}
