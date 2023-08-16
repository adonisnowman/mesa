<?php

use Phalcon\Http\Response;
use Orhanerday\OpenAi\OpenAi;

class IndexController extends BaseController
{
    public static $arrContextOptions = [];

    public function initialize()
    {
        session_name("swoole");
        session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST']);
        session_start();

    }

    public function infoAction()
    {
       

        $open_ai_key = getenv('OPENAI_API_KEY');
        $open_ai = new OpenAi($open_ai_key);
        
        $chat = $open_ai->chat([
           'model' => 'gpt-3.5-turbo',
           'messages' => [
               [
                   "role" => "system",
                   "content" => "You are a helpful assistant."
               ],
               [
                   "role" => "user",
                   "content" => "Who won the world series in 2020?"
               ],
               [
                   "role" => "assistant",
                   "content" => "The Los Angeles Dodgers won the World Series in 2020."
               ],
               [
                   "role" => "user",
                   "content" => "Where was it played?"
               ],
           ],
           'temperature' => 1.0,
           'max_tokens' => 4000,
           'frequency_penalty' => 0,
           'presence_penalty' => 0,
        ]);
        
        
        var_dump($chat);
        echo "<br>";
        echo "<br>";
        echo "<br>";
        // decode response
        $d = json_decode($chat);
        // Get Content
        echo($d->choices[0]->message->content);
        exit;
        $Insert = [];
        $Insert['UniqueID_MessageType'] = "95A1y0r097n97489921";
        $Insert['action_offshelf'] = 0;
        $Insert['used_offshelf'] = 0;
        $Insert['action_cost'] = 30;
        $Insert['used_cost'] = 30;

        $MessageTypeDefaultCost = models::insertTable($Insert, "UserMessageTypeDefaultCosts");

        var_dump($MessageTypeDefaultCost['SystemMsg']);
        exit;
        $MongoDB = new MongoAdonis($this->MongoDB);
        $options = [];

        $options['text'] = "adonis is ";


        $MongoDB::insert("adonis",$options);
    }
    //登入登出功能
    public function LogoutAction()
    {
        unset($_SESSION[Tools::getIp()]['ReDirect']);
        session_destroy();
        $Return['ReDirect'] = "sign-in";
        return $Return;
    }
    public function indexAction()
    {
       
        
        if (!empty($_GET['Token'])) Tools::checkToken($_GET['Token']);
        

        //預設模板讀取 www.xn--ehx388b.tw
        $Return = _Views::Init();
        if ($_SERVER['SERVER_NAME'] == "taiwanlottery.xn--ehx388b.tw") $Return['header'] = _Views::RedirectAdmin(["ReDirect" => "User_header"]);     
        else if ($_SERVER['SERVER_NAME'] == "taiwanlotteryadmin.xn--ehx388b.tw") $Return['header'] = _Views::RedirectAdmin(["ReDirect" => "Home_header"]);     
        else if (Tools::getIp() == Tools::ServerIp() || in_array(Tools::getIp(),  _Accounts::AllowIps())) $Return['header'] = _Views::RedirectAdmin(["ReDirect" => "Home_header"]);
        else $Return['header'] = _Views::RedirectAdmin(["ReDirect" => "User_header"]);

        if (!empty($_SESSION[Tools::getIp()]['ReDirect']))  $Return['ReDirect'] = $_SESSION[Tools::getIp()]['ReDirect'];
        else if ( $_SERVER['SERVER_NAME'] == "taiwanlotteryadmin.xn--ehx388b.tw" && ( Tools::getIp() == Tools::ServerIp() || in_array(Tools::getIp(),  _Accounts::AllowIps()))) $Return['ReDirect'] = "sign-in";

        else $Return['ReDirect'] = "Novelindex";

        

        //預設 新增修改 模板讀取
        $Return['Input_Nav'] = _Views::RedirectAdmin(["ReDirect" => "Input_Nav"]);

        //列表頁面
        $Item['ViewsPath'] = "admin";
        $RedirectAdmin = RedirectAdmin::getListByItem($Item);

        
        foreach ($RedirectAdmin as $item) {
            $Return[$item['ReDirect']] = _Views::RedirectAdmin(["ReDirect" => $item['ReDirect']]);
        }

       
        if (!empty($Return['ReDirect']))
            $Echo = _Views::RedirectAdmin($Return);
            _UsersApi::insertUserFootPoint(__CLASS__,$Return['ReDirect']);
        if (!empty($_SESSION[Tools::getIp()]['History'])) {
            $Return['ReDirect'] = $_SESSION[Tools::getIp()]['History'];
            $History = _Views::RedirectAdmin($Return);
        }



        if (!empty($Echo)) {            
            echo $Echo;
        }
        else if (!empty($History)) {
            echo $History;
            _UsersApi::insertUserFootPoint(__CLASS__,$Return['ReDirect']);
        }
        else {
            unset($_SESSION[Tools::getIp()]['ReDirect']);
            $Return['ReDirect'] = "Novelindex";
            echo _Views::RedirectAdmin($Return);
            _UsersApi::insertUserFootPoint(__CLASS__,$Return['ReDirect']);
        }

       
    }
}
