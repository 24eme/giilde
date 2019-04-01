<?php
use_helper('IvbdStatistique');

$stocksfinResult = $stocksfin->getRawValue();
$stockProduits = $stocksfinResult["disponibilites_stocks"]["agg_page"]["buckets"];


$vracsfinResult = $contrats->getRawValue();
$vracProduits = $vracsfinResult["disponibilites_vracs"]["agg_page"]["buckets"];
$csv =  "Produit;Stock fin de mois;Volume engagé;Diponibilité marché\n";

foreach ($vracProduits as $produivractKey => $vracProduit) {
  foreach ($stockProduits as $produitstockKey => $stockProduit) {
    if(strpos($vracProduit["key"], $stockProduit["key"]) !== false){
      foreach ($stockProduit["agg_line"]["buckets"] as $stock) {
        $s = $stock["stock_final"]["agg_column"]["value"];
        $v = ($vracProduit["volume_non_enleve"]["value"] < 0)? "0" : $vracProduit["volume_non_enleve"]["value"];
        $d = $s - $v;
        $csv.= ConfigurationClient::getCurrent()->get($stockProduit["key"])->getLibelleFormat().";".formatNumber($s).";".formatNumber($v).";".formatNumber($d)."\n";
      }
    }
  }
}
echo $csv;
?>
