<?php
/**
 * HTTP Authentication connected to AD filter
 * @author Tangui Morlier <tmorlier@actualys.com>
 * Inspired by James McGlinn <james@mcglinn.org>
 *
 */
class AutoAdminFilter extends BasicSecurityFilter
{
  /**
   * Execute filter
   *
   * @param sfFilterChain $filterChain
   */
  public function execute ($filterChain)
  {
    $context = $this->getContext();
    $user = $context->getUser();
    if ($user->isAuthenticated())
	return parent::execute($filterChain);


    $user->clearCredentials();
    $user->setAuthenticated(true);

    $rights = 'admin';

    $user->setAttribute('AUTH_USER', $rights);
    $user->setAttribute('AUTH_DESC', $rights);
    $user->signInOrigin($this->getCompte($user->getAttribute('AUTH_USER'), $user->getAttribute('AUTH_DESC')));
    parent::execute($filterChain);
  }

  public function getCompte($identifiant, $right) {
    $compte = new Compte();

    $compte->_id = "COMPTE-".$identifiant;
    $compte->identifiant = $identifiant;

    $compte->add("droits", array($right, Roles::OPERATEUR));

    return $compte;
  }


  /**
   * Sends HTTP Auth headers and exits
   *
   */
  public static function logout ($dest = null)
  {
    return ;
  }
}
