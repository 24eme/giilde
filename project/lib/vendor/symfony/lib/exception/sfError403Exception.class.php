<?php

class sfError403Exception extends sfException
{
  /**
   * Forwards to the 403 action.
   */
  public function printStackTrace()
  {

    if (sfConfig::get('sf_debug'))
    {
      $response = sfContext::getInstance()->getResponse();
      if (null === $response)
      {
        $response = new sfWebResponse(sfContext::getInstance()->getEventDispatcher());
        sfContext::getInstance()->setResponse($response);
      }

      $response->setStatusCode(403);

      return parent::printStackTrace();
    }
    else
    {
      // log all exceptions in php log
      if (!sfConfig::get('sf_test'))
      {
        error_log($this->getMessage());
      }

      sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
    }
  }
}
