<?php

use Phalcon\Crypt as Crypt;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

class Tools
{
    public static function getDate()
    {

        return date("Y-m-d");
    }
    public static function getDateTime($timestamp = false)
    {

        return ($timestamp) ? date("Y-m-d H:i:s", $timestamp) : date("Y-m-d H:i:s");
    }
    public static function getLetsPayDateTime()
    {

        return date("YmdHis");
    }

    public static function file_get_https(){
        $arrContextOptions=array(
            "ssl"=>array(
                  "verify_peer"=>false,
                  "verify_peer_name"=>false,
              ),
          );
          return stream_context_create($arrContextOptions);
    }
    public static function getIp(){
        return $_SERVER['REMOTE_ADDR'];
    }
    public static function ServerIp(){
        
        return file_get_contents('https://api.ipify.org', false ,);
    }

    public static  function getRequestHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }
    public static function getYouTubeTitle($video_id)
    {
        $html = 'https://www.googleapis.com/youtube/v3/videos?id=' . $video_id . '&key=AIzaSyDPWny4ri_7xMwmiluQPZr4C98kRn5n3uc&part=snippet';
        $response = Tools::httpGet($html);
        $decoded = json_decode($response, true);
        foreach ($decoded['items'] as $items) {
            $title = $items['snippet']['title'];
            return $title;
        }
    }
    public static function Pages($Pages, $entries, $Offset = 20)
    {

        $Pages['PageIndex'] = (!empty($Pages['PageIndex'])) ? $Pages['PageIndex'] : 1;
        $Pages['entries'] = $entries;
        $Pages['Limit'] = ceil($Pages['entries']  / $Offset);

        $PageStart = ($Pages['PageIndex'] - 5 < 1) ? 1 : ($Pages['PageIndex'] - 5);
        $PageEnd = ($PageStart + 10 < $Pages['Limit']) ? ($PageStart + 10) : $Pages['Limit'];

        for ($i = $PageStart; $i < $PageEnd; $i++) $Pages['List'][] = $i;


        if ($Pages['PageIndex'] > $Pages['Limit']) $Pages['PageIndex'] = $Pages['Limit'];

        $Pages['Offset'] = (($Pages['PageIndex'] - 1 < 1) ? 0 : ($Pages['PageIndex'] - 1) * $Offset);

        return $Pages;
    }
    public static function httpGet($url, $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        $response = curl_exec($ch);
        curl_close($ch);
        return ($json) ? json_decode($response, true) : $response;
    }

    public static function httpPost($url, $input = [], $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, false );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($input));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        //SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);
        return ($json) ? json_decode($response, true) : $response;
    }

    public static function fix_array_Key($data, $key = "id")
    {
        if (empty($data)) return array();
        $data = array_map('self::fix_element_Key', $data, array_fill(0, count($data), $key));
        return $data;
    }


    public static function fix_element_Key($data, $key = "id")
    {

        if (is_array($key)) {

            return (!empty($key)) ? array_intersect_key($data, array_flip($key)) : array();
        }

        return (isset($data[$key])) ? $data[$key] : false;
    }

    public static function filterArray($Items, $key, $arrayValue, $include = true)
    {

        if (!is_array($Items) || !is_array($arrayValue)) return [];

        return array_filter($Items, function ($item) use ($key, $arrayValue, $include) {

            if (!isset($item[$key])) return false;

            if ($include)
                return (in_array($item[$key], $arrayValue));
            else
                return (!in_array($item[$key], $arrayValue));
        });
    }

    public static function b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }
    public static function b64decode($string)
    {
        $data = str_replace(array(' ', '-', '_'), array('+', '+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public static function Crypt(String $string, $decode = false, String $key = "adonisnowman")
    {
        $crypt = new Crypt();
        $crypt->setCipher('AES-256-CFB');
        if ($decode == false) return self::b64encode($crypt->encrypt($string, $key));
        else return $crypt->decrypt(self::b64decode($string), $key);
    }

    public static function getFileList($dir = "", $reg = false)
    {
        if (!file_exists($dir)) return false;

        $Return = [];
        $dir = dir($dir);
        if ($reg) $reg = "/(?P<reg>$reg)/";
        while (false !== ($entry = $dir->read())) {

            if ($entry == '.' || $entry == "..") continue;
            if ($reg) {
                preg_match($reg, $entry, $matchs);
                if (!empty($matchs['reg'])) $Return[] = $matchs['reg'];
            } else $Return[] = $entry;
        }
        array_unique($Return);
        $Return = array_filter($Return);
        return $Return;
    }

    public static function getToken($expires = '+1 day', $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2')
    {
        $signer  = new Hmac();

        // Builder object
        $builder = new Builder($signer);

        $now        = new DateTimeImmutable();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('-1 minute')->getTimestamp();
        $expires    = $now->modify($expires)->getTimestamp();


        // Setup
        $builder
            ->setAudience('https://account.adonis.tw')  // aud
            ->setContentType('application/json')        // cty - header
            ->setExpirationTime($expires)               // exp 
            ->setId('abcd123456789')                    // JTI id 
            ->setIssuedAt($issued)                      // iat 
            ->setIssuer('https://adonis.tw')           // iss 
            ->setNotBefore($notBefore)                  // nbf
            ->setSubject('my subject for this claim')   // sub
            ->setPassphrase($passphrase)                // password 
        ;

        // Phalcon\Security\JWT\Token\Token object
        $tokenObject = $builder->getToken();

        // The token
        return $tokenObject->getToken();
    }
    
    public static function emailSend($Address = "adonisnowman@gmail.com", $text = "test",$body)
    {

        $mail = new PHPMailer(); //建立新物件        
        $mail->IsSMTP(); //設定使用SMTP方式寄信        
        $mail->SMTPAuth = true; //設定SMTP需要驗證        
        $mail->SMTPSecure = "ssl"; // Gmail的SMTP主機需要使用SSL連線   
        $mail->Host = "smtp.gmail.com"; //Gamil的SMTP主機        
        $mail->Port = 465;  //Gamil的SMTP主機的SMTP埠位為465埠。        
        $mail->CharSet = "UTF8"; //設定郵件編碼        

        $mail->Username = "adonisnowman@gmail.com"; //設定驗證帳號        
        $mail->Password = "piqejtexftausgqo"; //設定驗證密碼        

        $mail->From = "adonisnowman@gmail.com"; //設定寄件者信箱        
        $mail->FromName = "測試人員"; //設定寄件者姓名        

        $mail->Subject = "PHPMailer 測試信件"; //設定郵件標題        
        $mail->Body = $body; //設定郵件內容        
        $mail->IsHTML(true); //設定郵件內容為HTML        
        $mail->AddAddress($Address, $text); //設定收件者郵件及名稱        

        if (!$mail->Send()) {
            return "Mailer Error: " . $mail->ErrorInfo;
        } else {
            return "Message sent!";
        }
    }
    public static function checkToken($tokenReceived)
    {

        $audience      = 'https://account.adonis.tw';
        $now           = new DateTimeImmutable();
        $issued        = $now->getTimestamp();
        $notBefore     = $now->modify('-1 minute')->getTimestamp();
        $expires       = $now->getTimestamp();
        $id            = 'abcd123456789';
        $issuer        = 'https://adonis.tw';

        // Defaults to 'sha512'
        $signer     = new Hmac();
        $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';

        // Parse the token
        $parser      = new Parser();

        // Phalcon\Security\JWT\Token\Token object
        $tokenObject = $parser->parse($tokenReceived);

        // Phalcon\Security\JWT\Validator object
        $validator = new Validator($tokenObject, 100); // allow for a time shift of 100

        // Throw exceptions if those do not validate
        $validator
            ->validateAudience($audience)
            ->validateExpiration($expires)
            ->validateId($id)
            ->validateIssuedAt($issued)
            ->validateIssuer($issuer)
            ->validateNotBefore($notBefore)
            ->validateSignature($signer, $passphrase);
    }
}
