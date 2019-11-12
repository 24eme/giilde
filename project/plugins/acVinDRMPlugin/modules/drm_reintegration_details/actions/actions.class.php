<?php

class drm_reintegration_detailsActions extends drmGeneriqueActions
{

    public function executeProduit(sfWebRequest $request) {

        return $this->processProduitDetails($request, "DRMDetailReintegrationForm");
    }
}
