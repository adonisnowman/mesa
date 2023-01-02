<?php

use Swoole\Websocket\Server;
use Swoole\WebSocket\Frame;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;

if (file_exists("/etc/letsencrypt/live/adonis.tw-0002/fullchain.pem")) $fullchain = "/etc/letsencrypt/live/adonis.tw-0002/fullchain.pem";
if (file_exists("/etc/letsencrypt/live/adonis.tw-0002/privkey.pem")) $privkey = "/etc/letsencrypt/live/adonis.tw-0002/privkey.pem";

$port = 9501;
$server = new Swoole\Http\Server('0.0.0.0', 9501, SWOOLE_BASE, SWOOLE_SOCK_TCP);
$SwooleSetting = [];
$SwooleSetting['daemonize'] = false;
$SwooleSetting['ssl_cert_file'] = $fullchain;
$SwooleSetting['ssl_key_file'] = $privkey;
//配置参数
$server->set($SwooleSetting);

$server->addlistener('0.0.0.0', 4444, SWOOLE_SOCK_UDP);//UDP, 監聽所有ip地址

$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($data,$clientInfo);   
});



$server->on('request', function ($request, $response) {

    var_dump($request->header);
    var_dump($request->server);
    var_dump($request->get);
    var_dump($request->post);
    var_dump($request->cookie);
    // var_dump($request->rawcookie);
    var_dump($request->files);
    // var_dump($request->rawContent);
    // var_dump($request->getContent);
    // var_dump($request->getData);
    // var_dump($request->create);
    // var_dump($request->parse);
    // var_dump($request->isCompleted);
    // var_dump($request->getMethod);
    
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$server->start();