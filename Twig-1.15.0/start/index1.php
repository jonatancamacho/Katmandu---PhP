<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);
include_once('../controllers/ControllerFactoryMethod.php');

//läser av url:en efter (?)tecken i index1.php?PDOController/getCarByRegnr/ABC123
//splittar den till en array så att jag kan kommat åt Controllern,metod och argument
//$queryArray[0]=PDOController
//$queryArray[1]=getCarByRegnr
//$queryArray[2]=ABC123
$queryArray = explode('/', $_SERVER['QUERY_STRING']);
//skickar in Controller typen till fabriken som returnerar rätt controller instans antingen
//en PDOController,en XMLController  eller En SPLFileController
//$someController=ControllerFactoryMethod::getController($queryArray[0]);
$someController=ControllerFactoryMethod::getController($queryArray[0]);

//kollar så att metod finns i controllerobjektet, 
//minskar risk för att någon försöker ändra i url genom att 
//skicka in konstiga controller  och metodnamn
if (method_exists($someController, $queryArray[1])) {
   
     $someController->$queryArray[1]($queryArray[2]);
} else {
    // vi har bara surfat in till index1.php eg ingen querystring   
     $someController->getAllProdukter();
}
?>
