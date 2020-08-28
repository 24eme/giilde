<?php

class messagesActions extends sfActions
{

  public function executeModification(sfWebRequest $request)
  {
    $this->messageId = $request->getParameter('id');

  	$this->form = new MessageForm(MessagesClient::getInstance()->retrieveMessages(), $this->messageId);

  	if (!$request->isMethod(sfWebRequest::POST)) {

        return sfView::SUCCESS;
    }
    $this->form->bind($request->getParameter($this->form->getName()));

    if (!$this->form->isValid()) {

        return sfView::SUCCESS;
    }

    $this->form->save();
	$this->getUser()->setFlash("notice", 'Le message a été modifié avec succès.');

    return $this->redirect($this->generateUrl('produits')."#messages");
  }


}
