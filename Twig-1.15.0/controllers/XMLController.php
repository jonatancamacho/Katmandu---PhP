<?php

include_once '../lib/Twig/Autoloader.php';
include_once '../models/XMLModel.php';
require_once '../controllers/ValidationClass.php';
session_start();

class XMLController {

    private $xmlModell;
    private $loader;
    private $twig;
    private $cart;
    private $template;

    function __construct() {
        Twig_Autoloader::register();
        $this->loader = new Twig_Loader_Filesystem('../templates/');
        $this->twig = new Twig_Environment($this->loader);
        $this->xmlModell = new XMLModel();
        $this->cart = array();
    }

    public function getAllProdukter() {
        if ($_SESSION['loggedin'] == TRUE) {
            $produkter = $this->xmlModell->getAllProdukter();
            $template = $this->twig->loadTemplate('XMLStartVy.twig');
            $template->display(array('produkter' => $produkter));
        } else {
            $this->login();
        }
        
    }

    public function getProdukt($produkt) {
        $template = $this->twig->loadTemplate('XMLProdukt.twig');
        $produktArray = $this->xmlModell->getProdukt($produkt);
        $unikaKategorier = $this->xmlModell->getUnikaKategorier(); 
        $template->display(array('produkter'=>$produktArray,
            'unikaKategorier' => $unikaKategorier));;
    }

    public function getKategori($kategori) {
        $template = $this->twig->loadTemplate('XMLTillbehör.twig');
        $unikaKategorier = $this->xmlModell->getUnikaKategorier();
        $produkterna = $this->xmlModell->getKategori($kategori);
        $template->display(array('produkter'=>$produkterna, 
            'unikaKategorier' => $unikaKategorier));
    }
    public function getKontakt() {
        $produkt = $this->xmlModell->getAllProdukter();
        $template = $this->twig->loadTemplate('XMLKontakt.twig');
        $template->display(array('produkt'=>$produkt));
    } 
    //Slut på get funktioner
    public function addToCart($produkt) {
        
        if ($_SESSION['cart']) {
            
            $this->cart = $_SESSION['cart'];
            if (!array_key_exists($produkt, $this->cart)){
                $produktArray = $this->xmlModell->getProduktCart($produkt);
                $this->cart[$produkt] = array($produktArray[0],1);
                $_SESSION['cart'] = $this->cart;
            } else {
                $this->cart[$produkt][1]++;
                $_SESSION['cart'] = $this->cart;
            }
        } else {
            $_SESSION['cart'] = $this->cart;
            $produktArray = $this->xmlModell->getProduktCart($produkt);
            $this->cart[$produkt] = array($produktArray[0],1);
            $_SESSION['cart'] = $this->cart;
        }
        $this->getProdukt($produkt);
    }
    public function showCart() {
            if (!empty($_SESSION['cart'])) {
                $produktArray = $_SESSION['cart'];
                $this->template = $this->twig->loadTemplate('XMLKundvagn.twig');
                $this->template->display(array('produktArray' => $produktArray,
                    'attBetala' => $this->toPay()));
            } else {
                $this->template = $this->twig->loadTemplate('XMLKundvagn.twig');
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
    
    public function addProdukt() {
        if ($_SESSION['loggedin'] == true) {
            $validering = new ValidationClass();
            if (count($validering->validation()) == 0) {
                $this->xmlModell->addProdukt();
                $this->template = $this->twig->loadTemplate('XMLAddForm.twig');
                $this->template->display(array());
            } else {
                $this->template = $this->twig->loadTemplate('XMLAddForm.twig');
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
                $this->xmlModell->updateProdukt();
                $this->template = $this->twig->loadTemplate('XMLUpdateForm.twig');
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
            $this->xmlModell->deleteProdukt($produkt);
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
            $this->template = $this->twig->loadTemplate('XMLAddForm.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }
    public function showAdmin() {
        if ($_SESSION['loggedin'] == TRUE) {
            $this->template = $this->twig->loadTemplate('XMLAdmin.twig');
            $this->template->display(array());
        } else {
            $this->login();
        }
    }
    public function showUpdateForm($produkt) {
        if ($_SESSION['loggedin'] == TRUE) {
            $produktArray = $this->xmlModell->getProdukt($produkt);
            $this->template = $this->twig->loadTemplate('XMLUpdateForm.twig');
            $this->template->display(array('produktArray' => $produktArray));
        } else {
            $this->login();
        }
    }
    public function showAdminUpdate() {
        if ($_SESSION['loggedin'] == true) {
            try {
                $produktArray = $this->xmlModell->getAllProdukter();
                $this->template = $this->twig->loadTemplate('XMLAdminUpdate.twig');
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
            $this->template = $this->twig->loadTemplate('XMLLoginForm.twig');
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