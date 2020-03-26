<?php
/**
 * BaseDRM
 * 
 * Base model for DRM
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $region
 * @property acCouchdbJson $editeurs
 * @property string $apurement_possible
 * @property string $raison_rectificative
 * @property string $etape
 * @property string $campagne
 * @property string $type_creation
 * @property string $email_transmission
 * @property string $periode
 * @property string $teledeclare
 * @property string $precedente
 * @property string $version
 * @property string $numero_archive
 * @property acCouchdbJson $droits
 * @property DRMDeclaration $declaration
 * @property acCouchdbJson $declarant
 * @property acCouchdbJson $societe
 * @property DRMDeclaratif $declaratif
 * @property string $identifiant
 * @property string $mode_de_saisie
 * @property string $commentaire
 * @property acCouchdbJson $interpros
 * @property acCouchdbJson $valide
 * @property acCouchdbJson $douane
 * @property acCouchdbJson $controles
 * @property acCouchdbJson $mouvements
 * @property DRMCrdsRegime $crds
 * @property DRMFavoris $favoris
 * @property acCouchdbJson $documents_annexes
 * @property DRMNonApurement $releve_non_apurement
 * @property string $observations
 * @property string $quantite_sucre
 * @property acCouchdbJson $transmission_douane

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getRegion()
 * @method string setRegion()
 * @method acCouchdbJson getEditeurs()
 * @method acCouchdbJson setEditeurs()
 * @method string getApurementPossible()
 * @method string setApurementPossible()
 * @method string getRaisonRectificative()
 * @method string setRaisonRectificative()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getTypeCreation()
 * @method string setTypeCreation()
 * @method string getEmailTransmission()
 * @method string setEmailTransmission()
 * @method string getPeriode()
 * @method string setPeriode()
 * @method string getTeledeclare()
 * @method string setTeledeclare()
 * @method string getPrecedente()
 * @method string setPrecedente()
 * @method string getVersion()
 * @method string setVersion()
 * @method string getNumeroArchive()
 * @method string setNumeroArchive()
 * @method acCouchdbJson getDroits()
 * @method acCouchdbJson setDroits()
 * @method DRMDeclaration getDeclaration()
 * @method DRMDeclaration setDeclaration()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method acCouchdbJson getSociete()
 * @method acCouchdbJson setSociete()
 * @method DRMDeclaratif getDeclaratif()
 * @method DRMDeclaratif setDeclaratif()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getModeDeSaisie()
 * @method string setModeDeSaisie()
 * @method string getCommentaire()
 * @method string setCommentaire()
 * @method acCouchdbJson getInterpros()
 * @method acCouchdbJson setInterpros()
 * @method acCouchdbJson getValide()
 * @method acCouchdbJson setValide()
 * @method acCouchdbJson getDouane()
 * @method acCouchdbJson setDouane()
 * @method acCouchdbJson getControles()
 * @method acCouchdbJson setControles()
 * @method acCouchdbJson getMouvements()
 * @method acCouchdbJson setMouvements()
 * @method DRMCrdsRegime getCrds()
 * @method DRMCrdsRegime setCrds()
 * @method DRMFavoris getFavoris()
 * @method DRMFavoris setFavoris()
 * @method acCouchdbJson getDocumentsAnnexes()
 * @method acCouchdbJson setDocumentsAnnexes()
 * @method DRMNonApurement getReleveNonApurement()
 * @method DRMNonApurement setReleveNonApurement()
 * @method string getObservations()
 * @method string setObservations()
 * @method string getQuantiteSucre()
 * @method string setQuantiteSucre()
 * @method acCouchdbJson getTransmissionDouane()
 * @method acCouchdbJson setTransmissionDouane()
 
 */
 
abstract class BaseDRM extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRM';
    }
    
}