<?php

use Phalcon\Http\Response;

class CronController extends BaseController
{

    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', 'angularjs.adonis.tw');
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
                    $data = array_map(function($value) { return str_replace(' ', '', $value); }, $data);
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
                $OneDalla = OneDalla::getObjectByItem(Tools::fix_element_Key($Insert,["order_number"]));
                if(!empty($OneDalla->UniqueID)){

                }else {
                    $Return = Models::insertTable($Insert,"OneDalla");
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
                $data = array_map(function($value) { return str_replace(' ', '', $value); }, $data);
                if ($IndexCount == 0)  {
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
                if(!empty($CtbcBank->UniqueID)){

                }else {
                    $Return = Models::insertTable($Insert,"CtbcBank");
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
                $data = array_map(function($value) { return str_replace(' ', '', $value); }, $data);
                if ($IndexCount == 0)  {
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
                if(!empty($CathayBk->UniqueID)){

                }else {
                    $Return = Models::insertTable($Insert,"CathayBk");
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
                $data = array_map(function($value) { return str_replace(' ', '', $value); }, $data);
                if( $IndexCount < 4 ) {
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
                if(!empty($KgiBank->UniqueID)){

                }else {
                    $Return = Models::insertTable($Insert,"KgiBank");
                }
                
              
            }
            fclose($handle);
        }
    }

    
}
