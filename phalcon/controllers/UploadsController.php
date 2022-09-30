<?php

use Phalcon\Http\Response;

class UploadsController extends BaseController
{

    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', 'angularjs.adonis.tw');
        session_start();
    }
    // public function indexAction()
    // {
    //     extract($_POST, EXTR_OVERWRITE, '');

    // 	// $Token = Tools::Crypt($_SERVER['REMOTE_ADDR']);

    //     // Tools::Crypt($Token,true);
    //     if(empty($_SESSION)) $this->signInAction();
    //     else echo $this->viewAction("admin","index",[]);
    // }
    public function webmAction()
    {
	if (empty($_SESSION[Tools::getIp()]['Users'])) exit;
        else var_dump($_SESSION[Tools::getIp()]['Users']);
        $id =  _UniqueID::shortUniqueID();
        $Mp4File = "video/{$id}.mp4";
       
            echo $exec = " ffmpeg -i {$_FILES['file']['tmp_name']} -y $Mp4File 2>&1 ";
            if (exec($exec, $out)) {
                
                if (file_exists($Mp4File) == false) exit;
                exec(" sudo chmod 777 {$Mp4File} ", $out);
                var_dump($out);
                exec(" ffmpeg -i {$Mp4File} -pix_fmt rgb24 -r 4 video/{$id}.gif 2>&1", $out);
                var_dump($out);
                exec(" ffmpeg -re -i $Mp4File -c copy -f flv  -flvflags no_duration_filesize rtmp://adonis.tw/live/{$id} 2>&1", $out);
                var_dump($out);
            }ELSE var_dump($out);
        
    }

    
}
