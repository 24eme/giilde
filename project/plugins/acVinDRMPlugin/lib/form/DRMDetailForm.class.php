<?php

class DRMDetailForm extends acCouchdbObjectForm {

    protected $_label_choices;

    public function configure() {
        
        $this->stocks_debut = new DRMDetailStocksDebutForm($this->getObject()->stocks_debut);
        $this->embedForm('stocks_debut', $this->stocks_debut);

        $this->entrees = new DRMDetailEntreesForm($this->getObject()->entrees);
        $this->embedForm('entrees', $this->entrees);

        $this->sorties = new DRMDetailSortiesForm($this->getObject()->sorties);
        $this->embedForm('sorties', $this->sorties);

        $this->stocks_fin = new DRMDetailStocksFinForm($this->getObject()->stocks_fin);
        $this->embedForm('stocks_fin', $this->stocks_fin);

        $this->widgetSchema->setNameFormat('drm_detail[%s]');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->add('no_movements', false);
        $this->getObject()->add('edited', true);
        $this->getObject()->getDocument()->update();
    }

    public function getLabelChoices() {
        if (is_null($this->_label_choices)) {
            $this->_label_choices = array();
            foreach ($this->getObject()->getDocument()->getConfig()->label as $key => $label) {
                $this->_label_choices[$key] = $label;
            }
        }
        return $this->_label_choices;
    }

}
