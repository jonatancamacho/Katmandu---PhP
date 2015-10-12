<?php

require_once '../lib/Twig/Autoloader.php';
require_once '../models/Model.php';
require_once '../controllers/ValidationClass.php';
session_start();

class MobileController {
    
    //private instansvariabler
    private $twig;
    private $modell;
    private $template;
    private $cart;

    function __construct() {
        try {
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem('../templates/mobile');
            $this->twig = new Twig_Environment($loader);
            $this->template = $this->twig->loadTemplate('mStartVy.twig'); 
            $this->modell = new Model();
            $this->getAllProdukt = $this->modell->getAllProdukter();
            $this->cart = array();
        } catch (Exception $e) {
            $this->template = $this->twig->loadTemplate('mErrorPage.twig');
            $this->template->display(array('felmeddelande' => $e->getMessage()));
        }
    }
    //put your code here
    public function getAllProdukter() {
        try {
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem('../templates/mobile');
            $twig = new Twig_Environment($loader);
            $template = $twig->loadTemplate('mStartVy.twig');
        
            $produkt = $this->modell->getAllProdukter();
            $template->display(array('produkt'=>$produkt));
        } catch (Exception $e) {
            $this->template = $this->twig->loadTemplate('mErrorPage.twig');
            $this->template->display(array('felmeddelande' => $e->getMessage()));
        }
    }
 
    public function getKategori($kategori) {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('../templates/mobile');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('mTillbehör.twig');
        $produkterna = $this->modell->getKategori($kategori);
        $unikaKategorier = $this->modell->getUnikaKategorier();
        $template->display(array('produkter'=>$produkterna, 
            'unikaKategorier' => $unikaKategorier));
    }
    public function getProdukt($produkt) {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('../templates/mobile');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('mProdukt.twig');
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
        $loader = new Twig_Loader_Filesystem('../templates/mobile');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('mKontakt.twig');
        
        $produkt = $this->modell->getAllProdukter();
        $template->display(array('produkt'=>$produkt));
    } 
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
                $this->template = $this->twig->loadTemplate('mKundvagn.twig');
                $this->template->display(array('produktArray' => $produktArray,
                    'attBetala' => $this->toPay()));
            } else {
                $this->template = $this->twig->loadTemplate('mKundvagn.twig');
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
public function addProdukt () {
        $validering = new ValidationClass();
        if (count($validering->validation()) == 0) {
            $this->modell->addProdukt();
            $this->template = $this->twig->loadTemplate('mAddForm.twig');
            $this->template->display(array());
        } else {
            $this->template = $this->twig->loadTemplate('mAddForm.twig');
            $this->template->display(array('errormessages' => $validering->validation(), 
                'postatdata' => $_POST));
        }
    }
    public function updateProdukt() {
        $validering = new ValidationClass();
        if (count($validering->validation()) == 0) {
            $this->modell->updateProdukt();
            $this->template = $this->twig->loadTemplate('mUpdateForm.twig');
            $this->template->display(array());
        } else {
            $this->template = $this->twig->loadTemplate('mUpdateForm.twig');
            $this->template->display(array('errormessages' => $validering->validation(), 
                'postatdata' => $_POST));
        }  
    }
    public function deleteProdukt () {

        try {
            $this->modell->deleteProdukt($_POST['produkt']);
            $this->template = $this->twig->loadTemplate('mDeleteForm.twig');
            $this->template->display(array());
        } catch (Exception $e) {
            $this->template = $this->twig->loadTemplate('mDeleteForm.twig');
            $this->template->display(array('errormessages' => $e->getMessage()));
        }
    }    
    public function showAddForm() {
        
        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('mAddForm.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }
    public function showAdmin() {
        
        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('mAdmin.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }   
    public function showDeleteForm() {

        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('mDeleteForm.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    } 
    public function showUpdateForm() {

        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('mUpdateForm.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }    
    public function login() {
        if (strip_tags($_POST['username']) == 'admin' && strip_tags($_POST ['password']) == 'admin') {
            $_SESSION['loggedin'] = TRUE;
            $this->showAdmin();
        } else {
            $_SESSION['loggedin'] = FALSE;
            $this->template = $this->twig->loadTemplate('mLoginForm.twig');
            $this->template->display(array());
        }
    }
    public function logout() {
        if ($_SESSION['loggedin']) { 
            session_destroy();        
        }
            $this->getAllProdukter();
    }    
}
     
    ?>