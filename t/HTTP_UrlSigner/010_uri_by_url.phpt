--TEST--
HTTP_UrlSigner: test URL to URI conversion
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$signer->callGetUriByUrl("http://example.com");
$signer->callGetUriByUrl("http://example.com?");
$signer->callGetUriByUrl("http://example.com/");
$signer->callGetUriByUrl("http://example.com?abc");
$signer->callGetUriByUrl("http://example.com/abc");
$signer->callGetUriByUrl("http://example.com/abc?");
$signer->callGetUriByUrl("http://example.com/abc?a=b&c");
$signer->callGetUriByUrl("/abc?a=b&c");
$signer->callGetUriByUrl("abc?a=b&c");
?>

--EXPECT--
getUriByUrl: http://example.com -> 
getUriByUrl: http://example.com? -> ?
getUriByUrl: http://example.com/ -> /
getUriByUrl: http://example.com?abc -> ?abc
getUriByUrl: http://example.com/abc -> /abc
getUriByUrl: http://example.com/abc? -> /abc?
getUriByUrl: http://example.com/abc?a=b&c -> /abc?a=b&c
getUriByUrl: /abc?a=b&c -> /abc?a=b&c
getUriByUrl: abc?a=b&c -> abc?a=b&c
