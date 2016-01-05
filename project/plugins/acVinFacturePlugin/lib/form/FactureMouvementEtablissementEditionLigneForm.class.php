<?php

class FactureMouvementEtablissementEditionLigneForm extends acCouchdbObjectForm {

    protected $interpro_id;
    protected $keyMvt = null;
    
    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->interpro_id = $options['interpro_id'];
        $this->keyMvt = $options['keyMvt'];
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        foreach ($this->getObject() as $uniqkeyMvt => $mouvement) {
            $this->embedForm($uniqkeyMvt, new FactureMouvementEditionLigneForm($mouvement,array('interpro_id' => $this->interpro_id, 'uniqkeyMvt' =>$uniqkeyMvt)));
        }
        $this->widgetSchema->setNameFormat('facture_mouvement_etablissement_edition_ligne[%s]');
    }

  public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if (!array_key_exists($key, $taintedValues)) {
                $this->unEmbedForm($key);
            }
        }
        foreach ($taintedValues as $key => $values) {
            if (!is_array($values) || array_key_exists($key, $this->embeddedForms)) {
                continue;
            }
            $this->embedForm($key, new FactureMouvementEditionLigneForm($this->getObject()->add(),array('interpro_id' => $this->interpro_id, 'uniqkeyMvt' =>$key)));
        }
    }

    public function unEmbedForm($key) {
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
        $this->getObject()->remove($key);
    }

  
    
}
