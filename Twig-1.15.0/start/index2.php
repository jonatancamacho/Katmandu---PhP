<?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        //inkluderar
        include_once '../controllers/Controller.php';
        include_once '../controllers/XMLController.php';
        //surfar till denna sida med länken index2.php?getAllCars
        //delar upp querystyrningen till en array med/som avskiljare
        //arrayens innehåll:
        //$queryArray[0]=getAllCars
        $queryArray = explode('/', $_SERVER['QUERY_STRING']);
        //instansierar ett nytt controller objekt 
        $cont = new Controller();
        //anropar metod på controllerobjekt
        //blir tex $conu->getAllCars();
        
        if (method_exists ($cont, $queryArray[1]))
        {
            $cont->$queryArray[1] ($queryArray[2]);
        }
?>