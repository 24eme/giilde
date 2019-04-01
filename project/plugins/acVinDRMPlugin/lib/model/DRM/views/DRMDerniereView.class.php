<?php
class DRMDerniereView extends acCouchdbView
{
    const KEY_CAMPAGNE = 0;
    const KEY_PERIODE = 1;

    public static function getInstance() {

        return acCouchdbManager::getView('drm', 'derniere', 'DRM');
    }

    public function findLast() {

        $rows = $this->client->reduce(false)->descending(true)->limit(1)->getView($this->design, $this->view)->rows;

        if(count($rows) == 0) {

            return null;
        }

        return $this->build($rows[0]);
    }

    public function findByCampagneAndPeriode($campagne,$periode) {

      return $this->client->reduce(false)
                    ->startkey(array($campagne,$periode))
                    ->endkey(array($campagne,$periode, array()))
                    ->getView($this->design, $this->view)->rows;
    }

    public function findLastPeriode() {
        $last_drm = $this->findLast();

        if(!$last_drm) {

            return null;
        }

        return $last_drm->periode;
    }

    public function builds($rows) {
        $drms = array();

        foreach($rows as $row) {
            $key = $row->key[$row->key[self::KEY_PERIODE]];
            $drms[$key] = $this->build($row);
        }

        return $drms;
    }

    public function build($row) {
        $drm = new stdClass();
        $drm->campagne = $row->key[self::KEY_CAMPAGNE];
        $drm->periode = $row->key[self::KEY_PERIODE];

        return $drm;
    }
}
