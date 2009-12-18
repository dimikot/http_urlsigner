<?php
/**
 * Tool to create and parse digitally signed URLs with a number of
 * secured parameters passed.
 * 
 * Usage sample (URL builder): 
 *   $signer = new HTTP_UrlSigner("my_secret", "http://example.com/img/*");
 *   echo '<img src="' . $signer->buildUrl(array('w' => 10, 'h' => 10)) . '" />';
 *
 * Usage sample (URL parser):
 *   $signer = new HTTP_UrlSigner("my_secret", "http://example.com/img/*");
 *   $params = $signer->parseUrl($_SERVER['REQUEST_URI']); 
 * 
 * This sample guarantees that we get the same $params content than we
 * packed at the builder stage, and nobody else could create such URLs
 * (if he does not know the secret, of course).
 * 
 * Base URL (passed to the constructor) may be relative or absolute.
 * URL to be parsed may also be passed as relative or absolute.
 * 
 * @version 1.00
 */
class HTTP_UrlSigner
{
    /**
     * Replacements to convert base64 encoded string to safe URL.
     * 
     * @var array
     */
    private $_safeBase64 = array(array('+', '/', "="), array('_', '~', '-'));
	
    /**
     * Secret code to make digital signature.
     *
     * @var string
     */
	private $_secret;

	/**
	 * http://example.com/some/a_*_/path?abc
	 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	 *
	 * @var string
	 */
	private $_mask;
	
	/**
	 * http://example.com/some/a_*_/path?abc
	 * ^^^^^^^^^^^^^^^^^^^^^^^^^^
	 *
	 * @var string
	 */
	private $_maskPrefixFull = null;

	/**
	 * http://example.com/some/a_*_/path?abc
	 *                   ^^^^^^^^
	 *
	 * @var string
	 */
	private $_maskPrefix = null;
	
	/**
	 * http://example.com/some/a_*_/path?abc
	 *                            ^^^^^^^^^^
	 *
	 * @var string
	 */
	private $_maskSuffix = null;
	
	/**
	 * Create new ImageResizer object.
	 *
	 * @param string $secret
	 * @param string $baseUrl
	 */
	public function __construct($secret, $urlMask)
	{
		$this->_secret = $secret;
		$this->_mask = $urlMask;
		list ($this->_maskPrefixFull, $this->_maskPrefix, , $this->_maskSuffix) = $this->_splitUrl($this->_mask);
	}
	
	/**
	 * Builds URL with data is token mixed in.
	 *
	 * @param array $params
	 * @return string
	 */
	public function buildUrl(array $params)
	{
		$token = http_build_query($params);
		if (function_exists('gzdeflate')) {
			$deflated = gzdeflate($token);
			if ($this->_strlen($deflated) < $this->_strlen($token)) {
				$token = "z" . $deflated;
			} else {
				$token = "a" . $token;
			}
		} else {
			$token = "a" . $token;
		}
        $token = base64_encode($token);
        $token = str_replace($this->_safeBase64[0], $this->_safeBase64[1], $token);
        $token = join("/", str_split($token, 80));
        // Add digital signature to the end of the PACKED result. We cannot insert
        // the signature before packing, because else a hacked may create another 
        // pack which unpacks to the same result. Add signatures at the beginning,
        // because we need an easy way to explode() them back.
        $token = 
        	$this->_hash($this->_secret . $this->_maskPrefixFull . $token . $this->_maskSuffix) . 
			"/" .
			$this->_hash($this->_secret . $this->_maskPrefix . $token . $this->_maskSuffix) .
			"/" .
			$token;
		return str_replace("*", $token, $this->_mask);
	}

	/**
	 * Parses passed URL and return extracted data items.
	 *
	 * @param string $url
	 * @return array
	 */
	public function parseUrl($url)
	{
		list ($prefixFull, $prefix, $token, $suffix) = $this->_splitUrl($url);
		@list ($signFull, $sign, $token) = explode("/", $token, 3);
		if ($this->_maskPrefixFull === $this->_maskPrefix) {
			// Base URL is relative => compare relative signatures only.
			$ok = $this->_hash($this->_secret . $prefix . $token . $suffix) === $sign;
		} else if ($prefixFull !== $prefix) {
			// Checked URL is absolute => compare absolute signatures [we know that base URL is absolute].
			$ok = $this->_hash($this->_secret . $prefixFull . $token . $suffix) === $signFull;
		} else {
			// Checked URL is relative [we know that base URL is absolute].
			$ok = $this->_hash($this->_secret . $prefix . $token . $suffix) === $sign;
		}
		if (!$ok) {
			throw new Exception("Wrong digital signature");
		}		
		$token = str_replace('/', '', $token);
		$token = str_replace($this->_safeBase64[1], $this->_safeBase64[0], $token);
		$token = @base64_decode($token);
		if (!$token) {
			throw new Exception("Invalid URL token encoding");
		}
		if (@$token[0] == "z") {
			$token = gzinflate($this->_substr($token, 1));
		} else {
			$token = $this->_substr($token, 1);
		}
		$params = null;
		parse_str($token, $params);
		return $params;
	}
	
	protected function _getUriByUrl($url)
	{
		$m = null;
		if (preg_match('{^\w+://[^/?]+(.*)$}s', $url, $m)) {
			return $m[1];
		}
		return $url;
	}
	
    protected function _splitUrl($url)
    {
    	if ($this->_maskPrefix === null) {
			$m = null;
			if (!preg_match('/^(.*)\*(.*)$/', $this->_mask, $m)) {
				throw new Exception("URL mask must contain \"*\", {$this->_mask} given");
			}
			$this->_maskPrefixFull = $m[1];
			$this->_maskPrefix = $this->_getUriByUrl($this->_maskPrefixFull);
			$this->_maskSuffix = $m[2];
    	}
    	// ^(http://example.com(/some/a_)) (*) (_/path?abc)$
    	if ($this->_maskPrefix === $this->_maskPrefixFull) {
    		$url = $this->_getUriByUrl($url);
    	}
		$re = '^(' . 
			(preg_match('{^\w+://}s', $url)? preg_quote($this->_substr($this->_maskPrefixFull, 0, -$this->_strlen($this->_maskPrefix)), '/') : "") . 
			"(" . preg_quote($this->_maskPrefix, '/') . ")" .
			")(.+)(" . preg_quote($this->_maskSuffix, '/') . ')$';
		if (!preg_match("/$re/s", $url, $m)) {
			throw new Exception("URL does not match the mask \"{$this->_mask}\"");
		}
		return array($m[1], $m[2], $m[3], $m[4]);
    }
    
    protected function _hash($data)
    {
    	return md5($this->_secret . $data); 
    }
    
    private function _strlen($s)
    {
    	return function_exists('mb_orig_strlen')? mb_orig_strlen($s) : strlen($s);
    }
    
    private function _substr($s, $from, $len = null)
    {
    	if ($len !== null) {
    		return function_exists('mb_orig_substr')? mb_orig_substr($s, $from, $len) : substr($s, $from, $len);
    	} else {
    		return function_exists('mb_orig_substr')? mb_orig_substr($s, $from) : substr($s, $from);
    	}
    }    
}
