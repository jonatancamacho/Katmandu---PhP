<?php

//fritt från denna site http://sourcemaking.com/design_patterns/factory_method/php/1

/**
 * Description of ControllerFactory
 *
 * @author pei
 */
include_once '../controllers/Controller.php';
include_once '../controllers/MobileController.php';
include_once '../controllers/XMLController.php';

abstract class  ControllerFactoryMethod {
    //put your code here
    static function getController($controllerType){
        //vilken instanstyp ska returneras
        switch ($controllerType){
            case 'XMLController':
                return new XMLController();
                break;
            case 'PDOController':
                return new Controller();
                break;
            case 'MobileController':
                return new MobileController();
                break;
            default:
                if( strstr($_SERVER['HTTP_USER_AGENT'],'Android') ||
                        strstr($_SERVER['HTTP_USER_AGENT'],'webOS') ||
                        strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||
                        strstr($_SERVER['HTTP_USER_AGENT'],'iPod')||
                        strstr($_SERVER['HTTP_USER_AGENT'],'Mobile')) 
                {
                    return new MobileController();
                } else {
                    return new Controller();
                }
        }
    }
}
