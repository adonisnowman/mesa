<?php
use Swoole\Websocket\Server;
use Swoole\WebSocket\Frame;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;

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
$connections->create();
    //创建websocket服务器对象，监听0.0.0.0:9501端口，开启SSL隧道
    $ws = new swoole_websocket_server("0.0.0.0", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    
    $fullchain = "/etc/letsencrypt/live/swoole.bestaup.com/fullchain.pem";
    $privkey = "/etc/letsencrypt/live/swoole.bestaup.com/privkey.pem";

    if(file_exists("/etc/letsencrypt/live/adonis.tw-0002/fullchain.pem")) $fullchain = "/etc/letsencrypt/live/adonis.tw-0002/fullchain.pem";
    if(file_exists("/etc/letsencrypt/live/adonis.tw-0002/privkey.pem")) $privkey = "/etc/letsencrypt/live/adonis.tw-0002/privkey.pem";

    //配置参数
    $ws ->set([
  'daemonize' => false, //守护进程化。
  //配置SSL证书和密钥路径
  'ssl_cert_file' => $fullchain,
  'ssl_key_file'  => $privkey
    ]);
 
    //监听WebSocket连接打开事件
    $ws->on('open', function ($ws, $request) use ($messages, $connections)  {
        echo "client-{$request->fd} is open\n";
        $connections->set($request->fd, ['client' => $request->fd]);

        // update all the client with the existing messages
        foreach ($messages as $row) {
            $server->push($request->fd, json_encode($row));
        }
        $output['client'] = $request->fd;
        $output['message'] = "onLine Now!";
        foreach ($connections as $client) {  
            if( $output['client'] != (int) $client['client'])      
            $server->push($client['client'], json_encode($output));        
        }
    });
 
    //监听WebSocket消息事件
    $ws->on('message', function ($ws, $frame) use ($messages, $connections)  {
        echo "Message: {$frame->data}\n";
        // frame data comes in as a string
    $output = json_decode($frame->data, true);

    // assign a "unique" id for this message
    $output['id'] = time();
    $output['client'] = $frame->fd;
    $output['messages'] = "got it!";
    

    // now we can store the message in the Table
    $messages->set("adonis" . time(), $output);

    $data = JSON_decode($frame->data,1);
    $data['id'] = time();

    // now we notify any of the connected clients
    if(!empty($data['client'])) {
        $ws->push((int)$output['client'], json_encode($data));
        $ws->push((int)$data['client'], json_encode($data));
    }
    else
    foreach ($connections as $client) {        
        $ws->push($client['client'], json_encode($data));        
    }
});
 
    //监听WebSocket连接关闭事件
    $ws->on('close', function ($ws, $fd)use ($connections) {
        echo "client-{$fd} is closed\n";
        $connections->del($fd);
    });
 
    $ws->start();