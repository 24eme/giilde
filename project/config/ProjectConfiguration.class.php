<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
    protected static $routing = null;

  	public function setup()
  	{
        $this->enablePlugins('AppPlugin');
        $this->enablePlugins('acLessphpPlugin');
	    $this->enablePlugins('acCouchdbPlugin');
	    $this->enablePlugins('acVinImportPlugin');
        $this->enablePlugins('acVinLibPlugin');
	    $this->enablePlugins('acVinVracPlugin');
	    $this->enablePlugins('acVinDRMPlugin');
        $this->enablePlugins('acVinEtablissementPlugin');
	    $this->enablePlugins('acVinConfigurationPlugin');
	    $this->enablePlugins('InterproPlugin');
	    $this->enablePlugins('ImportPlugin');
	    $this->enablePlugins('acVinFacturePlugin');
        $this->enablePlugins('acVinSV12Plugin');
        $this->enablePlugins('acVinGenerationPlugin');
        $this->enablePlugins('acVinDocumentPlugin');
        $this->enablePlugins('acVinDSPlugin');
        $this->enablePlugins('acVinDAEPlugin');
        $this->enablePlugins('acVinRevendicationPlugin');
        $this->enablePlugins('acVinAlertePlugin');
        $this->enablePlugins('acVinSocietePlugin');
        $this->enablePlugins('acVinStocksPlugin');
        $this->enablePlugins('acVinComptePlugin');
        $this->enablePlugins('acElasticaPlugin');
        $this->enablePlugins('acVinRelancePlugin');
        $this->enablePlugins('acLdapPlugin');
        $this->enablePlugins('acExceptionNotifierPlugin');
        $this->enablePlugins('acVinAnnuairePlugin');
        $this->enablePlugins('acCASPlugin');
        $this->enablePlugins('acElasticaPlugin');
        $this->enablePlugins('StatistiquePlugin');

        $this->dispatcher->connect('application.throw_exception', array('acError500', 'handleException'));
  	}

    public static function getAppRouting()
    {
        if (null !== self::$routing) {
            return self::$routing;
        }
        if (sfContext::hasInstance() && sfContext::getInstance()->getRouting()) {
            self::$routing = sfContext::getInstance()->getRouting();
        } else {
            if (!self::hasActive()) {
                throw new sfException('No sfApplicationConfiguration loaded');
            }
            $appConfig = self::getActive();
            $config = sfFactoryConfigHandler::getConfiguration($appConfig->getConfigPaths('config/factories.yml'));
            $params = array_merge($config['routing']['param'], array('load_configuration' => false,
                                                                     'logging'            => false,
                                                                     'context'            => array('host'      => sfConfig::get('app_routing_context_production_host', 'localhost'),
                                                                                                   'prefix'    => sfConfig::get('app_prefix', sfConfig::get('sf_no_script_name') ? '' : '/'.$appConfig->getApplication().'_'.$appConfig->getEnvironment().'.php'),
                                                                                                   'is_secure' => sfConfig::get('app_routing_context_secure', false))));
            $handler = new sfRoutingConfigHandler();
            $routes = $handler->evaluate($appConfig->getConfigPaths('config/routing.yml'));
            $routeClass = $config['routing']['class'];
            self::$routing = new $routeClass($appConfig->getEventDispatcher(), null, $params);
            self::$routing->setRoutes($routes);
        }
        return self::$routing;
    }
}
