<?php

class acVinEtablissementRouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {
        $r = $event->getSubject();

        $r->prependRoute('etablissement_autocomplete_all', new sfRoute('/etablissement/autocomplete/:interpro_id/tous', array('module' => 'etablissement_autocomplete',
            'action' => 'all')));


        $r->prependRoute('etablissement_autocomplete_byfamilles', new sfRoute('/etablissement/autocomplete/:interpro_id/familles/:familles', array('module' => 'etablissement_autocomplete',
            'action' => 'byFamilles')));

        $r->prependRoute('etablissement_ajout', new SocieteRoute('/etablissement/:identifiant/nouveau', array('module' => 'etablissement',
            'action' => 'ajout'), array('sf_method' => array('get', 'post')), array('model' => 'Societe',
            'type' => 'object')));

        $r->prependRoute('etablissement_modification', new EtablissementRoute('/etablissement/:identifiant/modification', array('module' => 'etablissement',
            'action' => 'modification'), array('sf_method' => array('get', 'post')), array('model' => 'Etablissement',
            'type' => 'object')));

        $r->prependRoute('etablissement_visualisation', new EtablissementRoute('/etablissement/:identifiant/visualisation', array('module' => 'etablissement',
            'action' => 'visualisation'), array('sf_method' => array('get', 'post')), array('model' => 'Etablissement',
            'type' => 'object')));

        $r->prependRoute('etablissement_switch_statut', new EtablissementRoute('/etablissement/:identifiant/switchStatus', array('module' => 'etablissement',
            'action' => 'switchStatus'), array('sf_method' => array('get', 'post')), array('model' => 'Etablissement',
            'type' => 'object')
        ));

        $r->prependRoute('etablissement_reinit_crd', new EtablissementRoute('/etablissement/:identifiant/reinitCrd', array('module' => 'etablissement',
            'action' => 'reinitCrd'), array('sf_method' => array('get', 'post')), array('model' => 'Etablissement',
            'type' => 'object')
        ));
    }

}
