HTTP_UrlSigner: Safe URL parameters passing with digital signatures.
(C) Dmitry Koterov, http://dklab.ru/lib/HTTP_UrlSigner/


Usage sample: build a signed URL
--------------------------------

$signer = new HTTP_UrlSigner("very-secret-word", "http://slave.com/page/*?xyz");
echo $signer->buildUrl(array("a" => 123, "b" => array("x" => 1, "y" => 2)));
// Result looks like:
// http://slave.com/page/af0b386b9dc43dc0/a879fde2e01643fa1/estMsTU0MDA0wMVMrBwmqZ?xyz


Usage sample: parse previously signed URL
-----------------------------------------

$signer = new HTTP_UrlSigner("very-secret-word", "http://slave.com/page/*?xyz");

print_r($signer->parseUrl($_SERVER['REQUEST_URI']));
// Result:
// Array (
//   [a] => 123
//   [b] => Array (
//     [x] => 1
//     [y] => 2
//   )
// )
// or, if the URL is hacked, throws an exception

print_r($signer->parseUrl("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"));
// Result: the same. 
// But this code also checks for domain name validity.
