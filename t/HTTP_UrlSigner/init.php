<?php
header("Content-type: text/plain");
chdir(dirname(__FILE__));
include_once "../../lib/config.php";
include_once "HTTP/UrlSigner.php";

class HTTP_UrlSigner_Stub extends HTTP_UrlSigner
{
	public function callSplitUrl($url)
	{
		try {
			$result = $this->_splitUrl($url);
		} catch (Exception $e) {
			$result = $e->getMessage();
		}
		echo "splitUrl: $url -> " . (is_array($result)? "( " . join(" | ", $result) . " )" : $result) . "\n";
	}

	public function callGetUriByUrl($url)
	{
		$result = $this->_getUriByUrl($url);
		echo "getUriByUrl: $url -> $result\n";
	}
	
	public function callBuildParse($params, $passRelativeUrl = false, $passSuffix = '', $mangleToken = '')
	{
		try {
			$url = $this->buildUrl($params);
			if ($passRelativeUrl) $url = $this->_getUriByUrl($url);
			$url .= $passSuffix;
			$url = preg_replace('/(?=\?)/s', $mangleToken, $url);
//			echo "$url\n";
			$parsed = $this->parseUrl($url);
			echo http_build_query($parsed) === http_build_query($params)? "matched" : "not matched";
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		if ($passRelativeUrl) echo " (passed relative URL)";
		if ($passSuffix) echo " (with additional suffix '$passSuffix')";
		if ($mangleToken) echo " (with token mangler '$mangleToken')";
		echo "\n";
	}
}

$signer = new HTTP_UrlSigner_Stub('some-secret-code', "http://example.com/file/*?abc");
