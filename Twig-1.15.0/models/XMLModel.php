<?php
class XMLModel {

    private $xml;

    function __construct() {
        $this->xml = simplexml_load_file('../data/katter.xml');
    }

    public function getAlLProdukter() {
        // return $this->xml;
        return simplexml_load_file('../data/katter.xml');
    }

    public function getProdukt($produkt) {
        return $this->xml->xpath("//produkter/produkt[text()='{$produkt}']/..");
    }

    public function getKategori($kategori) {
       return $this->xml->xpath("//produkter/kategori[text()='{$kategori}']/..");
        //var_dump($this->xml->xpath("//produkter/kategori[text()='{$kategori}']/.."));
    }
    
    public function getUnikaKategorier() {
        return $this->xml->xpath("/katter/produkter[not(kategori = following::kategori)]");
    }
    public function getProduktCart($produkt) {
        $produkt = $this->xml->xpath("//produkter/produkt[text()='{$produkt}']/..");
        
        return json_decode(json_encode((array) $produkt), TRUE);
    }

// end function

    public function updateProdukt() {
        //för tillbaka en array
        $produkter = $this->getProdukt(trim($_POST ['produkt']));
        $produkter [0]->produkt = $_POST ['produkt'];
        $produkter [0]->kategori = $_POST ['kategori'];
        $produkter [0]->pris = $_POST ['pris'];
        $produkter [0]->beskrivning = $_POST ['beskrivning'];
        $produkter [0]->bild = $_POST ['bild'];
        //spara till xml filen kom ihåg att sätt skriv rättigheter
        $this->xml->asXML('../data/katter.xml');
    }

// end function

    public function addProdukt() {
        $produkterElement = $this->xml;
        //lägger till ett nytt <bil> element udner <bilar>
        $produktElement = $produkterElement->addChild('produkter');
        //lägger till ett nytt <regnr> element under <bil>
        $produktElement->addChild('produkt', $_POST['produkt']);
        //lägger till ett nytt <marke> element under <bil>
        $produktElement->addChild('kategori', $_POST['kategori']);
        $produktElement->addChild('pris', $_POST['pris']);
        $produktElement->addChild('beskrivning', $_POST['beskrivning']);
        $produktElement->addChild('bild', $_POST['bild']);
        //spara till xml filen
        $produkterElement->asXML('../data/katter.xml');
    }

    public function deleteProdukt($produkt) {
        //söker reda på bilen. 
        for ($index = 0; $index < count($this->xml); $index++) {
            //om matchning på regnummer
            if ($this->xml->produkter[$index]->produkt == $produkt) {
                //ta bort bilelement på funnen index position
                unset($this->xml->produkter[$index]);
                break;
            }
        }
        //spar ner till xml fil igen.
        $this->xml->asXML('../data/katter.xml');
    }

}

// end class
?>