<?php

/**
 * Model for RelanceTypeExplications
 *
 */
class RelanceTypeExplications extends BaseRelanceTypeExplications {

    public function storeVerificationForAlerte($alerte) {
        sfApplicationConfiguration::getActive()->loadHelpers('Float');
        sfApplicationConfiguration::getActive()->loadHelpers('Date');
        $this->origine_identifiant = $alerte->value[AlerteRechercheView::VALUE_ID_DOC];
        $this->origine_libelle = $alerte->value[AlerteRechercheView::VALUE_LIBELLE_DOCUMENT];
        $this->origine_date = date('Y-m-d');
        $this->alerte_identifiant = $alerte->id;
        $type_alerte = $alerte->key[AlerteRelanceView::KEY_TYPE_ALERTE];
        switch ($type_alerte) {
            case AlerteClient::DRM_MANQUANTE:
            case AlerteClient::DRA_MANQUANTE:
                $id = $alerte->value[AlerteRelanceView::VALUE_ID_DOC];
                if (preg_match('/^DRM-([0-9]{8})-([0-9]{6})*/', $id, $matches)) {
                    $periode = $matches[2];
                }
                if (!$periode)
                    throw new sfException("La periode de l'alerte $alerte->id ne peut être identifiée.");
                $doc = DRMClient::getInstance()->find($alerte->value[AlerteRelanceView::VALUE_ID_DOC]);
                $this->explications = ConfigurationClient::getInstance()->getPeriodeLibelle($periode);
                break;
//            case AlerteClient::VRAC_NON_SOLDES:
//                $doc = VracClient::getInstance()->find($alerte->value[AlerteRelanceView::VALUE_ID_DOC]);
//                $acheteur = $doc->getAcheteurObject();
//                $coordonneesAcheteur = $acheteur->raison_sociale . ' ' . $acheteur->getSiegeAdresses();
//                $volume_enleve = ($doc->volume_enleve) ? sprintFloatFr($doc->volume_enleve) : sprintFloatFr(0);
//                $this->explications = $doc->numero_contrat . '|' . format_date($doc->date_signature, 'dd/MM/yyyy') . '|' . $coordonneesAcheteur . '|' . sprintFloatFr($doc->volume_propose) . '|' . $volume_enleve . '|' . $doc->commentaire;
//                break;
//            case AlerteClient::VRAC_ATTENTE_ORIGINAL:
//            case AlerteClient::VRAC_PRIX_DEFINITIFS:
//                $doc = VracClient::getInstance()->find($alerte->value[AlerteRelanceView::VALUE_ID_DOC]);
//                $vendeur = $doc->getVendeurObject();
//                $coordonneesVendeur = $vendeur->raison_sociale . ' ' . $vendeur->getSiegeAdresses();
//                $this->explications = $doc->numero_contrat . '|' . format_date($doc->date_signature, 'dd/MM/yyyy') . '|' . $coordonneesVendeur . '|' . sprintFloatFr($doc->volume_propose);
//                break;
//            case AlerteClient::DS_NON_VALIDEE:
//                $doc = DSClient::getInstance()->find($alerte->value[AlerteRelanceView::VALUE_ID_DOC]);
//                $this->explications = format_date($doc->date_stock, 'dd/MM/yyyy');
//                break;
//            case AlerteClient::DRM_STOCK_NEGATIF:
//                $id = $alerte->value[AlerteRelanceView::VALUE_ID_DOC];
//                if (preg_match('/^DRM-([0-9]{8})-([0-9]{6})*/', $id, $matches)) {
//                    $periode = $matches[2];
//                }
//                if (!$periode)
//                    throw new sfException("La periode de l'alerte $alerte->id ne peut être identifiée.");
//                $doc = DRMClient::getInstance()->find($alerte->value[AlerteRelanceView::VALUE_ID_DOC]);
//                $this->explications = ConfigurationClient::getInstance()->getPeriodeLibelle($periode);
//            break;
//            case AlerteClient::SV12_MANQUANTE:
//                break;
//            case AlerteClient::VRAC_SANS_SV12:
//                $etbId = $alerte->key[AlerteRelanceView::KEY_IDENTIFIANT_ETB];
//                $campagne = $alerte->key[AlerteRelanceView::KEY_CAMPAGNE];
//                $contats_mouts = VracClient::getInstance()->retrieveBySoussigneStatutAndType($etbId, $campagne, VracClient::STATUS_CONTRAT_NONSOLDE, VracClient::TYPE_TRANSACTION_MOUTS, null)->rows;
//                $contats_raisins = VracClient::getInstance()->retrieveBySoussigneStatutAndType($etbId, $campagne, VracClient::STATUS_CONTRAT_NONSOLDE, VracClient::TYPE_TRANSACTION_RAISINS, null)->rows;
//                $vracs = array_merge($contats_mouts, $contats_raisins);
//                $this->explications = "";
//                foreach ($vracs as $key => $vrac) {
//                    $this->explications .= $vrac->value[VracClient::VRAC_VIEW_NUMCONTRAT];
//                    if ($key < count($vracs) - 1)
//                        $this->explications .= ", ";
//                }
//                break;
//            case AlerteClient::ECART_DREV_DRM:
//                $etbId = $alerte->key[AlerteRelanceView::KEY_IDENTIFIANT_ETB];
//                $campagne = $alerte->key[AlerteRelanceView::KEY_CAMPAGNE];
//                $region = $alerte->key[AlerteRelanceView::KEY_REGION];
//                $idDRM = $alerte->value[AlerteRelanceView::VALUE_ID_DOC];
//                $drm = DRMClient::getInstance()->find($idDRM);
//                if (!$drm)
//                    throw new sfException("La drm $idDRM n'existe pas, la relance du viticulteur $etbId ne peut pas être éditée.");
//                $rev = RevendicationClient::getInstance()->find(RevendicationClient::getInstance()->getId($region, $campagne));
//                if (!$rev)
//                    throw new sfException("La déclaration de revendication de  $region pour la campagne $campagne n'existe pas, la relance du viticulteur $etbId ne peut pas être éditée.");
//                $config = new AlerteConfig(AlerteClient::ECART_DREV_DRM);
//                $this->explications = "";
//                if ($rev->exist('datas') && $rev->datas->exist($etbId)) {
//                    foreach ($rev->datas->{$etbId}->produits as $produit) {
//                        $prod_node = $drm->getProduit($produit->produit_hash);
//                        $stock_drm = 0;
//                        if ($prod_node) {
//                            $stock_drm = $prod_node->entrees->recolte;
//                        }
//                        if (!$stock_drm)
//                            $stock_drm = 0;
//                        $rev_vol = 0;
//                        foreach ($produit->volumes as $num_ca => $vol) {
//                            $rev_vol+=$vol->volume;
//                        }
//                        $diff = $stock_drm - $rev_vol;
//                        $seuil = $config->getOption('seuil');
//                        if (abs($diff) > (abs($rev_vol) * ($seuil / 100))) {
//                            $libelle = ConfigurationClient::getCurrent()->get($produit->produit_hash)->getLibelleFormat(null, "%format_libelle% %la%");
//                            $this->explications .= $libelle . '|' . sprintFloatFr($rev_vol) . '|' . sprintFloatFr($stock_drm) . '|' . sprintFloatFr($diff) . ' \\\\ ';
//                        }
//                    }
//                }
//                break;
//            case AlerteClient::ECART_DS_DRM_JUILLET:
//                $etbId = $alerte->key[AlerteRelanceView::KEY_IDENTIFIANT_ETB];
//                $idDS = $alerte->value[AlerteRelanceView::VALUE_ID_DOC];
//                $ds = DSClient::getInstance()->find($idDS);
//                if (!$ds)
//                    throw new sfException("La ds $ds n'existe pas, la relance du viticulteur $etbId ne peut pas être éditée.");
//                $config = new AlerteConfig(AlerteClient::ECART_DS_DRM_JUILLET);
//                $this->explications .= "";
//                foreach ($ds->declarations as $hashKey => $declaration) {
//                    $diff = $declaration->stock_initial - $declaration->stock_declare;
//                    $seuil = $config->getOption('seuil');
//                    if (abs($diff) > (abs($declaration->stock_initial) * ($seuil / 100))) {
//                        $libelle = ConfigurationClient::getCurrent()->get(str_replace('-', '/', $hashKey))->getLibelleFormat(null, "%format_libelle% %la%");
//                        $this->explications .= $libelle . '|' . sprintFloatFr($declaration->stock_initial) . '|' . sprintFloatFr($declaration->stock_declare) . '|' . sprintFloatFr($diff) . ' \\\\ ';
//                    }
//                }
//                break;
//            case AlerteClient::ECART_DS_DRM_AOUT:
//                $etbId = $alerte->key[AlerteRelanceView::KEY_IDENTIFIANT_ETB];
//                $idDS = $alerte->value[AlerteRelanceView::VALUE_ID_DOC];
//                $ds = DSClient::getInstance()->find($idDS);
//                $campagne = $alerte->key[AlerteRelanceView::KEY_CAMPAGNE];
//                if (!$ds)
//                    throw new sfException("La ds $ds n'existe pas, la relance du viticulteur $etbId ne peut pas être éditée.");
//                $config = new AlerteConfig(AlerteClient::ECART_DS_DRM_AOUT);
//                $periode = substr($campagne, 5,4).'08';
//                $drm = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($etbId, $periode);
//                if(!$drm)
//                    $periode = substr($campagne, 0,4).'08';
//                $drm = DRMClient::getInstance()->findMasterByIdentifiantAndPeriode($etbId, $periode);
//                if(!$drm)
//                    throw new sfException("La drm de l'etb $etbId pour la periode $periode n'existe pas dans l'alerte $alerte->id.");
//                $this->explications = "";
//                foreach ($ds->declarations as $hashKey => $declaration) {
//                    $prod_node = $drm->getProduit(str_replace('-', '/', $hashKey));
//                    $stock_drm = 0;
//                    if ($prod_node) {
//                        $stock_drm = $prod_node->total_debut_mois;
//                    }
//                    $diff = $stock_drm - $declaration->stock_declare;
//                    $seuil = $config->getOption('seuil');
//                    if (abs($diff) > (abs($declaration->stock_declare) * ($seuil / 100))) {
//                        $libelle = ConfigurationClient::getCurrent()->get(str_replace('-', '/', $hashKey))->getLibelleFormat(null, "%format_libelle% %la%");
//                        $this->explications .= $libelle . '|' . sprintFloatFr($stock_drm) . '|' . sprintFloatFr($declaration->stock_declare) . '|' . sprintFloatFr($diff) . ' \\\\ ';
//
//                    }
//                }
//                break;
            default:
                throw new sfException("L'alerte $alerte->id de type $type_alerte n'a pas été trouvée.");
        }
    }

}
