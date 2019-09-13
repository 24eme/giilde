<?php
$partial = ($drm->isNegoce())? 'xmlnegoce' : 'xml';
include_partial($partial, array('drm' => $drm));
