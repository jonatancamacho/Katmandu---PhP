<?php

class ValidationClass {
    public function validation() {
        $errormsg = array();
        foreach ($_POST as $key => $value) {
            $_POST[$key] = strip_tags(trim($value));
            
            if ($value == '') {
                $errormsg[$key] = 'Fältet kan inte vara tomt';
            } else {
                switch ($key) {
                    case 'pris':
                        if ($this->validationPris($value) != null) {
                            $errormsg[$key] = $this->validationPris($value);
                        }
                        break;
                    case 'bild':
                        if ($this->validationBild($value) != null) {
                            $errormsg[$key] = $this->validationBild($value);
                        }
                        break;
                    case 'produkt':
                        break;
                    case 'kategori':
                        break;
                    case 'beskrivning':
                        break;
                    default:
                }
            }
        }
        return $errormsg;
    }
    private function validationBild($bild) {
        $error = "";
        $fileEnding = substr($bild, -4);
        if (strcmp($fileEnding, '.jpg') == 0 || strcmp($fileEnding, '.png') == 0 || strcmp($fileEnding, '.gif') == 0) {
            
        } else {
            $error = 'Bilden måste vara av formatet .jpg .png eller .gif';
        }
        return $error;
    }
    private function validationPris($pris) {
        $error = "";
        if (!is_numeric($pris)) {
            $error = 'Det får bara vara siffror i priset';
        }
        return $error;
    } 
}

?>
