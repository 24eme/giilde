<?php

class CSVDAEClient extends acCouchdbClient {

    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';

    public static function getInstance() {
        return acCouchdbManager::getClient("CSVDAE");
    }

    public function createOrFindDocFromDAES($path = null, $identifiant, $periode ) {
        $csvId = $this->buildId($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT);
        $csvDae = $this->find($csvId, $hydrate);
        if ($csvDae) {
            if($path){
                $csvDae->storeAttachment($path, 'text/csv', $csvDae->getFileName());
            }
            return $csvDae;
        }
        $csvDae = new CSVDAE();
        $csvDae->_id = $csvId;
        $csvDae->identifiant = $identifiant;
        $csvDae->periode = $periode;
        if($path){
            $csvDae->storeAttachment($path, 'text/csv', $csvDae->getFileName());
        }
        $csvDae->save();
        return $csvDae;
    }

    public function buildId($identifiant, $periode) {
        return "CSVDAE-" . $identifiant . "-" . $periode;
    }

}
