<?php
/**
 * Inheritance tree class _DRMTotal
 *
 */

abstract class _DRMTotal extends acCouchdbDocumentTree {

    public function getConfig() {

        return $this->getDocument()->getConfig()->get($this->getHash());
    }

    public function getConfigProduitsAttributes($teledeclarationMode = false) {
        $attributes = array(_ConfigurationDeclaration::ATTRIBUTE_CVO_ACTIF);

        if($teledeclarationMode && $this->getDocument()->isTeledeclare()) {
            $attributes[] = _ConfigurationDeclaration::ATTRIBUTE_DOUANE_ACTIF;
        }

        return $attributes;
    }

    public function getConfigProduits($teledeclarationMode = false) {

        return $this->getConfig()->formatProduits($this->getDocument()->getFirstDayOfPeriode(),
                                                  $this->getDocument()->getInterpro()->get('_id'),
                                                  $this->getDocument()->getDepartement(),
                                                  "%format_libelle% (%code_produit%)",
                                                  $this->getConfigProduitsAttributes($teledeclarationMode));
    }

    public function getParentNode() {

        return $this->getParent()->getParent();
    }

    public function getLibelle($format = "%format_libelle%") {

      return $this->getConfig()->getLibelleFormat(null, $format);
    }

   	public function getCode($format = "%g%%a%%m%%l%%co%%ce%") {

      return $this->getConfig()->getCodeFormat();
    }

    protected function init($params = array()) {
        parent::init($params);
        $this->total_debut_mois = null;
        $this->total_entrees = null;
        $this->total_entrees_revendique = null;
        $this->total_recolte = null;
        $this->total_sorties = null;
        $this->total_sorties_revendique = null;
        $this->total_facturable = null;
        $this->total_revendique = null;
        $this->total = null;
    }

	protected function update($params = array()) {
        parent::update($params);
        $this->total_debut_mois = $this->getTotalByKey('total_debut_mois');
        $this->total_recolte = $this->getTotalByKey('total_recolte');
        $this->total_entrees = $this->getTotalByKey('total_entrees');
        $this->total_sorties = $this->getTotalByKey('total_sorties');
        $this->total_entrees_revendique = $this->getTotalByKey('total_entrees_revendique');
        $this->total_sorties_revendique = $this->getTotalByKey('total_sorties_revendique');
        $this->total_facturable = $this->getTotalByKey('total_facturable');
        $this->total_revendique = $this->getTotalByKey('total_revendique');
        $this->total = $this->get('total_debut_mois') + $this->get('total_entrees') - $this->get('total_sorties');
    }

    private function getTotalByKey($key) {
    	$sum = 0;
    	foreach ($this->getFields() as $field => $k) {
    		if ($this->fieldIsCollection($field)) {
    			foreach ($this->get($field) as $f => $v) {
    				if ($this->get($field)->fieldIsCollection($f)) {
    					if ($v->exist($key)) {
		    				$sum += $v->get($key);
    					}
    				}
    			}
    		}
    	}
    	return $sum;
    }

    public function hasStockEpuise() {

        return $this->total_debut_mois == 0 && !$this->hasMouvement();
    }


    public function sommeLignes($lines) {
        $sum = 0;
        foreach($this->getChildrenNode() as $item) {
            $sum += $item->sommeLignes($lines);
        }

        return $sum;
    }

    public function hasMouvement() {

        return $this->total_entrees > 0 || $this->total_sorties > 0;
    }

    public function hasMouvementCheck() {
        foreach($this->getChildrenNode() as $item) {
            if($item->hasMouvementCheck()) {
                return true;
            }
        }

        return false;
    }

    public function nbComplete() {
        $nb = 0;
        foreach($this->getChildrenNode() as $item) {
        	$nb += $item->nbComplete();
        }

        return $nb;
    }

    public function nbToComplete() {
        $nb = 0;
        foreach($this->getChildrenNode() as $item) {
        	$nb += $item->nbToComplete();
        }

        return $nb;
    }

