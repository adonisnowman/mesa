<?php

include "phalcon/library/_UniqueID.php";
include "phalcon/library/Tools.php";




use Swoole\Websocket\Server;
use Swoole\WebSocket\Frame;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;
use Swoole\Client;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

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
$connections->column('mesaUniqueID', Table::TYPE_STRING, 20);
$connections->column('UniqueID_UsersToken', Table::TYPE_STRING, 750);
$connections->column('UniqueID_UsersLoginLogs', Table::TYPE_STRING, 20);
$connections->column('user_md5', Table::TYPE_STRING, 750);
$connections->column('soakedId', Table::TYPE_STRING, 50);
$connections->column('UniqueID_QRcodeSoaked', Table::TYPE_STRING, 50);
$connections->column('QRcodeSoaked_code', Table::TYPE_STRING, 50);
$connections->column('NickName', Table::TYPE_STRING, 50);

$connections->create();


$fullchain = "/etc/letsencrypt/live/swoole.bestaup.com/fullchain.pem";
$privkey = "/etc/letsencrypt/live/swoole.bestaup.com/privkey.pem";
$BestaupDefault = (file_exists($fullchain));
if (file_exists("mycert.pem"))
     $fullchain = "mycert.pem";
if (file_exists("private.key")) 
    $privkey = "private.key";

