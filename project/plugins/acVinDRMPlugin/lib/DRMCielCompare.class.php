<?php
class DRMCielCompare
{
	protected $xmlIn;
	protected $xmlOut;

	public function __construct($xmlIn, $xmlOut)
	{
		if(is_string($xmlIn)) {
			$this->xmlIn = simplexml_load_string($xmlIn);
		}else {
			$this->xmlIn = $xmlIn;
		}
		if(is_string($xmlOut)) {
			$this->xmlOut = simplexml_load_string($xmlOut);
		}else {
			$this->xmlOut = $xmlOut;
		}
	}

	public function sortAndPurgeNull($array){
		$nonnull = array();
		foreach ($array as $key => $value) {
			if(!is_null($value)){
				$nonnull[$key] = $value;
			}
		}
		ksort($nonnull);
		$produits = array();
		$res = array();
		foreach($nonnull as $key => $value) {
			if (!preg_match('/produit/', $key)) {
				$res[$key] = $value;
				continue;
			}
			if (preg_match('/produit\/\{array\}\/([A-Z0-9][^\/]+)\/\{array\}\/[a-z]/', $key, $m)) {
				$idproduit = $m[1];
			}else{
				$idproduit = 'produit unique';
			}
			if(!array_key_exists($idproduit,$produits)){
				$produits[$idproduit] = array();
			}
			$produits[$idproduit][$key] = $value;
		}
		foreach ($produits as $key => $produitArr) {
			$somme_balance = 0;
			foreach ($produitArr as $key_r => $value) {
				if (preg_match('/balance-stocks/', $key_r)) {
					$somme_balance += $value * 1;
				 	}
			}
			if ($somme_balance) {
		 			$res = array_merge($res, $produitArr);
		 		}
		}
		return $res;
	}

	public function getDiff()
	{

		$arrIn = $this->sortAndPurgeNull($this->identifyKey($this->flattenArray($this->xmlToArray($this->xmlIn))));
		$arrOut = $this->sortAndPurgeNull($this->identifyKey($this->flattenArray($this->xmlToArray($this->xmlOut))));

		$diff = array();
		foreach ($arrIn as $key => $value) {
			if (!isset($arrOut[$key]) && $value) {
				$diff[$key] = array($value, null);
			}
			if (isset($arrOut[$key]) && $arrOut[$key] != $value) {
				$diff[$key] = array($value, $arrOut[$key]);
			}
		}
		foreach ($arrOut as $key => $value) {
			if (!isset($arrIn[$key]) && $value) {
				$diff[$key] = array(null, $value);
			}
		}

		return $diff;
	}

	public function hasDiff()
	{
		return (count($this->getDiff()) > 0)? true : false;
	}

	private function xmlToArray($xml)
	{
		return json_decode(json_encode((array)$xml), true);
	}

	private function flattenArray($array)
	{
		return acCouchdbToolsJson::json2FlatenArray($array, null, '_');
	}

