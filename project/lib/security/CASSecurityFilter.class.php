<?php
/**
 * HTTP Authentication connected to AD filter
 * @author Tangui Morlier <tmorlier@actualys.com>
 * Inspired by James McGlinn <james@mcglinn.org>
 *
 */
class CASSecurityFilter extends BasicSecurityFilter
{

  protected $request = null;
  protected $routing = null;
  protected $controller = null;

  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->request = $context->getRequest();
    $this->routing = $context->getRouting();
    $this->controller = $context->getController();
  }
  /**
   * Execute filter
   *
   * @param sfFilterChain $filterChain
   */
  public function execute ($filterChain)
  {
      if (!$this->context->getUser()->isAuthenticated() && ($this->request->getParameter('ticket') || isset($_SESSION["phpCAS"]["user"]))) {
          acCas::processAuth();
          $this->getContext()->getUser()->signInOrigin(acCas::getUser());
      }

      parent::execute($filterChain);
  }

  protected function forwardToLoginAction()
  {
      $this->controller->redirect(sfConfig::get('app_cas_url') . '/login?service=' . $this->request->getUri());

      throw new sfStopException();
  }

}
