<?php


class _Views
{

    public static $app_id;
    public static $arrContextOptions = [];
    function __construct()
    {
        self::$arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
    }

    public static function Init(){
        $Return = [];
        $Return['Dashboard'] = _Views::RedirectAdmin(["ReDirect"=>"Home_Dashboard"]);

        $Return['ActionLogs'] = _Views::RedirectAdmin(["ReDirect"=>"ActionLogs"]); 
        $Return['header'] = _Views::RedirectAdmin(["ReDirect"=>"Home_header"]);
        $Return['aside'] = _Views::RedirectAdmin(["ReDirect"=>"Home_aside"]);
        $Return['nav'] = _Views::RedirectAdmin(["ReDirect"=>"Home_nav"]);

        return $Return;
    }
    
    public static function RedirectAdmin($Return){
        
        $Echo = NULL;
        
        if(!empty($Return['ReDirect']) && is_string(($Return['ReDirect'])) ) {
            
            $Item = [];
            $Item['ReDirect'] = $Return['ReDirect'];
            $RedirectAdmin = RedirectAdmin::getObjectByItem($Item);
           
        }  
        
        if(!empty($Return['ReDirect']) && !empty($Return['ReDirect']['ReDirect'])){
            
            $Item = Tools::fix_element_Key($Return['ReDirect'],["ViewsType","ReDirect"]);
            
            $RedirectAdmin = RedirectAdmin::getObjectByItem($Item);
           
        }
        

        if(!empty($RedirectAdmin->UniqueID) && $RedirectAdmin->offshelf != 1)
        $Echo = BaseController::viewAction($RedirectAdmin->ViewsPath,$RedirectAdmin->ReDirect,$Return);
        
        return $Echo;
    }
}
