<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/8
 * Time: 14:38
 */

namespace Seller\Controller;
use Think\Controller;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['userInfo']) || empty($_SESSION['powerInfo'])) {
            $this->redirect('/');
            exit;
        }

        $this->assignNav();

        $userInfo = $_SESSION["userInfo"];
        $this->assign('__USER__', $userInfo);
    }

    //  替换左边的导航条
    private function assignNav()
    {
        $arrPowerInfo = $_SESSION["powerInfo"];

        if(empty($arrPowerInfo))
            return;

        $findAction = false;
        $navAction = false;
        $arrNavTree = array();
        foreach($arrPowerInfo as $idItem => $item){
            if($item["parentid"] == 0){
                if($item["displayflag"] == 1) {
                    $arrNavTree[$idItem] = $item;
                }
                continue;
            }

            if(strtolower(CONTROLLER_NAME) == strtolower($item["controller"])
                && strtolower(ACTION_NAME) == strtolower($item["action"])) {
                $findAction = true;
            }

            $idRoot = $item["idRoot"];

            if($item["displayflag"] == 1){
                $arrNavTree[$idRoot]["subItem"][$idItem] = $item;

                if(strtolower(CONTROLLER_NAME) == strtolower($item["controller"])
                    && strtolower(ACTION_NAME) == strtolower($item["action"])){
                    $arrNavTree[$idRoot]["active"] = true;
                    $arrNavTree[$idRoot]["subItem"][$idItem]["active"] = true;

                    $_SESSION['defaultController'] = $idRoot;
                    $_SESSION['defaultAction'] = $idItem;

                    $navAction = true;
                }
            }
        }

        if($navAction == false){
            if(isset($_SESSION['defaultController'])) {
                $lastController = $_SESSION['defaultController'];
                if(isset($arrNavTree[$lastController])){
                    $arrNavTree[$lastController]["active"] = true;
                }

                if(isset($_SESSION['defaultAction'])) {
                    $lastAction = $_SESSION['defaultAction'];
                    if(isset($arrNavTree[$lastController]["subItem"][$lastAction])){
                        $arrNavTree[$lastController]["subItem"][$lastAction]["active"] = true;
                    }
                }
            }
        }

        //  没有权限访问该action
        if($findAction == false){
            $this->redirect('/');
        }

        $this->assign('__NAV__', $arrNavTree);
    }
}
