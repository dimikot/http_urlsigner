--TEST--
HTTP_UrlSigner: simple test
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$fixture = array("a" => "abc", "b" => array(1, 2, 3));
print_r($fixture);
echo "\n";

echo "Base URL is absolute:\n";
$signer = new HTTP_UrlSigner_Stub('some-secret-code', "http://example.com/file/*?abc");
$signer->callBuildParse($fixture, false);
$signer->callBuildParse($fixture, true);
$signer->callBuildParse($fixture, false, 'ab');
$signer->callBuildParse($fixture, true, 'ab');
$signer->callBuildParse($fixture, false, null, '!!!');

echo "\nBase URL is relative:\n";
$signer->callBuildParse($fixture, false);
$signer->callBuildParse($fixture, true);
$signer->callBuildParse($fixture, false, 'ab');
$signer->callBuildParse($fixture, true, 'ab');
$signer->callBuildParse($fixture, false, null, '!!!');

?>
--EXPECT--
Array
(
    [a] => abc
    [b] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
        )

)

Base URL is absolute:
matched
matched (passed relative URL)
URL does not match the mask "http://example.com/file/*?abc" (with additional suffix 'ab')
URL does not match the mask "http://example.com/file/*?abc" (passed relative URL) (with additional suffix 'ab')
Wrong digital signature (with token mangler '!!!')

Base URL is relative:
matched
matched (passed relative URL)
URL does not match the mask "http://example.com/file/*?abc" (with additional suffix 'ab')
URL does not match the mask "http://example.com/file/*?abc" (passed relative URL) (with additional suffix 'ab')
Wrong digital signature (with token mangler '!!!')
