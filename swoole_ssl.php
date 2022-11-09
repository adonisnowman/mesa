<?php

include "phalcon/library/_UniqueID.php";
include "phalcon/library/Tools.php";





use Swoole\Websocket\Server;
use Swoole\WebSocket\Frame;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;

date_default_timezone_set('asia/taipei');

// Table is a shared memory table that can be used across connections
$messages = new Table(1024);
// we need to set the types that the table columns support - just like a RDB
$messages->column('id', Table::TYPE_INT, 11);
$messages->column('client', Table::TYPE_INT, 4);
$messages->column('username', Table::TYPE_STRING, 64);
$messages->column('message', Table::TYPE_STRING, 255);
$messages->create();

$connections = new Table(1024);
$connections->column('client', Table::TYPE_INT, 4);
$connections->column('UniqueID', Table::TYPE_STRING, 20);
$connections->column('shortUniqueID', Table::TYPE_STRING, 20);
$connections->column('UniqueID_UsersToken', Table::TYPE_STRING, 750);
$connections->column('UniqueID_UsersLoginLogs', Table::TYPE_STRING, 750);
$connections->column('user_md5', Table::TYPE_STRING, 750);

$connections->create();
//创建websocket服务器对象，监听0.0.0.0:9501端口，开启SSL隧道
$ws = new swoole_websocket_server("0.0.0.0", 9509, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$Response = new Response();
$fullchain = "/etc/letsencrypt/live/swoole.bestaup.com/fullchain.pem";
$privkey = "/etc/letsencrypt/live/swoole.bestaup.com/privkey.pem";

if (file_exists("/etc/letsencrypt/live/adonis.tw-0002/fullchain.pem")) $fullchain = "/etc/letsencrypt/live/adonis.tw-0002/fullchain.pem";
if (file_exists("/etc/letsencrypt/live/adonis.tw-0002/privkey.pem")) $privkey = "/etc/letsencrypt/live/adonis.tw-0002/privkey.pem";


function SendAction($ws, $fd, $Action)
{
    var_dump($Action);
    $ws->push($fd, json_encode($Action));
}

$SwooleSetting = [
    'daemonize' => false, //守护进程化。
    //配置SSL证书和密钥路径
    'ssl_cert_file' => $fullchain,
    'ssl_key_file'  => $privkey
];
var_dump($SwooleSetting);
//配置参数
$ws->set($SwooleSetting);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) use ($connections) {


    if ((int)  round(((float)microtime(true))) == $request->server['master_time'])
        $shortUniqueID = _UniqueID::shortUniqueID($request->server['master_time']);
    else
        $shortUniqueID = _UniqueID::shortUniqueID();


    $user_md5 = md5($request->header['user-agent'] . $request->server['remote_addr']);
    echo "client-{$request->fd} is open [{$user_md5}] \n";

    $url = "http://mesa.adonis.tw/Swoole";


    $Data['shortUniqueID'] = $shortUniqueID;
    $Data['user_md5'] = $user_md5;
    $Data['query_string'] = $request->server['query_string'];
    $Data['HTTP_HOST'] = $request->header['origin'];
    $Data['REMOTE_ADDR'] = $request->server['remote_addr'];
    $Data['HTTP_USER_AGENT'] = $request->header['user-agent'];
  
    $post['Action'] = "Connect";
    $post['Data'] = json_encode($Data);

    $Return = Tools::httpPost($url, $post, true);

    if (!empty($Return['ErrorMsg'])) {
        $Action = [];
        $Action['Type'] = "Toast";
        $Action['Toast'][] = $Return['ErrorMsg'];

        SendAction($ws, $request->fd, $Action);

        return false;
    }

    if (!empty($Return['Action']) &&  !empty($Return[$Return['Action']])) 
        $Return = Tools::fix_element_Key($Return[$post['Action']], ["UniqueID", "shortUniqueID", "UniqueID_UsersToken", "UniqueID_UsersLoginLogs", "user_md5"]);
    else  return $ws->disconnect($request->fd, 1003, 'Pleace Login first');


    $Return['client'] = $request->fd;
    $connections->set($request->fd, $Return);
});
//



