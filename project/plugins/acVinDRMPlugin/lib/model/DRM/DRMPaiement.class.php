<?php

/**
 * Model for DRMPaiement
 *
 */
class DRMPaiement extends BaseDRMPaiement {

    const FREQUENCE_ANNUELLE = 'ANNUELLE';
    const FREQUENCE_MENSUELLE = 'MENSUELLE';
    const MOYEN_NUMERAIRE = 'NUMERAIRE';
    const MOYEN_OBLIGATION_CAUTIONNEES = 'OBLIGATION_CAUTIONNEES';
    const MOYEN_CHEQUE = 'CHEQUE';
    const MOYEN_VIREMENT = 'VIREMENT';
    const NUM_MOIS_DEBUT_CAMPAGNE = 8;

    public static $frequence_paiement_libelles = array(self::FREQUENCE_ANNUELLE => 'Annuelle',
        self::FREQUENCE_MENSUELLE => 'Mensuelle');

    public static $moyens_paiement_libelles = array(self::MOYEN_NUMERAIRE => 'Numéraire',
        self::MOYEN_OBLIGATION_CAUTIONNEES => 'Obligation cautionnées',
        self::MOYEN_VIREMENT => 'Virement',
        self::MOYEN_CHEQUE => 'Chèque');


    public function isAnnuelle() {
        return ($this->frequence == self::FREQUENCE_ANNUELLE) ? true : false;
    }

    public static function isDebutCampagne($numeroMois = null) {
        if (!$numeroMois) {
            $numeroMois = date('m');
        }
        return ($numeroMois == self::NUM_MOIS_DEBUT_CAMPAGNE) ? true : false;
    }

}
