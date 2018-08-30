<?php
$dataGlobal = $drm->declaration->getProduitsDetailsAggregateByAppellation(true, 'details');
$data = array();

if(isset($dataGlobal['/declaration/certifications/AOC_ALSACE'])) {
    $data['/declaration/certifications/AOC_ALSACE'] = $dataGlobal['/declaration/certifications/AOC_ALSACE'];
}

include_partial('drm_pdf/generateRecapMvtTex', array('drm' => $drm,'drmLatex' => $drmLatex, 'detailsNodes' => 'details', "libelleDetail" => null, 'data' => $data, 'tabTitle' => 'Récapitulatif')); ?>
