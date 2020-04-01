<?php

class CSVDRMClient extends acCouchdbClient {

    const TYPE_DRM = "DRM";
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';

    public static function getInstance() {
        return acCouchdbManager::getClient("CSVDRM");
    }

    public function findFromIdentifiantPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->find($this->buildId($identifiant, $periode), $hydrate);
    }

    public function createOrFindDocFromDRM($path, DRM $drm) {
        $csvId = $this->buildId($drm->identifiant, $drm->periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT);
        $csvDrm = $this->find($csvId, $hydrate);
        if ($csvDrm) {
            $csvDrm->storeAttachment($path, 'text/csv', $csvDrm->getFileName());
            return $csvDrm;
        }
        $csvDrm = new CSVDRM();
        $csvDrm->_id = $csvId;
        $csvDrm->identifiant = $drm->identifiant;
        $csvDrm->periode = $drm->periode;
        $csvDrm->storeAttachment($path, 'text/csv', $csvDrm->getFileName());
        $csvDrm->save();
        return $csvDrm;
    }

    public function buildId($identifiant, $periode) {
        return "CSVDRM-" . $identifiant . "-" . $periode;
    }

}
