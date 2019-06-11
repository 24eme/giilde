<?php

/**
 * Model for DRMNonApurement
 *
 */
class DRMNonApurementItem extends BaseDRMNonApurementItem {
    public function getDateEmission() {
        $d = $this->_get('date_emission');

        return preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '\3/\2/\1', $d);
    }

    public function setDateEmission($d) {
        $d = preg_replace('/(\d{2}).(\d{2}).(\d{4})/', '$3-$2-$1', $d);

        return $this->_set('date_emission', $d);
    }
}
