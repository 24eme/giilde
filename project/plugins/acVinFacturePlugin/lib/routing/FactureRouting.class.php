<?php

class FactureRouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {

        $r = $event->getSubject();

//        $r->prependRoute('facture', new sfRoute('/facture', array('module' => 'facture',
//								  'action' => 'index')));
//
//
//
        $r->prependRoute('facture_societe', new SocieteRoute('/facture/:identifiant', array('module' => 'facture',
            'action' => 'monEspace'), array('sf_method' => array('get', 'post')), array('model' => 'Societe',
            'type' => 'object')
        ));

        $r->prependRoute('facture_creation', new SocieteRoute('/facture-creation/:identifiant', array('module' => 'facture',
            'action' => 'creation'), array('sf_method' => array('get', 'post')), array('model' => 'Societe',
            'type' => 'object')
        ));

        $r->prependRoute('facture_etablissement', new EtablissementRoute('/facture/etablissement/:identifiant', array('module' => 'facture',
            'action' => 'etablissement'), array('sf_method' => array('get', 'post')), array('model' => 'Etablissement',
            'type' => 'object')
        ));

        $r->prependRoute('facture_redirect_to_doc', new sfRoute('/facture/redirect/:iddocument', array('module' => 'facture', 'action' => 'redirect')));

        $r->prependRoute('facture_defacturer', new FactureRoute('/facture/:id/defacturer', array('module' => 'facture',
            'action' => 'defacturer'), array('sf_method' => array('get', 'post')), array('model' => 'Facture',
            'type' => 'object')
        ));
        $r->prependRoute('facture', new sfRoute('/facture', array('module' => 'facture',
            'action' => 'index')));

        $r->prependRoute('facture_generation', new sfRoute('/facture/generation', array('module' => 'facture',
            'action' => 'generation')));

        $r->prependRoute('facture_sous_generation', new sfRoute('/facture/generation/:generation/:type',
            array('module' => 'facture', 'action' => 'sousGenerationFacture')
        ));

        $r->prependRoute('facture_mouvements', new sfRoute('/facture_mouvements_list', array('module' => 'facture',
            'action' => 'mouvementsList')));

        $r->prependRoute('facture_mouvements_nouveaux', new sfRoute('/facture_mouvements_nouveaux', array('module' => 'facture',
            'action' => 'nouveauMouvements')));

        $r->prependRoute('facture_mouvements_edition', new sfRoute('/facture_mouvements/:id', array('module' => 'facture',
            'action' => 'mouvements-edition')));

        $r->prependRoute('facture_mouvements_supprimer', new sfRoute('/facture_mouvements_supprimer/:id', array('module' => 'facture',
            'action' => 'mouvements-supprimer')));

        $r->prependRoute('facture_pdf', new sfRoute('/facture/pdf/:id', array('module' => 'facture',
            'action' => 'latex'), array('sf_method' => array('get')), array('model' => 'Facture',
            'type' => 'object')
        ));

        $r->prependRoute('facture_pdf_auth', new sfRoute(
            '/facture/pdf/:id/:auth',
            array('module' => 'facture', 'action' => 'getFactureWithAuth'),
            array('sf_method' => array('get')),
            array('model' => 'Facture', 'type' => 'object')
        ));

        $r->prependRoute('comptabilite_edition', new sfRoute('/comptabilite-edition', array('module' => 'facture',
            'action' => 'comptabiliteEdition'), array('sf_method' => array('get', 'post')), array('model' => 'Comptabilite',
            'type' => 'object')
        ));
    }

}
