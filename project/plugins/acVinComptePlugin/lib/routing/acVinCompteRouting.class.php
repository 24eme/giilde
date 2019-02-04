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
 * acVinComptePlugin configuration.
 *
 * @package    acVinComptePlugin
 * @subpackage lib
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Charlotte De Vichet <c.devichet@gmail.com>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @version    0.1
 */
class acVinCompteRouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {
        $r = $event->getSubject();
        //$r->prependRoute('ac_vin_login', new sfRoute('/', array('module' => 'acVinCompte', 'action' => 'login')));
        /*$r->prependRoute('ac_vin_logout', new sfRoute('/logout', array('module' => 'acVinCompte', 'action' => 'logout')));
        $r->prependRoute('ac_vin_login', new sfRoute('/login', array('module' => 'acVinCompte', 'action' => 'login')));
        $r->prependRoute('ac_vin_forbidden', new sfRoute('/forbidden', array('module' => 'acVinCompte', 'action' => 'forbidden')));*/

        $r->prependRoute('compte_teledeclarant_code_creation', new sfRoute('/teledeclarant/code_creation', array('module' => 'compte_teledeclarant', 'action' => 'first')));
        $r->prependRoute('compte_teledeclarant_cgu', new sfRoute('/teledeclarant/cgu', array('module' => 'compte_teledeclarant', 'action' => 'cgu')));
        $r->prependRoute('compte_teledeclarant_creation', new sfRoute('/teledeclarant/creation', array('module' => 'compte_teledeclarant', 'action' => 'creation')));
        $r->prependRoute('compte_teledeclarant_modification', new sfRoute('/teledeclarant/mon_compte', array('module' => 'compte_teledeclarant', 'action' => 'modification')));
        $r->prependRoute('compte_teledeclarant_mot_de_passe_oublie_login', new sfRoute('/mot_de_passe_oublie/login/:login/:mdp', array('module' => 'compte_teledeclarant', 'action' => 'motDePasseOublieLogin')));
        $r->prependRoute('compte_teledeclarant_mot_de_passe_oublie', new sfRoute('/mot_de_passe_oublie', array('module' => 'compte_teledeclarant', 'action' => 'motDePasseOublie')));
        $r->prependRoute('compte_teledeclarant_mot_de_passe_oublie_confirm', new sfRoute('/mot_de_passe_oublie/confirm', array('module' => 'compte_teledeclarant', 'action' => 'motDePasseOublieConfirm')));
        $r->prependRoute('compte_teledeclarant_modification_oublie', new sfRoute('/teledeclarant/mot_de_passe_oublie', array('module' => 'compte_teledeclarant', 'action' => 'modificationOublie')));
        $r->prependRoute('reglementation_generale_des_transactions',  new sfRoute('/contrats/reglementation_generale_des_transactions', array('module' => 'compte_teledeclarant', 'action' => 'reglementationGenerale')));

        $r->prependRoute('compte_search', new sfRoute('/compte/search', array('module' => 'compte', 'action' => 'search')));
        $r->prependRoute('compte_search_csv', new sfRoute('/compte/search/csv', array('module' => 'compte', 'action' => 'searchcsv')));
        $r->prependRoute('compte_addtag', new sfRoute('/compte/search/addtag', array('module' => 'compte', 'action' => 'addtag')));
        $r->prependRoute('compte_removetag', new sfRoute('/compte/search/removetag', array('module' => 'compte', 'action' => 'removetag')));

        $r->prependRoute('compte_ajout', new SocieteRoute('/compte/:identifiant/nouveau',
            array('module' => 'compte',
                'action' => 'ajout'),
            array('sf_method' => array('get', 'post')),
            array('model' => 'Societe',
                'type' => 'object')));
        $r->prependRoute('compte_modification', new CompteRoute('/compte/:identifiant/modification',
            array('module' => 'compte',
                'action' => 'modification'),
            array('sf_method' => array('get', 'post')),
            array('model' => 'Compte',
                'type' => 'object')));
        $r->prependRoute('compte_visualisation', new CompteRoute('/compte/:identifiant/visualisation',
            array('module' => 'compte',
                'action' => 'visualisation'),
            array('sf_method' => array('get', 'post')),
            array('model' => 'Compte',
                'type' => 'object')));
        $r->prependRoute('compte_switch_statut', new CompteRoute('/compte/:identifiant/switchStatus',
            array('module' => 'compte',
                'action' => 'switchStatus'),
            array('sf_method' => array('get', 'post')),
            array('model' => 'Compte',
                'type' => 'object')));
        $r->prependRoute('compte_coordonnee_modification', new CompteRoute('/compte-coordonnee/:identifiant/modification',
            array('module' => 'compte',
                'action' => 'modificationCoordonnee'),
            array('sf_method' => array('get', 'post')),
            array('model' => 'Compte',
                'type' => 'object')));
    }

}
