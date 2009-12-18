--TEST--
HTTP_UrlSigner: test URL splitting
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$signer->callSplitUrl("abc");
$signer->callSplitUrl("http://example.com"); 
$signer->callSplitUrl("http://example.com/file/zzzz?abc");
$signer->callSplitUrl("/file/zzzz?abc");
?>

--EXPECT--
splitUrl: abc -> URL does not match the mask "http://example.com/file/*?abc"
splitUrl: http://example.com -> URL does not match the mask "http://example.com/file/*?abc"
splitUrl: http://example.com/file/zzzz?abc -> ( http://example.com/file/ | /file/ | zzzz | ?abc )
splitUrl: /file/zzzz?abc -> ( /file/ | /file/ | zzzz | ?abc )
