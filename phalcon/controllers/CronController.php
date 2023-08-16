<?php

use Phalcon\Http\Response;

class CronController extends BaseController
{

    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', 'www.xn--ehx388b.tw');
        session_start();
    }


    public function indexAction()
    {

        $Return = _Views::Init();
        $Return['ReDirect'] = "signEmail";
        $Return['UniqueID'] = "";
        $Return['Token'] = Tools::getToken();
        echo _Views::RedirectAdmin($Return);
    }

    //大樂透 http://lotto.arclink.com.tw/Lotto49jhdz.html https://zan01.com/lotto649/history/1
    public function Lotto649Action()
    {
        // header('Content-Type: text/html; charset=utf-8');
        $Url = "https://www.taiwanlottery.com.tw/lotto/Lotto649/history.aspx";
        file_put_contents("Number.txt", $Txt = file_get_contents($Url, false, Tools::file_get_https()));
        // Get the html 

        $xml = new DOMDocument();



        // Load the url's contents into the DOM
        libxml_use_internal_errors(true);
        $xml->loadHTML($Txt);

        $Check = new DomXPath($xml);
        $classname = " font_red18 tx_md";
        echo $CheckName = $Check->query("//div[contains(@class,'{$classname}')]", $xml)[0]->nodeValue;
        echo "<br/> " ;


            $classname = "td_hm";
        $finder = $Check->query("//table[contains(@class,'{$classname}')]", $xml->getElementsByTagName("table")[0]);
        foreach ($finder  as $link) {



            echo "<br/> ";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[0]->nodeValue) == "期別")
                echo trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[0]->nodeValue);
            echo "<br/> ";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[1]->nodeValue) == "開獎日")
                echo trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[1]->nodeValue);

            echo "<br/> ";


            echo ((int)date("Y") - 1911) . "/" . date("m/d");
            echo "<br/> ";

            if (trim($link->getElementsByTagName("tr")[4]->getElementsByTagName("td")[0]->nodeValue) == "大小順序")
                echo ($link->getElementsByTagName("tr")[4]->nodeValue);






            preg_match_all("/(?P<Number>[0-9]{2})/", $link->getElementsByTagName("tr")[4]->nodeValue, $Numbers);
            echo "<br/> " . $link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[1]->nodeValue;
            echo $Special = array_pop($Numbers['Number']);



            echo "<br/>" . $link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[0]->nodeValue;
            echo join(",", $Numbers['Number']);
            echo "<br/> ------- 以上是目前抓取的主要欄位，會存進資料庫，其他中獎金額，先不紀錄 ------- <br/> ";

            echo $xml->saveHTML($link);

            echo "<br/>------------------------------------<br/>";
        }
    }

    //威力彩 http://lotto.arclink.com.tw/Lotto388jhdz.html https://zan01.com/superlotto638/history/
    public function SuperLotto638Action()
    {
        // header('Content-Type: text/html; charset=utf-8');
        $Url = "https://www.taiwanlottery.com.tw/lotto/SuperLotto638/history.aspx";
        file_put_contents("Number.txt", $Txt = file_get_contents($Url, false, Tools::file_get_https()));
        // Get the html 

        $xml = new DOMDocument();



        // Load the url's contents into the DOM
        libxml_use_internal_errors(true);
        $xml->loadHTML($Txt);

        $Check = new DomXPath($xml);
        $classname = " font_red18 tx_md";
        echo $CheckName = $Check->query("//div[contains(@class,'{$classname}')]", $xml)[0]->nodeValue;
        echo "<br/> " ;


            $classname = "td_hm";
        $finder = $Check->query("//table[contains(@class,'{$classname}')]", $xml->getElementsByTagName("table")[0]);
        foreach ($finder  as $link) {



            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[0]->nodeValue) == "期別")
                echo trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[0]->nodeValue);
            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[1]->nodeValue) == "開獎日")
                echo trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[1]->nodeValue);

            echo "\n";


            echo ((int)date("Y") - 1911) . "/" . date("m/d");
            echo "\n";

            if (trim($link->getElementsByTagName("tr")[5]->getElementsByTagName("td")[0]->nodeValue) == "大小順序")
                echo ($link->getElementsByTagName("tr")[5]->nodeValue);






            preg_match_all("/(?P<Number>[0-9]{2})/", $link->getElementsByTagName("tr")[5]->nodeValue, $Numbers);
            echo "<br/> " . $link->getElementsByTagName("tr")[3]->getElementsByTagName("td")[1]->nodeValue;
            echo $Special = array_pop($Numbers['Number']);



            echo "<br/>" . $link->getElementsByTagName("tr")[3]->getElementsByTagName("td")[0]->nodeValue;
            echo join(",", $Numbers['Number']);

            echo "<br/> ------- 以上是目前抓取的主要欄位，會存進資料庫，其他中獎金額，先不紀錄 ------- <br/> ";
            echo $xml->saveHTML( $link );

            echo "<br/>------------------------------------<br/>";

        }
    }

    //3星彩 http://lotto.arclink.com.tw/Lotto3List.html https://zan01.com/l3d/history/
    public function Lotto3DAction()
    {
        // header('Content-Type: text/html; charset=utf-8');
        $Url = "https://www.taiwanlottery.com.tw/lotto/3D/history.aspx";
        file_put_contents("Number.txt", $Txt = file_get_contents($Url, false, Tools::file_get_https()));
        // Get the html 

        $xml = new DOMDocument();



        // Load the url's contents into the DOM
        libxml_use_internal_errors(true);
        $xml->loadHTML($Txt);

        $Check = new DomXPath($xml);
        $classname = " font_red18 tx_md";
        echo $CheckName = $Check->query("//div[contains(@class,'{$classname}')]", $xml)[0]->nodeValue;
        echo "<br/> " ;

        echo $xml->saveHTML($xml->getElementsByTagName("table")[0]->getElementsByTagName("table")[1]  );
        $finder = $xml->getElementsByTagName("table")[0]->getElementsByTagName("table");
        foreach ($finder  as $link) {



            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[0]->nodeValue) == "期別")
                echo trim($link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[0]->nodeValue);
            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[1]->nodeValue) == "日期")
                {
                    preg_match("/(?P<Date>[0-9]{3}\/[0-9]{1,2}\/[0-9]{1,2})/", trim($link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[1]->nodeValue), $Numbers); ;

                    echo  $Numbers['Date'];
                } 

            echo "\n";


            echo ((int)date("Y") - 1911) . "/" . date("m/d");
            echo "\n";

          
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[2]->nodeValue) == "獎號")
            echo  join("," ,str_split( (string) trim($link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[2]->nodeValue) ) );




            

            echo "<br/> ------- 以上是目前抓取的主要欄位，會存進資料庫，其他中獎金額，先不紀錄 ------- <br/> ";
            echo $xml->saveHTML( $link );

            echo "<br/>------------------------------------<br/>";

        }
    }

    //4星彩 http://lotto.arclink.com.tw/Lotto4List.html https://zan01.com/l4d/
    public function Lotto4DAction()
    {
        // header('Content-Type: text/html; charset=utf-8');
        $Url = "https://www.taiwanlottery.com.tw/lotto/4D/history.aspx";
        file_put_contents("Number.txt", $Txt = file_get_contents($Url, false, Tools::file_get_https()));
        // Get the html 

        $xml = new DOMDocument();



        // Load the url's contents into the DOM
        libxml_use_internal_errors(true);
        $xml->loadHTML($Txt);

        $Check = new DomXPath($xml);
        $classname = " font_red18 tx_md";
        echo $CheckName = $Check->query("//div[contains(@class,'{$classname}')]", $xml)[0]->nodeValue;
        echo "<br/> " ;

        echo $xml->saveHTML($xml->getElementsByTagName("table")[0]->getElementsByTagName("table")[1]  );
        $finder = $xml->getElementsByTagName("table")[0]->getElementsByTagName("table");
        foreach ($finder  as $link) {



            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[0]->nodeValue) == "期別")
                echo trim($link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[0]->nodeValue);
            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[1]->nodeValue) == "日期")
                {
                    preg_match("/(?P<Date>[0-9]{3}\/[0-9]{1,2}\/[0-9]{1,2})/", trim($link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[1]->nodeValue), $Numbers); ;

                    echo  $Numbers['Date'];
                } 

            echo "\n";


            echo ((int)date("Y") - 1911) . "/" . date("m/d");
            echo "\n";

          
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[2]->nodeValue) == "獎號")
            echo  join("," ,str_split( (string) trim($link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[2]->nodeValue) ) );




            

            echo "<br/> ------- 以上是目前抓取的主要欄位，會存進資料庫，其他中獎金額，先不紀錄 ------- <br/> ";
            echo $xml->saveHTML( $link );

            echo "<br/>------------------------------------<br/>";

        }
    }

    //今彩539 http://lotto.arclink.com.tw/Lotto39jhdz.html https://zan01.com/dailycash/history/
    public function DailycashAction()
    {
        // header('Content-Type: text/html; charset=utf-8');
        $Url = "https://www.taiwanlottery.com.tw/lotto/Dailycash/history.aspx";
        file_put_contents("Number.txt", $Txt = file_get_contents($Url, false, Tools::file_get_https()));
        // Get the html 

        $xml = new DOMDocument();



        // Load the url's contents into the DOM
        libxml_use_internal_errors(true);
        $xml->loadHTML($Txt);

        $Check = new DomXPath($xml);
        $classname = " font_red18 tx_md";
        echo $CheckName = $Check->query("//div[contains(@class,'{$classname}')]", $xml)[0]->nodeValue;
        echo "<br/> " ;

        
        $finder = $xml->getElementsByTagName("table")[1]->getElementsByTagName("table");
        foreach ($finder  as $link) {



            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[0]->nodeValue) == "期別")
                echo trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[0]->nodeValue);
            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[1]->nodeValue) == "日期")
                {
                    preg_match("/(?P<Date>[0-9]{3}\/[0-9]{1,2}\/[0-9]{1,2})/", trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[1]->nodeValue), $Numbers); ;

                    echo  $Numbers['Date'];
                } 

            echo "\n";

            echo ((int)date("Y") - 1911) . "/" . date("m/d");
            echo "\n";






            
            preg_match("/大小順序[\s\S]+/", $link->getElementsByTagName("tr")[2]->nodeValue, $Numbers);
            preg_match_all("/(?P<Number>[0-9]{2})/", $Numbers[0], $Numbers);
           
            echo "<br/> " . $link->getElementsByTagName("tr")[2]->getElementsByTagName("td")[1]->nodeValue;
            echo join(",",  $Numbers['Number']);


          

            echo "<br/> ------- 以上是目前抓取的主要欄位，會存進資料庫，其他中獎金額，先不紀錄 ------- <br/> ";
            echo $xml->saveHTML( $link );

            echo "<br/>------------------------------------<br/>";

        }
    }

    //雙贏彩
    public function Lotto1224Action()
    {
        // header('Content-Type: text/html; charset=utf-8');
        $Url = "https://www.taiwanlottery.com.tw/lotto/Lotto1224/history.aspx";
        file_put_contents("Number.txt", $Txt = file_get_contents($Url, false, Tools::file_get_https()));
        // Get the html 

        $xml = new DOMDocument();



        // Load the url's contents into the DOM
        libxml_use_internal_errors(true);
        $xml->loadHTML($Txt);

        $Check = new DomXPath($xml);
        $classname = " font_red18 tx_md";
        echo $CheckName = $Check->query("//div[contains(@class,'{$classname}')]", $xml)[0]->nodeValue;
        echo "<br/> " ;

        
        $finder = $xml->getElementsByTagName("table")[1]->getElementsByTagName("table");
        foreach ($finder  as $link) {



            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[0]->nodeValue) == "期別")
                echo trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[0]->nodeValue);
            echo "\n";
            if (trim($link->getElementsByTagName("tr")[0]->getElementsByTagName("td")[1]->nodeValue) == "開獎日")
                {
                    preg_match("/(?P<Date>[0-9]{3}\/[0-9]{1,2}\/[0-9]{1,2})/", trim($link->getElementsByTagName("tr")[1]->getElementsByTagName("td")[1]->nodeValue), $Numbers); ;

                    echo  $Numbers['Date'];
                } 

            echo "\n";

            echo ((int)date("Y") - 1911) . "/" . date("m/d");
            echo "\n";






            
            preg_match_all("/(?P<Number>[0-9]{2})/", $link->getElementsByTagName("tr")[4]->nodeValue, $Numbers);
           
            echo "<br/> " . $link->getElementsByTagName("tr")[4]->getElementsByTagName("td")[0]->nodeValue;
            echo join(",",  $Numbers['Number']);


          

            echo "<br/> ------- 以上是目前抓取的主要欄位，會存進資料庫，其他中獎金額，先不紀錄 ------- <br/> ";
            echo $xml->saveHTML( $link );

            echo "<br/>------------------------------------<br/>";

        }
    }



    public function OneDallaAction()
    {

        set_time_limit(0);
        $field_key = [
            "created_time" => "建單時間",
            "channel_name" => "渠道名稱",
            "order_number" => "訂單號",
            "business_number" => "商戶號",
            "Amount" => "交易金額",
            "Amount_date" => "交易日期",
            "Bank_Code" => "銀行代碼",
            "Bank_account" => "銀行帳號",
            "Payee_Name" => "收款人姓名",
            "status" => "狀態",
        ];

        $keys = array_keys($field_key);

        $IndexCount = 0;
        $target_file = "one_dalla.csv";
        if (($handle = fopen($target_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {
                $data = array_map('trim', $data);


                if ($IndexCount == 0) {
                    $data = array_map(function ($value) {
                        return str_replace(' ', '', $value);
                    }, $data);
                    $i = 0;
                    foreach ($field_key as $words) {
                        if (strcmp($data[$i++], $words) < 0) {
                            echo "error field";
                            exit;
                        }
                    }

                    $IndexCount++;
                    continue;
                }

                $Insert = [];

                for ($c = 0; $c < count($keys); $c++) {
                    if (!isset($keys[$c])) continue;
                    $Insert[$keys[$c]] = trim($data[$c]);
                }
                $OneDalla = OneDalla::getObjectByItem(Tools::fix_element_Key($Insert, ["order_number"]));
                if (!empty($OneDalla->UniqueID)) {
                } else {
                    $Return = Models::insertTable($Insert, "OneDalla");
                }
            }
            fclose($handle);
        }
    }

    public function CtbcBankAction()
    {

        set_time_limit(0);
        $field_key = [
            "created_time" => "日期",
            "Summary" => "摘要",
            "Currency" => "幣別",
            "payout" => "支出金額",
            "deposits" => "存入金額",
            "balance" => "餘額",
            "Remark" => "備註",
            "Bank_account" => "轉出入帳號",
            "Mark" => "註記",
        ];

        $keys = array_keys($field_key);

        $IndexCount = 0;
        $target_file = "CtbcBank.csv";
        if (($handle = fopen($target_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {
                $data = array_map('trim', $data);
                $data = array_map(function ($value) {
                    return str_replace(' ', '', $value);
                }, $data);
                if ($IndexCount == 0) {
                    $i = 0;
                    foreach ($field_key as $words) {
                        if (strcmp($data[$i++], $words) < 0) {
                            echo "error field";
                            exit;
                        }
                    }

                    $IndexCount++;
                    continue;
                }

                $Insert = [];

                for ($c = 0; $c < count($keys); $c++) {
                    if (!isset($keys[$c])) continue;
                    $Insert[$keys[$c]] = trim($data[$c]);
                }
                $CtbcBank = CtbcBank::getObjectByItem(CtbcBank::resetValues($Insert));
                if (!empty($CtbcBank->UniqueID)) {
                } else {
                    $Return = Models::insertTable($Insert, "CtbcBank");
                }
            }
            fclose($handle);
        }
    }

    public function CathayBkAction()
    {

        set_time_limit(0);
        $field_key = [
            "created_time" => "帳務日期",
            "payout" => "提出金額",
            "deposits" => "存入金額",
            "balance" => "餘額",
            "transaction_code" => "交易代號",
            "Cheque_number" => "支票號碼",
            "Remark" => "備註",
            "Bank_account" => "帳號",
            "Bank_accountAs" => "對方帳號",
            "Trading_branch" => "交易分行",
            "virtual_account" => "虛擬帳號",
        ];

        $keys = array_keys($field_key);

        $IndexCount = 0;
        $target_file = "cathaybk.csv";
        if (($handle = fopen($target_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {
                $data = array_map('trim', $data);
                $data = array_map(function ($value) {
                    return str_replace(' ', '', $value);
                }, $data);
                if ($IndexCount == 0) {
                    $i = 0;
                    foreach ($field_key as $words) {
                        if (strcmp($data[$i++], $words) < 0) {
                            echo "error field";
                            exit;
                        }
                    }

                    $IndexCount++;
                    continue;
                }

                $Insert = [];

                for ($c = 0; $c < count($keys); $c++) {
                    if (!isset($keys[$c])) continue;
                    $Insert[$keys[$c]] = trim($data[$c]);
                }
                $CathayBk = CathayBk::getObjectByItem(CathayBk::resetValues($Insert));
                if (!empty($CathayBk->UniqueID)) {
                } else {
                    $Return = Models::insertTable($Insert, "CathayBk");
                }
            }
            fclose($handle);
        }
    }

    public function KgiBankAction()
    {

        set_time_limit(0);
        $field_key = [
            "Uniform_numbers" => "統一編號",
            "Bank_account" => "帳號",
            "created_time" => "交易日期",
            "account_date" => "入帳日期",
            "Summary" => "摘要",
            "payout" => "支出金額",
            "deposits" => "存入金額",
            "balance" => "帳戶餘額",
            "Remark" => "備註",
        ];

        $keys = array_keys($field_key);

        $IndexCount = 0;
        $target_file = "KgiBank.csv";
        if (($handle = fopen($target_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {
                $data = array_map('trim', $data);
                $data = array_map(function ($value) {
                    return str_replace(' ', '', $value);
                }, $data);
                if ($IndexCount < 4) {
                    $IndexCount++;
                    continue;
                }
                if ($IndexCount == 4) {
                    $i = 0;
                    foreach ($field_key as $words) {
                        if (strcmp($data[$i++], $words) < 0) {
                            echo "error field";
                            exit;
                        }
                    }

                    $IndexCount++;
                    continue;
                }

                $Insert = [];

                for ($c = 0; $c < count($keys); $c++) {
                    if (!isset($keys[$c])) continue;
                    $Insert[$keys[$c]] = trim($data[$c]);
                }
                $KgiBank = KgiBank::getObjectByItem(KgiBank::resetValues($Insert));
                if (!empty($KgiBank->UniqueID)) {
                } else {
                    $Return = Models::insertTable($Insert, "KgiBank");
                }
            }
            fclose($handle);
        }
    }
}