$SwoolePort  = ($BestaupDefault) ? "9501" : "9501";
//创建websocket服务器对象，监听0.0.0.0:9501端口，开启SSL隧道
$ws = new Server("0.0.0.0", $SwoolePort, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$Response = new Response();

function SendAction($ws, $fd, $Action)
{
    var_dump($Action);
    $ws->push($fd, json_encode($Action));
}

function ReFreshConnections($ws, $UniqueID_QRcodeSoaked,  $connections)
{

    $Action = [];
    $Action['Type'] = "Connections";
    foreach ($connections as $client) {

        if ($client['UniqueID_QRcodeSoaked'] == $UniqueID_QRcodeSoaked)
            $Action["Connections"][] = Tools::fix_element_Key($client, ["mesaUniqueID", "NickName"]);
    }
    foreach ($connections as $client) {

        SendAction($ws, $client['client'], $Action);
    }
}

function DisConnect($ws, $client)
{

    $ws->disconnect((int) $client, 1003, 'Pleace Login first');
}


$max_request = 100;
$SwooleSetting = [

    // Process
    'daemonize' => 1,
    'user' => 'cfd888',
    'group' => 'cfd888',
    'chroot' => '/data/server/',
    'open_cpu_affinity' => true,
    'cpu_affinity_ignore' => [0, 1],
    'pid_file' => __DIR__ . '/server.pid',

    // Server
    'reactor_num' => 8,
    'worker_num' => 2,
    'message_queue_key' => 'mq1',
    'dispatch_mode' => 2,
    'discard_timeout_request' => true,
    // 'dispatch_func' => 'my_dispatch_function',

    // Worker
    'max_request' => $max_request,
    'max_request_grace' => $max_request / 2,

    // HTTP Server max execution time, since v4.8.0
    // 'max_request_execution_time' => 30, // 30s

    // Task worker
    // 'task_ipc_mode' => 1,
    // 'task_max_request' => 100,
    // 'task_tmpdir' => '/tmp',
    // 'task_worker_num' => 8,
    // 'task_enable_coroutine' => true,
    // 'task_use_object' => true,

    // Logging
    // 'log_level' => 1,
    // 'log_file' => '/data/swoole.log',
    // 'log_rotation' => SWOOLE_LOG_ROTATION_DAILY,
    // 'log_date_format' => '%Y-%m-%d %H:%M:%S',
    // 'log_date_with_microseconds' => false,
    // 'request_slowlog_file' => false,

    // Enable trace logs
    // 'trace_flags' => SWOOLE_TRACE_ALL,

    // TCP
    'input_buffer_size' => 2097152,
    'buffer_output_size' => 32 * 1024 * 1024, // byte in unit
    'tcp_fastopen' => false,
    'max_conn' => 1000,
    'tcp_defer_accept' => 5,
    'open_tcp_keepalive' => true,
    'open_tcp_nodelay' => false,
    // 'pipe_buffer_size' => 32 * 1024*1024,
    'socket_buffer_size' => 128 * 1024 * 1024,

    // Kernel
    'backlog' => 512,
    'kernel_socket_send_buffer_size' => 65535,
    'kernel_socket_recv_buffer_size' => 65535,

    // TCP Parser
    'open_eof_check' => true,
    'open_eof_split' => true,
    'package_eof' => '\r\n',
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_body_offset' => 8,
    'package_length_offset' => 8,
    'package_max_length' => 2 * 1024 * 1024, // 2MB
    // 'package_length_func' => 'my_package_length_func',

    // Coroutine
    'enable_coroutine' => true,
    'max_coroutine' => 3000,
    'send_yield' => false,

    // tcp server
    'heartbeat_idle_time' => 600,
    'heartbeat_check_interval' => 60,
    'enable_delay_receive' => true,
    'enable_reuse_port' => true,
    'enable_unsafe_event' => true,

    // Protocol
    'open_http_protocol' => true,
    'open_http2_protocol' => true,
    'open_websocket_protocol' => true,
    'open_mqtt_protocol' => true,

    // HTTP2
    // 'http2_header_table_size' => 4095,
    // 'http2_initial_window_size' => 65534,
    // 'http2_max_concurrent_streams' => 1281,
    // 'http2_max_frame_size' => 16383,
    // 'http2_max_header_list_size' => 4095,

    // SSL
    'ssl_cert_file' => __DIR__ . '/config/ssl.cert',
    'ssl_key_file' => __DIR__ . '/config/ssl.key',
    // 'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
    // 'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD, // removed from v4.5.4
    // 'ssl_protocols' => 0, // added from v4.5.4
    // 'ssl_verify_peer' => false,
    // 'ssl_sni_certs' => [
    //     "cs.php.net" => [
    //         'ssl_cert_file' => __DIR__ . "/config/sni_server_cs_cert.pem",
    //         'ssl_key_file' => __DIR__ . "/config/sni_server_cs_key.pem"
    //     ],
    //     "uk.php.net" => [
    //         'ssl_cert_file' => __DIR__ . "/config/sni_server_uk_cert.pem",
    //         'ssl_key_file' => __DIR__ . "/config/sni_server_uk_key.pem"
    //     ],
    //     "us.php.net" => [
    //         'ssl_cert_file' => __DIR__ . "/config/sni_server_us_cert.pem",
    //         'ssl_key_file' =>  __DIR__ . "/config/sni_server_us_key.pem",
    //     ],
    // ],

    // Static Files
    'document_root' => __DIR__ . '/',
    'enable_static_handler' => true,
    'static_handler_locations' => ['/static', '/app/images'],
    'http_index_files' => ['index.html', 'index.txt'],

    // Source File Reloading
    'reload_async' => true,
    'max_wait_time' => 30,

    // HTTP Server
    'http_parse_post' => true,
    'http_parse_cookie' => true,
    'upload_tmp_dir' => '/tmp',

    // Compression
    'http_compression' => true,
    'http_compression_level' => 3, // 1 - 9
    'compression_min_length' => 20,


    // Websocket
    'websocket_compression' => true,
    'open_websocket_close_frame' => false,
    'open_websocket_ping_frame' => false, // added from v4.5.4
    'open_websocket_pong_frame' => false, // added from v4.5.4

    // TCP User Timeout
    'tcp_user_timeout' => 0,

    // DNS Server
    'dns_server' => '8.8.8.8:53',
    'dns_cache_refresh_time' => 60,
    'enable_preemptive_scheduler' => 0,

    // 'open_fastcgi_protocol' => 0,
    'open_redis_protocol' => 0,

    'stats_file' => './stats_file.txt', // removed from v4.9.0

    // 'enable_object' => true,

];


if ($BestaupDefault) $SwooleUrl = "https://adonis.bestaup.com/Swoole";
else $SwooleUrl = "http://mesa.adonis.tw/Swoole";

echo $SwooleUrl;

//環境參數設定
$SwooleSetting = [];
$SwooleSetting['daemonize'] = false;
$SwooleSetting['ssl_cert_file'] = $fullchain;
$SwooleSetting['ssl_key_file'] = $privkey;
//配置参数
$ws->set($SwooleSetting);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) use ($connections, $SwooleUrl) {

    $key = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";

    if ((int)  round(((float)microtime(true))) == $request->server['master_time'])
        $mesaUniqueID = _UniqueID::shortUniqueID($request->server['master_time'],  $key);
    else
        $mesaUniqueID = _UniqueID::shortUniqueID(false,  $key);


    $user_md5 = md5($request->header['user-agent'] . $request->server['remote_addr']);
    echo "client-{$request->fd} is open [{$user_md5}] \n";




    $Data['mesaUniqueID'] = $mesaUniqueID;
    $Data['user_md5'] = $user_md5;
    $Data['query_string'] = $request->server['query_string'];
    $Data['HTTP_HOST'] = $request->header['origin'];
    $Data['REMOTE_ADDR'] = $request->server['remote_addr'];
    $Data['HTTP_USER_AGENT'] = $request->header['user-agent'];

    $post['Action'] = "MesaConnect";
    $post['Data'] = json_encode($Data);

    $Return = Tools::httpPost($SwooleUrl, $post, true);

    if (!empty($Return['ErrorMsg'])) {
        $Action = [];
        $Action['Type'] = "Toast";
        $Action['Toast'][] = $Return['ErrorMsg'];

        SendAction($ws, $request->fd, $Action);

        return false;
    }

    if (empty($Return['Action']) ||  empty($Return[$Return['Action']])) return DisConnect($ws, (int) $request->fd);

    $Return = Tools::fix_element_Key($Return[$post['Action']], ["UniqueID", "mesaUniqueID", "UniqueID_UsersToken", "UniqueID_UsersLoginLogs", "user_md5"]);
    $Return['client'] = $request->fd;
    var_dump("client_adonis",$Return);
    $connections->set($request->fd, $Return);
});
//



