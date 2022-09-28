<?php
use Phalcon\Http\Response;

class AdminController extends BaseController
{
    public static $arrContextOptions = [];
    
    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST']);
        session_start();

        self::$arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
    }
    
    public function init(){

        Accounts::find()->delete();
        AccountsLocation::find()->delete();
        AccountsLoginLogs::find()->delete();
        ActionLogs::find()->delete();
        checkKeysRule::find()->delete();
        Encryptionkey::find()->delete();
        ServersLocation::find()->delete();
        UniqueIDsLog::find()->delete();

        _Action::checkKeys_init();
        _Action::RedirectAdmin_init();

        

       

        
        
        $Return = [];
        $Return['header'] = self::viewAction("ReDirect","Home_header",[]);
        $Return['aside'] = self::viewAction("ReDirect","Home_aside",[]);
        $Return['nav'] = self::viewAction("ReDirect","Home_nav",[]);
        $Return['Dashboard'] = self::viewAction("ReDirect","Home_Dashboard",[]);
        $Return['Input_Nav'] = self::viewAction("ReDirect","Input_Nav",[]);
   
        unset($_SESSION[Tools::getIp()]['ReDirect']); 
        session_destroy();
        echo self::viewAction("admin","sign-in",$Return);
       
        exit;
    }
    public function infoAction(){
        
        
        $headers = Tools::getRequestHeaders();
        
    }
    
    public function indexAction()
    {

        if( INIT_RESET == 1 ) $this->init();

        //預設模板讀取
        $Return = _Views::Init();
        
        if(!empty($_SESSION[Tools::getIp()]['ReDirect']))  
            $Return['ReDirect'] = $_SESSION[Tools::getIp()]['ReDirect'];
        else $Return['ReDirect'] = "sign-in";
       
        //預設 新增修改 模板讀取
        $Return['Input_Nav'] = _Views::RedirectAdmin(["ReDirect"=>"Input_Nav"]);
        
        //列表頁面
        $Item['ViewsPath'] = "admin";
        $RedirectAdmin = RedirectAdmin::getListByItem($Item);

        foreach($RedirectAdmin AS $item){
            $Return[$item['ReDirect']] = _Views::RedirectAdmin(["ReDirect"=>$item['ReDirect']]);
        }
        
        
        if(!empty($Return['ReDirect'])) 
            $Echo = _Views::RedirectAdmin($Return);
        if(!empty($_SESSION['History'])) {
            $Return['ReDirect'] = $_SESSION['History'];
            $History = _Views::RedirectAdmin($Return);
        }
            
        

        if(!empty($Echo)) echo $Echo;
        else if(!empty($History)) echo $History;
        else  {
            unset($_SESSION[Tools::getIp()]['ReDirect']);
            $Return['ReDirect'] = "sign-in";
            echo _Views::RedirectAdmin($Return);
        }
        
    }
    
    
}
