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
        $r->prependRoute('dae', new sfRoute('/dae', array('module' => 'dae',
                    'action' => 'index')));

        $r->prependRoute('dae_etablissement', new EtablissementRoute('/dae/:identifiant', array('module' => 'dae',
                    'action' => 'monEspace'),
                        array('sf_method' => array('get', 'post')),
                        array('model' => 'Etablissement',
                            'type' => 'object')));

        $r->prependRoute('dae_nouveau', new EtablissementRoute('/dae/:identifiant/nouveau', array('module' => 'dae',
            'action' => 'nouveau'), array('sf_method' => array('get', 'post')), array('model' => 'Etablissement',
            'type' => 'object')));

        $r->prependRoute('dae_export', new EtablissementRoute('/dae/:identifiant/export', array('module' => 'dae',
                    'action' => 'export'),
                        array('sf_method' => array('get', 'post')),
                        array('model' => 'Etablissement',
                            'type' => 'object')));

        

    }
}
