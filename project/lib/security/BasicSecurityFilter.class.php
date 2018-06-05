<?php

class BasicSecurityFilter extends sfBasicSecurityFilter
{
    public function execute ($filterChain)
    {
        if(!sfConfig::get('app_force_usurpation_mode', false)) {

            return parent::execute($filterChain);
        }

        $user = $this->context->getUser();

        if(!$user->hasCredential(AppUser::CREDENTIAL_ADMIN) && !$user->isUsurpationCompte()) {

            return parent::execute($filterChain);
        }

        $request = $this->context->getRequest();

        $user->usurpationOff();
        
        $compte = null;
        if($request->getAttribute('sf_route') instanceof InterfaceEtablissementRoute) {
            $compte = $request->getAttribute('sf_route')->getEtablissement()->getMasterCompte()->identifiant;
        }
        if(!$compte && $request->getAttribute('sf_route') instanceof InterfaceSocieteRoute) {
            $compte =$request->getAttribute('sf_route')->getSociete()->getMasterCompte()->identifiant;
        }
        if($compte && $user->hasCredential(AppUser::CREDENTIAL_ADMIN)) {
            $user->usurpationOn($compte, null);
        }

        parent::execute($filterChain);
    }
}