	private function identifyKey($array)
	{
		$patternProduit = '/\/produit\/\{array\}\/([0-9]+)\/\{array\}\//i';
		$patternCrd = '/\/compte-crd\/\{array\}\/([0-9]+)\/\{array\}\//i';
		$patternCentilisation = '/\/centilisation\/\{array\}\/([0-9]+)\/\{array\}\//i';
		$newKeyProduit = '';
		$newKeyCrd = '';
		$newKeyCentilisation = '';
		$result = array();
		foreach ($array as $key => $value) {
			if (preg_match('/\/produit\//i', $key) || preg_match('/\/compte-crd\//i', $key) || preg_match('/\/centilisation\//i', $key)) {
				if (preg_match('/\/observations/i', $key)) {
					continue;
				}
				if (preg_match($patternProduit, $key) && (preg_match('/code-inao/i', $key) || preg_match('/libelle-fiscal/i', $key))) {
					$newKeyProduit = $value;
					continue;
				}
				if (preg_match($patternProduit, $key) && preg_match('/libelle-personnalise/i', $key)) {
					$newKeyProduit .= '_'.KeyInflector::slugifyCaseSensitive($value);
					continue;
				}
				if (preg_match($patternCrd, $key) && preg_match('/categorie-fiscale-capsules/i', $key)) {
					$newKeyCrd = $value;
					continue;
				}
				if (preg_match($patternCrd, $key) && preg_match('/type-capsule/i', $key)) {
					$newKeyCrd .= '_'.$value;
					continue;
				}
				if (preg_match($patternCentilisation, $key) && preg_match('/@attributes/i', $key)) {
					if(preg_match('/\/volume$/i', $key)){
						$newKeyCentilisation = $value;
					}
					if(preg_match('/\/volumePersonnalise/i', $key)){
						$newKeyCentilisation .= $value;
					}
					continue;
				}
				$value = $this->cleanValue($value);
				if (preg_match($patternProduit, $key)) {
					$tmp = preg_replace($patternProduit, '/produit/{array}/'.$newKeyProduit.'/{array}/', $key);
					$result[$tmp] = $value;
				}elseif (preg_match($patternCentilisation, $key)){
				  if($value !== 0){
						 $tmp = preg_replace($patternCrd, '/compte-crd/{array}/'.$newKeyCrd.'/{array}/', $key);
						 $tmp = preg_replace($patternCentilisation, '/centilisation/{array}/'.$newKeyCentilisation.'/{array}/', $tmp);

						 $result[$tmp] = $value;
					 }
				} elseif (preg_match($patternCrd, $key)){
						$tmp = preg_replace($patternCrd, '/compte-crd/{array}/'.$newKeyCrd.'/{array}/', $key);
						$result[$tmp] = $value;
				} else {
						$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	private function cleanValue($value)
	{
		if ($value == "false") {
			return 0;
		}
		if ($value == "true") {
			return 1;
		}
		if (is_numeric($value)) {
			return $value * 1;
		}
		return $value;
	}


	    public function getFormattedXMLComparaison() {
	      $str_arr = array();
	      foreach ($this->getDiff() as $key => $values) {
	        $keyArr = explode("/",$key);
	        if(strpos($key,"{array}/produit/{array}")){
	          $probleme = "[Problème de ".((isset($keyArr[7]))? str_replace("-"," ",$keyArr[7]) : "???")." en ".str_replace("-"," ",$keyArr[1])."]";
	          $produit = "".str_replace("_"," ",str_replace("-"," ",$keyArr[5]));
	          $catMvt = (isset($keyArr[9]))? "".str_replace(array("-periode"),array(""),$keyArr[9]) : "";
						$mvt = (isset($keyArr[11]))? " ".str_replace("-"," ",$keyArr[11]) : '';
	          $str_arr[$probleme." ".$produit." ".$catMvt.$mvt] = $values;
	        }elseif(strpos($key,"{array}/compte-crd/{array}")){
						$keyArr = explode("/",$key);
	          $probleme = "[Problème de CRD ".str_replace(array("T_PERSONNALISEES","M_PERSONNALISEES"),array("TRANQ","MOUSSEUX"),$keyArr[3])."]";
	          $origine = (isset($keyArr[5]))? ucfirst($keyArr[5]) : "";
						$origine .= (isset($keyArr[7]))? " ".str_replace(array("-capsules"),array(""),$keyArr[7]) : '';

	          $mvt = (isset($keyArr[9]))? " ".str_replace(array("-capsules"),array(""),$keyArr[9]) : '';
						$mvt .= (isset($keyArr[11]))? " ".$keyArr[11] : '';
	          $str_arr[$probleme." ".$origine.$mvt] = $values;
	        }else{
	          $str_arr[$key] = $values;
	        }

	      }
		  krsort($str_arr);
	      return $str_arr;
	    }


		public function xmlInToArray()
		{
			$properFlattenXml = array();
			$flattenXml = $this->flattenArray($this->xmlToArray($this->xmlIn));
			foreach ($flattenXml as $key => $value) {
				if(preg_match("/_declaration-recapitulative_{array}\//",$key)){
					$properFlattenXml[str_ireplace(array("_declaration-recapitulative_{array}/","/{array}/"),array("","/"),$key)] = $value;
				}
			}
			return $properFlattenXml;
		}
}
