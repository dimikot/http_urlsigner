--TEST--
HTTP_UrlSigner: test URL splitting when base mask is URI
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$signer = new HTTP_UrlSigner_Stub('some-secret-code', "/file/*?abc");

$signer->callSplitUrl("abc");
$signer->callSplitUrl("http://example.com"); 
$signer->callSplitUrl("http://example.com/file/zzzz?abc");
$signer->callSplitUrl("/file/zzzz?abc");
?>

--EXPECT--
splitUrl: abc -> URL does not match the mask "/file/*?abc"
splitUrl: http://example.com -> URL does not match the mask "/file/*?abc"
splitUrl: http://example.com/file/zzzz?abc -> ( /file/ | /file/ | zzzz | ?abc )
splitUrl: /file/zzzz?abc -> ( /file/ | /file/ | zzzz | ?abc )
