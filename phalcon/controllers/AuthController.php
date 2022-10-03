<?php

use Phalcon\Http\Response;

class AuthController extends BaseController
{

    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', 'angularjs.adonis.tw');
        session_start();
    }
    
    
    public function indexAction()
    {
       
    
        extract($_POST, EXTR_OVERWRITE, '');
	
    }
    
    public function SignTokenAction($UniqueID="",$Token="")
    {
        
        Tools::checkToken($Token);

        $Item['UniqueID'] = $UniqueID;
        $User = Users::getObjectById($Item);
       
        if(!empty($User)) {
            $User->SignToken_time = Tools::getDateTime();
            $User->save();

            $Return = _Views::Init();
            $Return['ReDirect'] = "signMobile";
            $Return['UniqueID'] = $UniqueID;
            
            echo _Views::RedirectAdmin($Return);
        }
    }

    
}
