<?php

abstract class importAbstractTask extends sfBaseTask {

    protected static $months_fr = array(
        "août" => "08",
        "avr" => "04",
        "déc" => "12",
        "févr" => "02",
        "janv" => "01",
        "juil" => "07",
        "juin" => "06",
        "mai" => "05",
        "mars" => "03",
        "nov" => "11",
        "oct" => "10",
        "sept" => "09",
    );
    protected $produits_hash = null;

    protected function convertToFloat($number) {

        return round(str_replace(",", ".", $number) * 1, 2);
    }

    protected function convertToDateObject($date, $wird_date = false) {
        if (preg_match('/^([0-9]{2})-([a-zûé]+)-([0-9]{2})$/', $date, $matches)) {

            return new DateTime(sprintf('%d-%d-%d', $matches[3], self::$months_fr[$matches[2]], $matches[1]));
        }

        if (preg_match('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $date, $matches)) {

            return new DateTime(sprintf('%d-%d-%d', $matches[3], $matches[2], $matches[1]));
        }
        if ($wird_date) {
            if (preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{2})/', $date, $matches)) {
                if ($matches[3] > "16") {
                    $y = "19" . $matches[3];
                } else {
                    $y = "20" . $matches[3];
                }
                return new DateTime(sprintf('%d-%d-%d', $y, $matches[1], $matches[2]));
            }
        }
        throw new sfException(sprintf("La date '%s' est invalide", $date));
    }

    protected function convertOuiNon($indicateur) {

        return (int) ($indicateur == 'O');
    }

    protected function getKey($key, $withDefault = false) {
        if ($withDefault) {

            return ($key) ? $key : Configuration::DEFAULT_KEY;
        }
        if (!$key) {

            throw new sfException('La clé "' . $key . '" n\'est pas valide');
        }

        return $key;
    }

    protected function getConfigurationHash($code) {

        return ConfigurationClient::getCurrent()->get($this->getHash($code));
    }

    protected function getHash($code) {
        $produits_hash = $this->getProduitsHash();
      

        if (!array_key_exists($code * 1, $produits_hash)) {

            throw new sfException(sprintf("Le produit avec le code %s n'existe pas", $code));
        }

        return $produits_hash[$code * 1];
    }

    protected function getProduitsHash() {
        if (is_null($this->produits_hash)) {
            $this->produits_hash = array();
            foreach (ConfigurationClient::getCurrent()->getProduits() as $produit) {
               
                $this->produits_hash[$produit->getCodeProduit()] = $produit->getHash();
            }
        }
        return $this->produits_hash;
    }

    protected function verifyVolume($value, $can_be_negatif = false) {
        $this->verifyFloat($value, $can_be_negatif);
    }

    protected function verifyFloat($value, $can_be_negatif = false) {
        if ($can_be_negatif && !(preg_match('/^[\-]{0,1}[0-9]+\.[0-9]+$/', $value))) {
            throw new sfException(sprintf("Nombre flottant '%s' invalide", $value));
        } elseif (!$can_be_negatif && !(preg_match('/^[0-9]+\.[0-9]+$/', $value))) {
            throw new sfException(sprintf("Nombre flottant '%s' invalide", $value));
        }

        $value = $this->convertToFloat($value);

        if (!$can_be_negatif && $value < 0) {
            throw new sfException(sprintf("Nombre flottant '%s' négatif", $value));
        }
    }

    public function logLignes($type, $message, $lines, $num_ligne = null) {
        $this->log(sprintf("%s;%s (de la ligne %s à %s) :", $type, $message, $num_ligne - count($lines), $num_ligne));
        foreach ($lines as $i => $line) {
            $this->log(sprintf(" - %s : %s", $i, implode($line, ";")));
        }
    }

    public function logLigne($type, $message, $line, $num_ligne = null) {
        $this->log(sprintf("%s;%s (ligne %s) : %s", $type, $message, $num_ligne, implode($line, ";")));
    }

}
