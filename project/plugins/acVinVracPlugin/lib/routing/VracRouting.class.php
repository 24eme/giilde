<?php

class VracRouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {

        $r = $event->getSubject();

        $r->prependRoute('vrac', new sfRoute('/vrac', array('module' => 'vrac',
                                                            'action' => 'index')));
        $r->prependRoute('vrac_recherche',  new EtablissementRoute('/vrac/recherche/:identifiant', array('module' => 'vrac',
                                                                        'action' => 'recherche'),
                                                    array('sf_method' => array('get','post')),
                                                    array('model' => 'Etablissement',
                                                        'type' => 'object')
                                                        ));

        $r->prependRoute('vrac_etablissement_selection',  new sfRoute('/vrac/etablissement-selection',
                                                                    array('module' => 'vrac',
                                                                    'action' => 'etablissementSelection'),
                                                                    array('sf_method' => array('post'))
                                                        ));

        $r->prependRoute('vrac_debrayage',  new EtablissementRoute('/vrac/connexion/:identifiant', array('module' => 'vrac',
                                                                        'action' => 'connexion'),
                                                                array('sf_method' => array('get','post')),
                                                                array('model' => 'Etablissement',
                                                                    'type' => 'object')
                                                                    ));

        $r->prependRoute('vrac_exportCsv', new sfRoute('/vrac/exportCsv', array('module' => 'vrac',
                                                            'action' => 'exportCsv')));


        $r->prependRoute('vrac_nouveau', new sfRoute('/vrac/nouveau', array('module' => 'vrac',
                                                            'action' => 'nouveau')));
        $r->prependRoute('vrac_soussigne', new VracRoute('/vrac/:numero_contrat/soussigne',
                                                        array('module' => 'vrac','action' => 'soussigne'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));
        $r->prependRoute('vrac_marche', new VracRoute('/vrac/:numero_contrat/marche',
                                                        array('module' => 'vrac','action' => 'marche'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));
        $r->prependRoute('vrac_condition', new VracRoute('/vrac/:numero_contrat/condition',
                                                        array('module' => 'vrac','action' => 'condition'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));
        $r->prependRoute('vrac_validation', new VracRoute('/vrac/:numero_contrat/validation',
                                                        array('module' => 'vrac','action' => 'validation'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_visualisation', new VracRoute('/vrac/:numero_contrat/visualisation',
                                                        array('module' => 'vrac','action' => 'visualisation'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_soussigne_getinfos', new sfRoute('/vrac/soussigne/infos',
                                                        array('module' => 'vrac','action' => 'getInformations'),
                                                        array('sf_method' => array('get'))));


        $r->prependRoute('vrac_nouveau_modification', new VracRoute('/vrac/modification',
                                                        array('module' => 'vrac','action' => 'getModifications'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_soussigne_modification', new VracRoute('/vrac/:numero_contrat/modification',
                                                        array('module' => 'vrac','action' => 'getModifications'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_getContratsSimilaires', new VracRoute('/vrac/:numero_contrat/getContratsSimilaires',
                                                        array('module' => 'vrac','action' => 'getContratsSimilaires'),
                                                        array('sf_method' => array('get')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_volumeEnleve', new VracRoute('/vrac/:numero_contrat/volumeEnleve',
                                                        array('module' => 'vrac','action' => 'volumeEnleve'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_solder', new VracRoute('/vrac/:numero_contrat/solder',
                                                        array('module' => 'vrac','action' => 'changeStatut'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_nonsolder', new VracRoute('/vrac/:numero_contrat/nonsolder',
                                                        array('module' => 'vrac','action' => 'changeStatut'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_changecontratinterne', new VracRoute('/vrac/:numero_contrat/contratinterne',
                                                        array('module' => 'vrac','action' => 'changeContratInterne'),
                                                        array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_updateVolumeEnleve', new VracRoute('/vrac/:numero_contrat/calculeVolumeEnleve',
                                                        array('module' => 'vrac','action' => 'updateVolumeEnleve'),
                                                        array('sf_method' => array('get')),
                                                        array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_export_etiquette', new sfRoute('/vrac/export-etiquette', array('module' => 'vrac',
                                                                        'action' => 'exportEtiquette')));

        $r->prependRoute('vrac_redirect_to_visualisation', new sfRoute('/vrac/redirect/:identifiant_vrac',
                                                        array('module' => 'vrac', 'action' => 'redirect'),
                                                        array('sf_method' => array('get'))
                                                          ));
        $r->prependRoute('vrac_pdf', new VracRoute('/vrac/:numero_contrat/pdf',
                                                        array('module' => 'vrac', 'action' => 'latex'),
							array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')
                                                         ));

        $r->prependRoute('vrac_societe',  new sfRoute('/contrats/societe/:identifiant', array('module' => 'vrac',
                                                                                              'action' => 'societe')));

        $r->prependRoute('vrac_annuaire_commercial', new sfRoute('/contrats/annuaire/commercial/:identifiant/:createur',
                                                        array('module' => 'vrac', 'action' => 'annuaireCommercial'),
							array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')
                                                         ));
        $r->prependRoute('vrac_annuaire', new sfRoute('/contrats/annuaire/:acteur/:type/:identifiant/:createur',
                                                        array('module' => 'vrac', 'action' => 'annuaire'),
							array('sf_method' => array('get','post')),
                                                        array('model' => 'Vrac', 'type' => 'object')
                                                         ));

        $r->prependRoute('vrac_history',  new sfRoute('/contrats/historique/:identifiant', array('module' => 'vrac',
                                                                                              'action' => 'history')));

        $r->prependRoute('vrac_history_exportCsv', new sfRoute('/contrats/exportHistoriqueCsv/:identifiant', array('module' => 'vrac',
                                                            'action' => 'exportHistoriqueCsv')));

        $r->prependRoute('vrac_signature',  new VracRoute('/contrats/:numero_contrat/signature', array('module' => 'vrac',
                                                                                              'action' => 'signature'),
                                                                                            array('sf_method' => array('get','post')),
                                                                                            array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_redirect_saisie',  new VracRoute('/contrats/:numero_contrat/saisie', array('module' => 'vrac',
                                                                                              'action' => 'redirectSaisie'),
                                                                                            array('sf_method' => array('get','post')),
                                                                                            array('model' => 'Vrac', 'type' => 'object')));

        $r->prependRoute('vrac_societe_choix_etablissement',  new sfRoute('/contrats/:identifiant/choix-etablissement', array('module' => 'vrac',
                                                                                              'action' => 'choixEtablissement'),
                                                                                            array('sf_method' => array('get','post'))));

        $r->prependRoute('vrac_notice',  new sfRoute('/contrats/:type/notice', array('module' => 'vrac', 'action' => 'notice'),
                                                                            array('sf_method' => array('get'))));

        $r->prependRoute('vrac_supprimer_brouillon',  new VracRoute('/contrats/:numero_contrat/supprimer', array('module' => 'vrac',
                                                                                              'action' => 'suppressBrouillon'),
                                                                                            array('sf_method' => array('get','post')),
                                                                                            array('model' => 'Vrac', 'type' => 'object')));


    }

}
