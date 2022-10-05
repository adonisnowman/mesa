<?php


class UploadsController extends BaseController
{

    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', 'angularjs.adonis.tw');
        session_start();
    }
    public function imagesAction()
    {
        if (empty($_SESSION[Tools::getIp()]['Users'])) exit;
        else $ImagesDir = "ImagesDir/".$_SESSION[Tools::getIp()]['Users']['UniqueID'];
        if(file_exists($ImagesDir) == false){
            mkdir($ImagesDir);
            chmod($ImagesDir, 0777);
        }
        $id =  _UniqueID::shortUniqueID();
        $fileName = $ImagesDir."/{$id}.jpg";
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,1);
        
       
       
        Tools::base64_to_jpeg( $postdata['src'], $fileName );
        $Return['src'] = $fileName;

        echo json_encode($Return);
    }
    public function webmAction()
    {
        if (empty($_SESSION[Tools::getIp()]['Users'])) exit;
        else $VideoDir = "video/".$_SESSION[Tools::getIp()]['Users']['UniqueID'];
        if(file_exists($VideoDir) == false){
            mkdir($VideoDir);
            chmod($VideoDir, 0777);
        }
        $id =  _UniqueID::shortUniqueID();
        $Mp4File = $VideoDir."/{$id}.mp4";
        $GifFile = $VideoDir."/{$id}.gif";
       
            echo $exec = " ffmpeg -i {$_FILES['file']['tmp_name']} -y $Mp4File 2>&1 ";
            if (exec($exec, $out)) {
                
                if (file_exists($Mp4File) == false) exit;
                exec(" sudo chmod 777 {$Mp4File} ", $out);
                var_dump($out);
                exec(" ffmpeg -i {$Mp4File} -pix_fmt rgb24 -r 4 {$GifFile} 2>&1", $out);
                var_dump($out);
                exec(" ffmpeg -re -i $Mp4File -c copy -f flv  -flvflags no_duration_filesize rtmp://adonis.tw/live/{$id} 2>&1", $out);
                var_dump($out);
            }ELSE var_dump($out);
        
    }

    
}

