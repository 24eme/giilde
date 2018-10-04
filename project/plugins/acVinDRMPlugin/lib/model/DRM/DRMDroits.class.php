<?php

class DRMDroits extends BaseDRMDroits {

    const DROIT_CVO = 'CVO';
    const DROIT_DOUANE = 'Douane';

    public static $correspondanceGenreKey = array('TRANQ' => 'TRANQ', 'EFF' => 'MOUSSEUX', 'MOU' => 'MOUSSEUX');
    public static $correspondanceGenreLibelle = array('TRANQ' => 'Vins tranquilles', 'EFF' => 'Vins mousseux', 'MOU' => 'Vins mousseux');

    public function getCumul() {
        $sum = 0;
        foreach ($this->toArray() as $key => $value) {
            $sum += $value->getCumul();
        }
        return $sum;
    }

    public function initDroitsDouane() {
        $conf = $this->getDocument()->getConfig();
        $date = $this->getDocument()->getDate();
        foreach ($conf->declaration->certifications as $keyCertif => $certification) {
            foreach ($certification->genres as $keyGenre => $genre) {
                if(!array_key_exists($keyGenre, self::$correspondanceGenreKey)) {
                    continue;
                }
                $droitsDouaneConf = $genre->getDroitDouane($date);
                $droitDouane = $this->getOrAdd(self::$correspondanceGenreKey[$keyGenre]);
                $droitDouane->volume_reintegre = 0;
                $droitDouane->volume_taxe = 0;
                $droitDouane->taux = $droitsDouaneConf->taux * 1;
                $droitDouane->code = $droitsDouaneConf->code;
                $droitDouane->libelle = self::$correspondanceGenreLibelle[$keyGenre];
                $droitDouane->updateTotal();
            }
        }
    }

    public function updateDroitDouane($genreKey, $configurationCepageNode, $vol, $reintegration = false) {
        $keyDouane = self::$correspondanceGenreKey[$genreKey];

        $date = $this->getDocument()->getDate();
        $droitsConfig = $configurationCepageNode->getDroitDouane($date);

        $genreDouaneNode = $this->getOrAdd($keyDouane);
        if ($reintegration) {
            $genreDouaneNode->volume_reintegre += $vol;
        } else {
            $genreDouaneNode->volume_taxe += $vol;
        }
        $genreDouaneNode->taux = $droitsConfig->taux * 1.0;
        $genreDouaneNode->code = $droitsConfig->code;
        $genreDouaneNode->libelle = self::$correspondanceGenreLibelle[$genreKey];
        $genreDouaneNode->updateTotal();
    }

}
