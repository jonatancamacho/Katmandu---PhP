<?php

require_once '../lib/Twig/Autoloader.php';
require_once '../models/Model.php';
require_once '../controllers/ValidationClass.php';
session_start();

class Controller {
    
    //private instansvariabler
    private $twig;
    private $modell;
    private $template;
    private $cart;

    function __construct() {
        try {
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem('../templates/');
            $this->twig = new Twig_Environment($loader);
            $this->template = $this->twig->loadTemplate('StartVy.twig'); 
            $this->modell = new Model();
            $this->cart = array();
        } catch (Exception $e) {
            $this->template = $this->twig->loadTemplate('ErrorPage.twig');
            $this->template->display(array('felmeddelande' => $e->getMessage()));
        }
    }
    public function getXMLData(){
        $this->template = $this->twig->loadTemplate('StartVy.twig');
        $this->modell->getXMLData();
        $this->template->display(array());
    }
    //Construktor
    
    public function getAllProdukter() {
        try {
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem('../templates/');
            $twig = new Twig_Environment($loader);
            $template = $twig->loadTemplate('StartVy.twig');
        
            $produkt = $this->modell->getAllProdukter();
            $template->display(array('produkt'=>$produkt));
        } catch (Exception $e) {
            $this->template = $this->twig->loadTemplate('ErrorPage.twig');
            $this->template->display(array('felmeddelande' => $e->getMessage()));
        }
    }
    public function getKategori($kategori) {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('../templates/');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('Tillbehör.twig');
        $produkterna = $this->modell->getKategori($kategori);
        $unikaKategorier = $this->modell->getUnikaKategorier();
        $template->display(array('produkter'=>$produkterna, 
            'unikaKategorier' => $unikaKategorier));
    }
    public function getProdukt($produkt) {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('../templates/');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('Produkt.twig');
        $produktArray = $this->modell->getProdukt($produkt);
        $unikaKategorier = $this->modell->getUnikaKategorier();
        if ($_SESSION['loggedin'] == TRUE) {
            $inloggad = 'inloggad';
        } else {
            $inloggad = 'inte';
        }  
        $template->display(array('produkter'=>$produktArray, 
            'inloggad' => $inloggad, 'unikaKategorier' => $unikaKategorier));
    }
      public function getKontakt() {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('../templates/');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('Kontakt.twig');
        
        $produkt = $this->modell->getAllProdukter();
        $template->display(array('produkt'=>$produkt));
    } 
    //Slut på get funktioner
    
    public function addToCart($produkt) {
        if ($_SESSION['cart']) {
            $this->cart = $_SESSION['cart'];
            if (!array_key_exists($produkt, $this->cart)){
                $produktArray = $this->modell->getProdukt($produkt);
                $this->cart[$produkt] = array($produktArray[0],1);
                $_SESSION['cart'] = $this->cart;
            } else {
                $this->cart[$produkt][1]++;
                $_SESSION['cart'] = $this->cart;
            }
        } else {
            $_SESSION['cart'] = $this->cart;
            $produktArray = $this->modell->getProdukt($produkt);
            $this->cart[$produkt] = array($produktArray[0],1);
            $_SESSION['cart'] = $this->cart;
        }
        $this->getProdukt($produkt);
    }
    public function showCart() {
            if (!empty($_SESSION['cart'])) {
                $produktArray = $_SESSION['cart'];
                $this->template = $this->twig->loadTemplate('Kundvagn.twig');
                $this->template->display(array('produktArray' => $produktArray,
                    'attBetala' => $this->toPay()));
            } else {
                $this->template = $this->twig->loadTemplate('Kundvagn.twig');
                $this->template->display(array('produktArray' => 'Tomt i kundvangen'));
            }
    }
    public function deleteFromCart($produkt) {
        if ($_SESSION['cart']){
            $this->cart = $_SESSION['cart'];
            if (array_key_exists($produkt, $this->cart)) {
                $this->cart[$produkt][1]--;
            }
            if($this->cart[$produkt][1]<=0) {
                unset($this->cart[$produkt]);
            }
            $_SESSION['cart'] = $this->cart;
            $this->showCart();
        }
    }
    private function toPay() {
        $toPay = 0;
        foreach ($_SESSION['cart'] as $produktens) {
            $toPay+=$produktens[0]['Pris']*$produktens[1];
        }
        return $toPay;
    }
    //Slut på kundvangshantering

    public function addProdukt () {
        if ($_SESSION['loggedin'] == true) {
            $validering = new ValidationClass();
            if (count($validering->validation()) == 0) {
                $this->modell->addProdukt();
                $this->template = $this->twig->loadTemplate('AddForm.twig');
                $this->template->display(array());
            } else {
                $this->template = $this->twig->loadTemplate('ErrorPage.twig');
                $this->template->display(array('errormessages' => $validering->validation(), 
                    'postatdata' => $_POST));
            }
        } else {
            $this->login();
        }    
    }
    public function updateProdukt() {
        if ($_SESSION['loggedin'] == true) {
            $validering = new ValidationClass();
            if (count($validering->validation()) == 0) {
                $this->modell->updateProdukt();
                $this->template = $this->twig->loadTemplate('UpdateForm.twig');
                $this->template->display(array());
            } else {
                $this->template = $this->twig->loadTemplate('ErrorPage.twig');
                $this->template->display(array('errormessages' => $validering->validation(), 
                    'postatdata' => $_POST));
            }
        } else {
            $this->login();
        }
    }
    public function deleteProdukt ($produkt) {
        if ($_SESSION['loggedin'] == true) {
            try {
            $this->modell->deleteProdukt($produkt);
            $this->showAdminUpdate();
            } catch (Exception $e) {
            $this->template = $this->twig->loadTemplate('ErrorPage.twig');
            $this->template->display(array('errormessages' => $e->getMessage()));
            }
        } else {
            $this->login();
        }
    }
    //ta bort, uppdatera och läggtill produkter
    
    
    public function showAddForm() {
        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('AddForm.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }
    public function showAdmin() {
        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('Admin.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }   
    public function showUpdateForm($produkt) {
        if ($_SESSION['loggedin'] == TRUE) {
            $produktArray = $this->modell->getProdukt($produkt);
            $this->template = $this->twig->loadTemplate('UpdateForm.twig');
            $this->template->display(array('produktArray' => $produktArray));
        } else {
            $this->login();
        }
    }
    public function showAdminUpdate() {
        if ($_SESSION['loggedin'] == true) {
            try {
                $produktArray = $this->modell->getAllProdukter();
                $this->template = $this->twig->loadTemplate('AdminUpdate.twig');
                $this->template->display(array('produktArray' => $produktArray));
            } catch (Exception $e){
                $this->template = $this->twig->loadTemplate('ErrorPage.twig');
            $this->template->display(array('errormessages' => $e->getMessage()));
            }
        } else {
            $this->login();
        }
    }
    //slut på att visa olika forms
    
    public function login() {
        if (strip_tags($_POST['username']) == 'admin' && strip_tags($_POST ['password']) == 'admin') {
            $_SESSION['loggedin'] = TRUE;
            $this->showAdmin();
        } else {
            $_SESSION['loggedin'] = FALSE;
            $this->template = $this->twig->loadTemplate('LoginForm.twig');
            $this->template->display(array());
        }
    }
    public function logout() {
        if ($_SESSION['loggedin']) { 
            session_destroy();        
        }
            $this->getAllProdukter();
    }
    //logga in och logga ut funktionalitet
    
}
?>
