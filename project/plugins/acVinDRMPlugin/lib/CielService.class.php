<?php
class CielService
{
	private static $_instance = null;
	protected $configuration;
	const TOKEN_CACHE_FILENAME = 'ciel_access_token';
	const TOKEN_TIME_VALIDITY = 2700;

  	public static function hasAppConfig() {
		return (sfConfig::get('app_ciel_oauth'));
	}

	public function __construct()
	{
		$this->configuration = sfConfig::get('app_ciel_oauth');
		if (!$this->configuration) {
			throw new sfException('CielService Error : Yml configuration not found for CIEL');
		}
	}
	
	public static function getInstance()
    {
       	if(is_null(self::$_instance)) {
       		self::$_instance = new CielService();
		}
		return self::$_instance;
    }
    
    public function getToken()
    {
    	if ($this->needNewToken()) {
    		$token = $this->sign();
    		$this->setTokenCache($token);
    	} else {
    		$file = $this->getTokenCacheFilename();
    		$token = file_get_contents($file);
    		if ($token === false) {
    			throw new sfException('CielService Error : cannot read '.$this->getTokenCacheFilename());
    		}
    	}
    	return $token;
    }
	
	public function sign()
	{
		$encrypted = '';
		$key = openssl_pkey_get_private('file://'.$this->configuration['keypath'], $this->configuration['keypass']);
		if (!$key) {
			throw new sfException('CielService Error : Openssl get private key failed : '.openssl_error_string());
		}
		$datas = $this->getDatas();
		if (!openssl_sign($datas, $encrypted, $key, 'SHA256')) {
			throw new sfException('CielService Error : '.openssl_error_string());
		}
		$oauth = $datas.'.'.$this->base64SafeEncode($encrypted);
		$data = array('grant_type'=> 'urn:ietf:params:oauth:grant-type:jwt-bearer' , 'assertion' => $oauth);
		$result = json_decode($this->httpQuerry($this->configuration['urltoken'], array('http' => $this->getOauthHttpRequest($data))), true);
		if (isset($result['error'])) {
			throw new sfException('CielService Error : '.$result['error'].' : '.$result['message']);
		}
		if (!isset($result['access_token'])) {
			throw new sfException('CielService Error : '.json_encode($result));
		}
		return $result['access_token'];
	}

	public function transfer($xml = null, $token = null)
	{
		if (!$token) {
			$token = $this->getToken();
		}
		$result = $this->httpQuerry($this->configuration['urlapp'], array('http' => $this->getTransferHttpRequest($token, $xml)));
		return $result;
	}

	public function storeXmlAsAttachement($drm, $xml) {
			$tmp = tempnam('/tmp', 'attachement');
			file_put_contents($tmp, $xml);
			$drm->storeAttachment($tmp, 'text/xml', 'drm_transmise.xml');
			unlink($tmp);
	}

	public function transferAndStore($drm, $xml, $token = null) {
		$cielResponse = "";
		try {
			$cielResponse = $this->transfer($xml, $token);
		} catch (sfException $e) {
			$cielResponse = $e->getMessage();
		}
		$this->storeXmlAsAttachement($drm, $xml);
		$drm->add('transmission_douane')->add('xml', $cielResponse);
		$drm->add('transmission_douane')->add('success', false);
		
		if (preg_match('/identifiant-declaration>([^<]*)<.*horodatage-depot>([^<]+)</', $cielResponse, $m)) {
			$drm->add('transmission_douane')->add('success', true);
			$drm->add('transmission_douane')->add('horodatage', $m[2]);
			$drm->add('transmission_douane')->add('id_declaration', $m[1]);
		}
		$drm->updateControles();
		
		$drm->save();
	}

	protected function needNewToken()
	{
		$file = $this->getTokenCacheFilename();
		if (file_exists($file)) {
			$timestamp = filemtime($file);
			if (($timestamp + self::TOKEN_TIME_VALIDITY) >= time()) {
				return false;
			}
		}
		return true;
	}

	protected function setTokenCache($token)
	{
		$file = $this->getTokenCacheFilename();
		$result = file_put_contents($file, $token, LOCK_EX);
		if ($result === false) {
			throw new sfException('CielService Error : cannot write in '.$file);
		}
	}
	
	protected function getTokenCacheFilename()
	{
		return sfConfig::get('sf_cache_dir').'/'.self::TOKEN_CACHE_FILENAME;
	}
	
	protected function getOauthHttpRequest($content)
	{
		return array(
				'headers'  => array(
						"Host: ".$this->configuration['host'],
						"Content-Type: application/x-www-form-urlencoded"),
				'method'  => 'POST',
				'protocol_version' => 1.1,
				'ignore_errors' => true,
				'content' => http_build_query($content));
	}

	protected function getTransferHttpRequest($token, $xml = null)
	{
		return array(
				'headers'  => array(
						"Host: ".$this->configuration['host'],
						"Content-Type: application/xml;charset=UTF-8",
						"Authorization: Bearer $token"),
				'method'  => 'POST',
				'protocol_version' => 1.1,
				'ignore_errors' => true,
				'content' => $xml);
	}

	protected function httpQuerry($url, $options)
	{
		if (extension_loaded('curl')) {
			return $this->httpQuerryCurl($url, $options);
		}
		return $this->httpQuerryFgc($url, $options);
	}

	protected function httpQuerryFgc($url, $options)
	{
		if (isset($options['http']['headers'])) {
			$options['http']['header'] = join('\n', $options['http']['header']);
			unset($options['http']['headers']);
		}
		$context  = stream_context_create($options);
		return file_get_contents($url, false, $context);
	}

	protected function httpQuerryCurl($url, $options)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if (isset($options['http']['content']) || (isset($options['http']['method']) && $options['http']['method'] == 'POST')) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['http']['content']);
		}
		if (isset($options['http']['headers'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $options['http']['headers']);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		if ($httpCode < 200 || $httpCode >= 300 ) {
			throw new sfException('HTTP Error '.$httpCode.' : '.$server_output);
		}
		return $server_output;
	}
	
	protected function getDatas()
	{
		$entete = '{"alg":"RS256"}';
		$corps = '{"iss":"'.$this->configuration['iss'].'","scope":"'.$this->configuration['service'].'","aud":"'.$this->configuration['url'].'","iat":'.time().'000}';
		return $this->base64SafeEncode($entete).'.'.$this->base64SafeEncode($corps);
	}
	
	protected function base64SafeEncode($input)
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}
}
