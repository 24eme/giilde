<?php
/**
 * BaseCSVDAE
 * 
 * Base model for CSVDAE
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property acCouchdbJson $_attachments
 * @property string $identifiant
 * @property string $periode
 * @property string $statut
 * @property acCouchdbJson $erreurs

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method acCouchdbJson getAttachments()
 * @method acCouchdbJson setAttachments()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getPeriode()
 * @method string setPeriode()
 * @method string getStatut()
 * @method string setStatut()
 * @method acCouchdbJson getErreurs()
 * @method acCouchdbJson setErreurs()
 
 */
 
abstract class BaseCSVDAE extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'CSVDAE';
    }
    
}