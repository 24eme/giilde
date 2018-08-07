<?php
/**
 * BaseFichier
 * 
 * Base model for Fichier
 *
 * @property string $_id
 * @property string $_rev
 * @property acCouchdbJson $_attachments
 * @property string $type
 * @property string $fichier_id
 * @property string $identifiant
 * @property string $date_depot
 * @property string $libelle
 * @property string $mime
 * @property integer $visibilite
 * @property integer $papier
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method acCouchdbJson getAttachments()
 * @method acCouchdbJson setAttachments()
 * @method string getType()
 * @method string setType()
 * @method string getFichierId()
 * @method string setFichierId()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getDateDepot()
 * @method string setDateDepot()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getMime()
 * @method string setMime()
 * @method integer getVisibilite()
 * @method integer setVisibilite()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseFichier extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Fichier';
    }
    
}