<?php

/* This file is part of the acVinComptePlugin package.
 * Copyright (c) 2011 Actualys
 * Authors :
 * Tangui Morlier <tangui@tangui.eu.org>
 * Charlotte De Vichet <c.devichet@gmail.com>
 * Vincent Laurent <vince.laurent@gmail.com>
 * Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * acVinComptePlugin task.
 *
 * @package    acVinComptePlugin
 * @subpackage lib
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Charlotte De Vichet <c.devichet@gmail.com>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @version    0.1
 */
class acVinCompteUpdateProductionTagTask extends sfBaseTask {

    protected $debug = false;
    protected static $accent_matching = array('accent' => array('Ô','ô', 'Â','â','ê','è','é','É', 'È','Ê','û','Û','î','Î'), 'to_replace' => array('o','o', 'A','a','e','e','e','e','e','e','u','u','i','i'));

    protected function configure() {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('campagne', null, sfCommandOption::PARAMETER_OPTIONAL, 'Campagne', null),
            new sfCommandOption('reinitialisation_tags_produit', null, sfCommandOption::PARAMETER_REQUIRED, 'Reset tags', false),
            new sfCommandOption('reinitialisation_tags_export', null, sfCommandOption::PARAMETER_REQUIRED, 'Reset tags', false),
            new sfCommandOption('reinitialisation_tags_domaines', null, sfCommandOption::PARAMETER_REQUIRED, 'Reset tags', false),
            new sfCommandOption('debug', null, sfCommandOption::PARAMETER_OPTIONAL, 'use only one code creation', '0'),
        ));

        $this->namespace = 'compte';
        $this->name = 'updateProductionTag';
        $this->briefDescription = '';
        $this->detailedDescription = '';
    }

    protected function execute($arguments = array(), $options = array()) {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $context = sfContext::createInstance($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->debug = array_key_exists('debug', $options) && $options['debug'];


        if (!isset($options['campagne']) || !$options['campagne']) {
            $campagne = ConfigurationClient::getInstance()->getCurrentCampagne();
        } else {
            $campagne = $options['campagne'];
        }

        $this->logSection("campagne use", $campagne);

        foreach (EtablissementAllView::getInstance()->findByInterproStatutAndFamilleVIEW('INTERPRO-declaration', EtablissementClient::STATUT_ACTIF, null) as $e) {
            $id = $e->key[EtablissementAllView::KEY_ETABLISSEMENT_ID];
            $tags = array('export' => array(), 'produit' => array(), 'domaines' => array());

            $mvts = SV12MouvementsConsultationView::getInstance()->getByIdentifiantAndCampagne($id, $campagne);
            foreach ($mvts as $m) {
                $produit_libelle = $this->getProduitLibelle($m->produit_hash);
                if(!$produit_libelle) {
                    continue;
                }
                $tags['produit'][$produit_libelle] = 1;
            }
            $mvts = DRMMouvementsConsultationView::getInstance()->getByIdentifiantAndCampagne($id, $campagne);
            foreach ($mvts as $m) {
                $produit_libelle = $this->getProduitLibelle($m->produit_hash);
                if(!$produit_libelle) {
                    continue;
                }
                $tags['produit'][$produit_libelle] = 1;
                if ($m->detail_libelle && preg_match("/Export/", $m->type_libelle)) {
                    $tags['export'][$m->detail_libelle] = 1;
                }
            }
            $contratDomaines = VracDomainesView::getInstance()->findDomainesByVendeur(str_replace('ETABLISSEMENT-', '', $id));
            foreach ($contratDomaines->rows as $domaineView) {
                $domaine = $this->replaceAccents($domaineView->key[VracDomainesView::KEY_DOMAINE]);
                $tags['domaines'][$domaine] = 1;
            }

            $etablissement = EtablissementClient::getInstance()->findByIdentifiant(str_replace('ETABLISSEMENT-', '', $id));
            if (!$etablissement) {
                throw new sfException("etablissement $id non trouvé");
            }
            $compte = $etablissement->getContact();
            if ($options['reinitialisation_tags_export']) {
                $compte->tags->remove('export');
                $this->logSection("reset tags export", $compte->identifiant);
            }
            if ($options['reinitialisation_tags_produit']) {
                $compte->tags->remove('produit');
                $this->logSection("reset tags produit", $compte->identifiant);
            }
            if ($options['reinitialisation_tags_domaines']) {
                $compte->tags->remove('domaines');
                $this->logSection("reset tags produit", $compte->identifiant);
            }
            if (!count($tags)) {
                continue;
            }
            foreach ($tags as $type => $ttags) {
                foreach ($ttags as $t => $null) {
                    $compte->addTag($type, $t);
                }
            }

            if(!$compte){
               echo ("compte de l'établissement $etablissement->_id non trouvé\n");
               continue;
            }

            try {

                $compte->save();
            } catch (Exception $exc) {
                echo $exc."\n";
                continue;
            }

            $this->logSection("done", $compte->identifiant);
        }
    }

    public function getProduitLibelle($hash) {
        $configuration = ConfigurationClient::getInstance()->getCurrent();

        if(!$configuration->exist($hash)) {
            echo "Hash non trouvé :".$hash."\n";
            return null;
        }


        return $this->replaceAccents($configuration->get($hash)->getLibelleFormat(null, "%format_libelle%"));
    }

    protected function replaceAccents($s) {
        return str_replace(self::$accent_matching['accent'], self::$accent_matching['to_replace'], $s);
    }

}