//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use ($messages, $connections) {

    // frame data comes in as a string
    $output = json_decode($frame->data, true);
    echo "Message: \n";

    $client = $connections->get($frame->fd);
    var_dump($client,$output);
    if ($output['user_md5'] == $client['user_md5']  && $output['UniqueID'] == $client['UniqueID_UsersLoginLogs']) {

        if (!empty($output['Action']) && $output['shortUniqueID'] == $client['shortUniqueID']) {

            switch ($output['Action']) {
                case 'MessageToUsers':
                    $MessageUsers = Tools::fix_array_Key( $output['MessageUsers'] , "shortUniqueID" );
                 
                    foreach ($connections as $client) {
                        $Action = [];
                        $Action['MessageTokenUniqueID'] = $output['MessageTokenUniqueID'];
                        $Action['Type'] = "Toast";
                        $Action['Toast'][] = "你有新訊息:" . $output['MessageTokenUniqueID'];
                        if( in_array( $client['shortUniqueID'] , $MessageUsers ) )
                            SendAction($ws, $client['client'], $Action);
                    }


                    break;
                default:
                    $Action = [];
                    $Action['Type'] = "Toast";
                    $Action['Toast'][] = "本次登入連線短碼:" . $client['shortUniqueID'];
                    $Action['Toast'][] = "您暫時無法使用此類型的訊息發送功能";
                    SendAction($ws, $frame->fd, $Action);
                    break;
            }

            return false;
        }

        $url = "http://mesa.adonis.tw/Swoole";
        $Data['shortUniqueID'] = $client['shortUniqueID'];
       
        $post['Action'] = "MessageToken";
        $post['Data'] = json_encode($Data);

        $Return = Tools::httpPost($url, $post , true);
       
        if (!empty($Return['ErrorMsg'])) {
            $Action = [];
            $Action['Type'] = "Toast";
            $Action['Toast'][] = "本次登入連線短碼:" . $Return['ErrorMsg'];
            SendAction($ws, $frame->fd, $Action);
        }

        if (!empty($Return['MessageToken'])) $MessageToken = $Return['MessageToken'];
       
        if (!empty($output['account'])) {
            //首次連線訊息
            $Action = [];
            $Action['shortUniqueID'] = $client['shortUniqueID'];
            $Action['Type'] = "Toast";
            $Action['Toast'][] = "本次登入連線短碼:" . $client['shortUniqueID'];
            $Action['Toast'][] = "首次連線";
            SendAction($ws, $frame->fd, $Action);

            //傳送線上名單
            $Action = [];
            $Action['Type'] = "Connections";
            foreach ($connections as $client) {
                $Action["Connections"][] = ["shortUniqueID" => $client["shortUniqueID"] ];
            }

            foreach ($connections as $client) {

                SendAction($ws, $client['client'], $Action);
            }

        } else if (!empty($output['MessageType']) && !empty($MessageToken[$output['MessageType']]) && $output['shortUniqueID'] == $client['shortUniqueID']) {


            $MessageType = $client;
            unset($MessageType['client']);
            $MessageType['MessageType'] = $output['MessageType'];

            $Action = [];
            $Action['Type'] = "MessageType";
            $Action['MessageType'] = $MessageType;
            $Action['MessageToken'] = $MessageToken[$output['MessageType']];
            SendAction($ws, $frame->fd, $Action);
        } else {
            $Action = [];
            $Action['Type'] = "Toast";
            $Action['Toast'][] = "本次登入連線短碼:" . $client['shortUniqueID'];
            $Action['Toast'][] = "您暫時無法使用此類型的訊息發送功能";
            SendAction($ws, $frame->fd, $Action);
        }


        
    } else {

        $Action = [];
        $Action['Type'] = "Action";
        $Action['Action'] = " sharedData.Logout(); ";
        SendAction($ws, $frame->fd, $Action);
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($connections) {
    echo "client-{$fd} is closed\n";
    $DisClient = $connections->get($fd);


    $url = "http://mesa.adonis.tw/Swoole";

    $Data = $DisClient;
    unset($Data['client']);
   
    $post['Action'] = "DisConnect";
    $post['Data'] = json_encode($Data);

    $Return = Tools::httpPost($url, $post);
   
    foreach ($connections as $client) {

        if ((int) $client['client'] == $fd) continue;

        $Action = [];
        $Action['DisClient'] = $DisClient['shortUniqueID'];
        $Action['Type'] = "Toast";
        $Action['Toast'][] = "本次關閉連線短碼:" . $Action['DisClient'];
        SendAction($ws, $client['client'], $Action);
    }

    //傳送線上名單
    $Action = [];
    $Action['Type'] = "Connections";
    foreach ($connections as $client) {
        $Action["Connections"][] = ["shortUniqueID" => $client["shortUniqueID"] ];
    }

    foreach ($connections as $client) {

        SendAction($ws, $client['client'], $Action);
    }


    $connections->del($fd);
});

$ws->start();
