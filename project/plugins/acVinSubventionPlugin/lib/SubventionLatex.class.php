<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMLatex
 *
 * @author mathurin
 */
class SubventionLatex extends GenericLatex {

    private $subvention = null;
    private $approbationMode = false;

    function __construct(Subvention $subvention, $config = null) {
        sfProjectConfiguration::getActive()->loadHelpers("Partial", "Url", "MyHelper");
        $this->subvention = $subvention;
    }

    public function getLatexFileNameWithoutExtention() {
        return $this->getTEXWorkingDir() . $this->subvention->_id . '_' . $this->subvention->_rev;
    }

    public function getLatexFileContents() {
        return html_entity_decode(htmlspecialchars_decode(get_partial('subvention/generateTex', array('subvention' => $this->subvention, 'subventionLatex' => $this)), HTML_ENTITIES));
    }

    public function setApprobationMode($approbationMode) {
        $this->approbationMode = $approbationMode;
    }

    public function getPublicFileName($extention = '.pdf') {
        return 'subvention_' . $this->subvention->_id . '_' . $this->subvention->_rev . '_' . (int)$this->approbationMode . $extention;
    }

}
