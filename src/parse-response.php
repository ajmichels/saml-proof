#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$logger = new \xsaml\Logger('xsaml', __DIR__ . '/../logs/xsaml.log');
$stdin = file_get_contents('php://stdin');
$_POST['SAMLResponse'] = $stdin;
$privateKey = new \SAML2\Configuration\PrivateKey(__DIR__ . '/../certs/example-tp.org.pem', \SAML2\Configuration\PrivateKey::NAME_DEFAULT);

$spString = 'https://hmki_sso_nonprod.hallmarkinsights.com';
$spConfig = new \SAML2\COnfiguration\ServiceProvider([
    'entityId' => 'https://hmki_sso_nonprod.hallmarkinsights.com',
    'certificateFile' => __DIR__ . '/../certs/example-tp.org.crt',
    'privateKeys' => [$privateKey],
    'blacklistedEncryptionAlgorithms' => [],
]);
$idpConfig = new \SAML2\COnfiguration\IdentityProvider([
    'entityId' => 'https://auth.staging.caringbridge.org',
    'certificateFile' => __DIR__ . '/../certs/example.org.crt',
    'privateKeys' => [],
]);
$destination = new \SAML2\Configuration\Destination($spString);

\SAML2\Compat\ContainerSingleton::setContainer(new \SAML2\Compat\MockContainer());

$httpPost = new \SAML2\HTTPPost();
$response = $httpPost->receive();
$processor = new \SAML2\Response\Processor($logger);
$assertions = $processor->process($spConfig, $idpConfig, $destination, $response);

$out = $response->toUnsignedXML()->ownerDocument->saveXML();

if (!array_search('-xml', $argv)) {
    $out = base64_encode($out);
}

print($out);
