<?php

/**
 * Model for DRMDeclaration
 *
 */
class DRMDeclaration extends BaseDRMDeclaration {

    public function getChildrenNode() {

        return $this->certifications;
    }

    public function getMouvements($isTeledeclaration = false) {
        $produits = $this->getProduitsDetails();
        $mouvements = array();
        foreach ($produits as $produit) {
            $mouvements = array_replace_recursive($mouvements, $produit->getMouvements());
        }

        return $mouvements;
    }

    public function cleanDetails() {
        $delete = false;
        foreach ($this->getProduitsDetails() as $detail) {
            if ($detail->isSupprimable()) {
                $detail->delete();
                $delete = true;
            }
        }

        if ($delete) {
            $this->cleanNoeuds();
        }
    }

    public function cleanNoeuds() {
        $this->_cleanNoeuds();
    }

    public function hasProduitDetailsWithStockNegatif() {
        foreach ($this->getProduitsDetails() as $prod) {
            if ($prod->hasProduitDetailsWithStockNegatif()) {
                return true;
            }
        }
        return false;
    }

    public function getProduitsDetailsSorted($teledeclarationMode = false, $detailsKey = null) {
        $produits = array();

        foreach ($this->certifications as $certification) {

            $produits = array_merge($produits, $certification->getProduitsDetailsSorted($teledeclarationMode, $detailsKey));
        }

        return $produits;
    }

    public function getProduitsDetailsByCertifications($isTeledeclarationMode = false, $detailsKey = null) {
        foreach ($this->getConfig()->getCertifications() as $certification) {
            if (!isset($produitsDetailsByCertifications[$certification->getHashWithoutInterpro()])) {
                $produitsDetailsByCertifications[$certification->getHashWithoutInterpro()] = new stdClass();
                $produitsDetailsByCertifications[$certification->getHashWithoutInterpro()]->certification_libelle = $certification->getLibelle();
                $produitsDetailsByCertifications[$certification->getHashWithoutInterpro()]->produits = array();
                $produitsDetailsByCertifications[$certification->getHashWithoutInterpro()]->certification_keys = $certification->getKey();
            } else {
                $produitsDetailsByCertifications[$certification->getHashWithoutInterpro()]->certification_keys .= ','.$certification->getKey();
            }
           if ($this->getDocument()->exist($certification->getHash())) {
                $produitsDetailsByCertifications[$certification->getHashWithoutInterpro()]->produits = array_merge($produitsDetailsByCertifications[$certification->getHashWithoutInterpro()]->produits, $this->getDocument()->get($certification->getHash())->getProduitsDetailsSorted($isTeledeclarationMode, $detailsKey));
            }
        }

        return $produitsDetailsByCertifications;
    }

    public function getMouvementsAggregateByAppellation($mouvementsFilter, $produitsFilter) {
        $recap = array();
        $produits = array();
        $nodes = array('stocks_debut', 'entrees', 'sorties', 'stocks_fin');
        foreach ($this->certifications as $certification) {
            foreach ($certification->genres as $genre) {
                foreach ($genre->appellations as $appellation) {
                    if(!preg_match("|".$produitsFilter."|", $appellation->getHash())) {
                        continue;
                    }
                    $produits[$appellation->getHash()] = $appellation->getLibelle();
                }
            }
        }
        foreach($this->getProduitsDetailsSorted(true, null) as $detail) {
            foreach($nodes as $node) {
                foreach($detail->{$node} as $key => $item) {
                    if(!preg_match("/".$mouvementsFilter."/", $key)) {
                        continue;
                    }
                    if(!preg_match("|".$produitsFilter."|", $detail->getHash())) {
                        continue;
                    }

                    foreach($item as $value) {
                        if(!$value->volume) {
                            continue;
                        }

                        if(!isset($recap[$value->identifiant])) {
                            foreach($produits as $produitLibelle) {
                                $recap[$value->identifiant][$produitLibelle] = 0;
                            }
                        }
                        $recap[$value->identifiant][$detail->getAppellation()->getConfig()->getLibelle()] += $value->volume;
                    }
                }
            }
        }

        return $recap;
    }

    public function getProduitsDetailsAggregateByAppellation($isTeledeclarationMode = false, $detailsKey = null, $exclude = null) {
        $recap = array();
        foreach ($this->certifications as $certification) {
            $recap[$certification->getHash()] = new stdClass();
            $recap[$certification->getHash()]->certification_libelle = $certification->getConfig()->getLibelle();
            $recap[$certification->getHash()]->produits = array();
            foreach ($certification->genres as $genre) {
                foreach ($genre->appellations as $appellation) {
                    if($exclude && preg_match("|$exclude|", $appellation->getHash())) {
                        continue;
                    }
                    $produit = new stdClass();
                    $produit->libelle = $appellation->getConfig()->getLibelle();
                    foreach($appellation->getProduitsDetailsSorted($isTeledeclarationMode, $detailsKey) as $detail) {
                        foreach($detail as $key => $value) {
                            if(!preg_match('/^total/', $key)) {
                                continue;
                            }

                            if(!isset($produit->{$key})) {
                                $produit->{$key} = 0;
                            }

                            $produit->{$key} += $value;
                        }
                        $nodes = array('stocks_debut', 'entrees', 'sorties', 'stocks_fin');
                        foreach($nodes as $node) {
                            foreach ($detail->{$node} as $key => $value) {
                                if(is_object($value)) {
                                    continue;
                                }

                                if(!isset($produit->{$node})) {
                                    $produit->{$node} = new stdClass();
                                }

                                if(!isset($produit->{$node}->{$key})) {
                                    $produit->{$node}->{$key} = null;
                                }

                                if(!$value) {
                                    continue;
                                }

                                $produit->{$node}->{$key} += $value;
                            }
                        }
                    }
                    $recap[$certification->getHash()]->produits[$appellation->getHash()] = $produit;
                }
            }
        }

        return $recap;
    }

}