    public function isComplete() {
        foreach($this->getChildrenNode() as $item) {
            if(!$item->isComplete()) {
                return false;
            }
        }

        return true;
    }

    public function getPreviousSisterWithMouvementCheck() {
        $item = $this->getPreviousSister();
        $sister = null;

        if ($item) {
            $sister = $item;
        }

        if (!$sister) {
            $item = $this->getParentNode()->getPreviousSisterWithMouvementCheck();
            if ($item) {

               $sister = $item->getChildrenNode()->getLast();
            }
        }

        if ($sister && !$sister->hasMouvementCheck()) {

            return $sister->getPreviousSisterWithMouvementCheck();
        }

        return $sister;
    }

    public function getNextSisterWithMouvementCheck() {
        $item = $this->getNextSister();
        $sister = null;

        if ($item) {
            $sister = $item;
        }

        if (!$sister) {
            $item = $this->getParentNode()->getNextSisterWithMouvementCheck();
            if ($item) {

               $sister = $item->getChildrenNode()->getFirst();
            }
        }

        if ($sister && !$sister->hasMouvementCheck()) {

            return $sister->getNextSisterWithMouvementCheck();
        }

        return $sister;
    }

    public function getProduits() {
        $produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $produits = array_merge($produits, $item->getProduits());
        }

        return $produits;
    }

    public function reorderByConf() {
        $children = array();

        foreach($this->getChildrenNode() as $child) {
            $children[$child->getKey()] = $child->getData();
        }

        foreach($children as $key => $child) {
            $this->getChildrenNode()->remove($key);
        }

        foreach($this->getConfig()->getChildrenNode() as $child) {
            if(!array_key_exists($child->getKey(), $children)) {
                continue;
            }

            $child_added = $this->getChildrenNode()->add($child->getKey(), $children[$child->getKey()]);
            $child_added->reorderByConf();
        }

    }

    public function getProduitsDetails($teledeclarationMode = false, $detailsKey = null) {
        $produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $produits = array_merge($produits, $item->getProduitsDetails($teledeclarationMode, $detailsKey));
        }

        return $produits;
    }

    public function getProduitsDetailsSorted($teledeclarationMode = false, $detailsKey = null) {
        $produits = $this->getProduitsDetails($teledeclarationMode, $detailsKey);

        //uasort($produits, "_DRMTotal::sortProduitByLibelle");

        return $produits;
    }

    public static function sortProduitByLibelle($p1, $p2) {
        return $p1->getLibelle("%format_libelle%") > $p2->getLibelle("%format_libelle%");
    }

    public function getProduitsLibelle($format = "%format_libelle% <span class=\"labels\">%la%</span>", $label_separator = ", ") {
        $produits = $this->getProduitsDetails();
        $produits_format = array();
        foreach($produits as $key => $produit) {
            $produits_format[$key] = $produit->getLibelle($format);
        }

        return $produits_format;
    }

    public function getLieuxArray() {
        $lieux = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $lieux = array_merge($lieux, $item->getLieuxArray());
        }

        return $lieux;
    }

    protected function _cleanNoeuds() {
        $noeuds = array();

        foreach($this->getChildrenNode() as $key => $item) {
            if($item instanceof _DRMTotal || $item instanceof DRMDetails) {
                $noeud = $item->cleanNoeuds();
                if(isset($noeud)) {
                    $noeuds[] = $noeud;
                }
            }
        }

        foreach($noeuds as $noeud) {
            $noeud->delete();
        }
    }

    public function cleanNoeuds() {
        $this->_cleanNoeuds();

        if (count($this->getChildrenNode()) == 0) {

            return $this;
        }

        return null;
    }

    public function hasProduitDetailsWithStockNegatif() {
        foreach ($this->getProduitsDetails() as $prod) {
            if ($prod->hasProduitDetailsWithStockNegatif()) {
                return true;
            }
        }
        return false;
    }

    abstract public function getChildrenNode();

}
