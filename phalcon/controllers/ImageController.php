<?php

use Phalcon\Http\Response;

class ImageController extends BaseController
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
    function getCode($num,$w,$h,$code) {

        /**
        
        * 去掉了数字0和1 字母小写O和L 
        
        * 避免用户输入时模糊混淆
        
        */        
        $str = "0123456789";
        
       
        
        if(empty($code))
            for ($i = 0; $i < $num; $i++) $code .= $str[mt_rand(0, strlen($str)-1)];
        
       
        $im = imagecreate($w, $h);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);
        

        imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
        imagerectangle($im, 0, 0, $w-1, $h-1, $gray);

        /*在画布上随机生成大量点*/
        for ($i = 0; $i < 80; $i++) imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        for ($i = 0; $i < 80; $i++)  imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        
       
        
        $strx = rand(6, 12);
        
        for ($i = 0; $i < $num; $i++) {
        
            $strpos = rand(3, 6);            
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);            
            $strx += rand(10, 30);        
        }
        
        /*输出图片*/
        
        return imagepng($im);
        
        
    }
    public function uploadAction()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,1);
        
        
        $fileName = "TempUpload/"._UniqueID::shortUniqueID();
        Tools::base64_to_jpeg( $postdata['src'], $fileName );

        if( filesize($fileName) == $postdata['fileSize'] ) {
            $CheckedfileName = $fileName.".".Tools::mime_content_type($fileName , true);
            file_put_contents( $CheckedfileName ,file_get_contents( $fileName ) );

            $Return['src'] = $CheckedfileName;

        }
        else {
            $Return['src'] = "";
        }        

        echo json_encode($Return);
           
    }

    public function QRcodeAction(){
        $data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';

        // quick and simple:
        echo '<img src="'.(new QRcode($data)).'" alt="QR Code" />';
    }
    public function codeAction($code)
    {
       
        $code = Tools::Crypt($code,true);
        extract($_POST, EXTR_OVERWRITE, '');

        header("Content-type: image/PNG");

        /*调用生成验证码函数*/

        ;
        $img_file = $this->getCode(4,100,30,$code);

        // Read image path, convert to base64 encoding
        $imgData = base64_encode($img_file);
        
        // Format the image SRC:  data:{mime};base64,{data};
        echo $src = 'data: image/png;base64,'.$imgData;
        
        
        exit;
       
		
        
        
    }
    
    
}
