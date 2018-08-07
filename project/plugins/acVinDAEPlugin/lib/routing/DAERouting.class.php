<?php

class DAERouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {

        $r = $event->getSubject();
        $r->prependRoute('dae', new sfRoute('/ds', array('module' => 'dae',
                    'action' => 'index')));

        $r->prependRoute('dae_etablissement', new EtablissementRoute('/dae/:identifiant', array('module' => 'ds',
                    'action' => 'monEspace'),
                        array('sf_method' => array('get', 'post')),
                        array('model' => 'Etablissement',
                            'type' => 'object')));

        

    }
}