//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use ($messages, $connections, $SwooleUrl) {



    $output = json_decode($frame->data, true);
    echo "Message: \n";

    $client = $connections->get($frame->fd);
   
    if ($output['user_md5'] == $client['user_md5']  && $output['UniqueID'] == $client['UniqueID_UsersLoginLogs']) {


        if (empty($output['mesaUniqueID'])) {
            $Action = [];
            $Action['mesaUniqueID'] = $client['mesaUniqueID'];
            $Action['Type'] = "Toast";
            $Action['Toast'][] = "本次登入連線短碼:" . $client['mesaUniqueID'];
            $Action['Toast'][] = "首次連線";
            SendAction($ws, $frame->fd, $Action);
        }
        //首次連線訊息



        if (!empty($output['Action']) ) {

            switch ($output['Action']) {
                case 'MessageToUsers':
                    $clientTemp = $connections->get($frame->fd);
                    foreach ($connections as $client) {
                        $Action = [];
                        $Action['Type'] = "soakedMessageList";
                        $Action[$Action['Type']]['NickName'] = $client['NickName'];
                        $Action[$Action['Type']]['mesaUniqueID'] = $output['mesaUniqueID'];
                        $Action[$Action['Type']]['QRcodeSoaked_code'] = $client['QRcodeSoaked_code'];
                        $Action[$Action['Type']]['SoakedMessage'] = $output['SoakedMessage'];

                        if ($client['UniqueID_QRcodeSoaked'] == $clientTemp['UniqueID_QRcodeSoaked'])
                            SendAction($ws, $client['client'], $Action);
                    }
                    break;
                default:
                    $Action = [];
                    $Action['Type'] = "Toast";
                    $Action['Toast'][] = "本次登入連線短碼:" . $client['mesaUniqueID'];
                    $Action['Toast'][] = "您暫時無法使用此類型的訊息發送功能";
                    SendAction($ws, $frame->fd, $Action);
                    break;
            }

            return false;
        }
        //    會員判斷
        if (!empty($output['account']) && !empty($output['mobile'])) {

            $Data = Tools::fix_element_Key($output, ['account', 'mobile', 'UniqueID']);
            $post['Action'] = "UserSoaked";
            $post['Data'] = json_encode($Data);

            $Return = Tools::httpPost($SwooleUrl, $post, true);

            $Action = [];
            $Action['Type'] = "UserSoaked";
            $Action['UserSoaked'] = $Return[$post['Action']];

            SendAction($ws, $frame->fd, $Action);
        }

        //暱稱設定
        if (!empty($output['NickName'])) {

            $client['NickName'] = $output['NickName'];
            $connections->set($frame->fd, $client);
            $UniqueID_QRcodeSoaked =  $client['UniqueID_QRcodeSoaked'];
            $Action = [];
            $Action['soakedLogin'] = date("Y-m-d H:i:s");
            SendAction($ws, $client['client'], $Action);
            ReFreshConnections($ws, $UniqueID_QRcodeSoaked, $connections);
        }
        //匿名使用
        if (!empty($output['hash'])) {


            $post['Action'] = "QRcodeSoaked_connections";
            $post['Data'] = json_encode($output['hash']);

            $Return = Tools::httpPost($SwooleUrl, $post, true);

            var_dump($Return);
            $Action = [];
            $Action['Type'] = "QRcodeSoaked_connections";
            $Action['QRcodeSoaked_connections'] = $Return[$post['Action']];
            SendAction($ws, $frame->fd, $Action);
            var_dump("Action", $Action);
            foreach ($connections as $client) {

                

                    $clientTemp = $connections->get($frame->fd);
                    $clientTemp['UniqueID_QRcodeSoaked'] = $Action['QRcodeSoaked_connections']['UniqueID_QRcodeSoaked'];
                    $clientTemp['QRcodeSoaked_code'] = $Action['QRcodeSoaked_connections']['QRcodeSoaked_code'];
                    $connections->set($frame->fd, $clientTemp);

                    $UniqueID_QRcodeSoaked =  $Action['QRcodeSoaked_connections']['UniqueID_QRcodeSoaked'];

                    $Action = [];
                    $Action['UniqueID_QRcodeSoaked'] = $UniqueID_QRcodeSoaked;
                    SendAction($ws, $clientTemp['client'], $Action);

                    ReFreshConnections($ws, $client['UniqueID_QRcodeSoaked'], $connections);
                   
            }
           
        }
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($connections, $SwooleUrl) {
    echo "client-{$fd} is closed\n";
    $DisClient = $connections->get($fd);

    //如果 $DisClient 找無資料 表示 Server 可能快掛了！



    $Data = $DisClient;
    unset($Data['client']);

    $post['Action'] = "DisConnect";
    $post['Data'] = json_encode($Data);

    $Return = Tools::httpPost($SwooleUrl, $post);
    $UniqueID_QRcodeSoaked = false;
    foreach ($connections as $client) {

        if ((int) $client['client'] == $fd) {
            $UniqueID_QRcodeSoaked =  $client['UniqueID_QRcodeSoaked'];
            continue;
        }

        $Action = [];
        $Action['DisClient'] = $DisClient['mesaUniqueID'];
        $Action['Type'] = "Toast";
        $Action['Toast'][] = "本次關閉連線短碼:" . $Action['DisClient'];
        SendAction($ws, $client['client'], $Action);
    }

    //傳送線上名單

    $connections->del($fd);

    ReFreshConnections($ws, $UniqueID_QRcodeSoaked, $connections);
});

$ws->start();
