<?php

class DSNegoceProduitDetailsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new DSNegoceProduitDetailStocksForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}
