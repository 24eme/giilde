<?php

class CommonRouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {

        $r = $event->getSubject();
        if (sfConfig::get('sf_app') == 'edi') {
          return ;
        }
        $r->prependRoute('common_homepage', new sfRoute('/', array('module' => 'common', 'action' => 'home')));
        $r->prependRoute('common_accueil', new sfRoute('/accueil', array('module' => 'common', 'action' => 'accueil')));
        $r->prependRoute('common_accueil_etablissement', new sfRoute('/accueil/:identifiant', array('module' => 'common', 'action' => 'accueilEtablissement')));
    }
}
