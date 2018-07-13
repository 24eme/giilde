<?php
/**
 * BaseDAE
 * 
 * Base model for DAE
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $date
 * @property acCouchdbJson $declarant
 * @property string $produit_hash
 * @property string $label
 * @property string $millesime
 * @property string $type_acheteur
 * @property string $destination
 * @property string $volume
 * @property string $volume_hl
 * @property string $contenance
 * @property string $prix_ht

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getDate()
 * @method string setDate()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method string getProduitHash()
 * @method string setProduitHash()
 * @method string getLabel()
 * @method string setLabel()
 * @method string getMillesime()
 * @method string setMillesime()
 * @method string getTypeAcheteur()
 * @method string setTypeAcheteur()
 * @method string getDestination()
 * @method string setDestination()
 * @method string getVolume()
 * @method string setVolume()
 * @method string getVolumeHl()
 * @method string setVolumeHl()
 * @method string getContenance()
 * @method string setContenance()
 * @method string getPrixHt()
 * @method string setPrixHt()
 
 */
 
abstract class BaseDAE extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DAE';
    }
    
}