<?php
class Model  {
    //put your code here
    private $con;
    private $dsn, $username, $password;

    function __construct() {
        //vilken databasserver och databasnamn ska användas
        $this->dsn = 'mysql:host=utb-mysql.du.se;dbname=db30';
        $this->username = 'db30';
        $this->password = 'FJJAcyMU';
    }

    private function openConnection() {
        try {
            if ($this->con == NULL) {
                //kopplar upp mot databasen med hjälp av ett PDO objekt
                //dess konstruktor måste veta, datasourcename(dsn),
                //användarnamn och lösenord
                $this->con = new PDO($this->dsn, $this->username, $this->password);
            }
        } catch (PDOException $pdoexp) {
            //kastar ett nytt fel som Kontrollerna får ta hand om och tex visa
            //en felmeddeladesida
            $this->con = NULL;
            throw new Exception('Databasfel');
        }
    }
    public function getAllProdukter(){
        try {
            $this->openConnection();
            $pdoStatement = $this->con->prepare('SELECT * FROM h12jonat_produkter');
            //3.exekverar frågan med hjälp av pdoStament objektet och dess medod execute
            $pdoStatement->execute();
            //4.hämtar resultatet till en array med hjälp av pdoStatement objket
            //och dess metod fetchAll
            $produkter = $pdoStatement->fetchAll();
            //5. stänger uppkopplingen
            $this->con = NULL;
            //retunerar arrayen med resultsetet
            return $produkter;
        } catch (PDOException $pdoexp){
            //vid fel kastas ett nytt som kontroller får ta hand om
            $this->con = NULL;
            throw new Exception('Databasfel- gick inte att hämta alla produkter');
        }
    }
    
    public function getKategori($kategori) {
       try {
            $this->openConnection();
            $pdoStatement = $this->con->prepare("CALL h12jonatGetKategori('{$kategori}')");
            //3.exekverar frågan med hjälp av pdoStament objektet och dess medod execute
            $pdoStatement->execute();
            //4.hämtar resultatet till en array med hjälp av pdoStatement objket
            //och dess metod fetchAll
            $kategorier = $pdoStatement->fetchAll();
            //5. stänger uppkopplingen
            $this->con = NULL;
            //retunerar arrayen med resultsetet
            return $kategorier;
        } catch (PDOException $pdoexp){
            //vid fel kastas ett nytt som kontroller får ta hand om
            $this->con = NULL;
            throw new Exception('Databasfel- gick inte att hämta kategori');
        } 
    }
    public function getProdukt($produkt) {
       try {
            $this->openConnection();
            $pdoStatement = $this->con->prepare("CALL h12jonatGetProdukt('{$produkt}')");
            $pdoStatement->execute();
            $produkten = $pdoStatement->fetchAll();
            $this->con = NULL;
            return $produkten;
        } catch (PDOException $pdoexp){
            $this->con = NULL;
            throw new Exception('Databasfel- gick inte att hämta produkter');
        } 
    }
    public function getUnikaKategorier(){
        try {
            $this->openConnection();
            $statement = $this->con->prepare('SELECT DISTINCT kategori FROM h12jonat_produkter');
            $statement->execute();
            $kategorier = $statement->fetchAll();
            $this->con = null;
            return $kategorier;
        } catch (PDOException $pdoexp) {
            $this->con = null;
            throw new Exception('Databas fel det gick inte hämta några kategorier');
        }
    }

    public function addProdukt() {
        try {
            $this->openConnection();
            $statement = $this->con->prepare('INSERT INTO h12jonat_produkter '  
               . '(Produkt, Kategori, Pris, Beskrivning, Bild)
                VALUES(:produkten, :kategorin, :priset, :beskrivningen, :bilden)');
            $statement->bindParam(':produkten', filter_var($_POST['produkt'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':kategorin', filter_var($_POST['kategori'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':priset', filter_var($_POST['pris'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':beskrivningen', filter_var($_POST['beskrivning'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':bilden', filter_var($_POST['bild'], FILTER_SANITIZE_STRING));

            $statement->execute();
            $this->con = NULL;
        } catch (PDOException $pdoexp){
            $this->con = NULL;
            throw new Exception('Databasfel, gick inte att lägga till produkt');
        }
    }
    public function updateProdukt() {
        try {
            $this->openConnection();
            $statement = $this->con->prepare('UPDATE h12jonat_produkter SET 
                Kategori=:kategorin, Pris=:priset, Beskrivning=:beskrivningen, 
                Bild=:bilden WHERE Produkt=:produkten');
            $statement->bindParam(':produkten', filter_var($_POST['produkt'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':kategorin', filter_var($_POST['kategori'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':priset', filter_var($_POST['pris'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':beskrivningen', filter_var($_POST['beskrivning'], FILTER_SANITIZE_STRING));
            $statement->bindParam(':bilden', filter_var($_POST['bild'], FILTER_SANITIZE_STRING));

            $statement->execute();
            $this->con = null;
        } catch (PDOException $pdoexp){
            $this->con = null;
            throw new Exception('Databasfel, gick inte att uppdatera produkten');
        }
    }    
    public function deleteProdukt($produkt) {
        try {
            $this->openConnection();
            $statement = $this->con->prepare('DELETE FROM h12jonat_produkter WHERE Produkt=:produkten');
            $statement->bindParam(':produkten', $produkt);
            
            $statement->execute();
            $this->con = null;
        } catch (PDOException $pdoexp){
            $this->con = null;
            throw new Exception('Databasfel, gick inte att ta bort produkt');
        }
    }
    public function getXMLData() {
        
        $result = $this->getAllProdukter();
        $xml = simplexml_load_file('../data/katter.xml');
            $produkterElement = $xml;
        foreach ($result as $value) {
            
            $produktElement = $produkterElement->addChild(produkter);
            $produktElement->addChild('produkt', $value[Produkt]);
            $produktElement->addChild('kategori', $value[Kategori]);
            $produktElement->addChild('pris', $value[Pris]);
            $produktElement->addChild('beskrivning', $value[Beskrivning]);
            $produktElement->addChild('bild', $value[Bild]);
            
        }
        $produkterElement->asXML('../data/katter.xml');
    }
}

?>
