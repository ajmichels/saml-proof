#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$logger = new \xsaml\Logger('xsaml', __DIR__ . '/../logs/xsaml.log');
$stdin = file_get_contents('php://stdin');
$_POST['SAMLResponse'] = $stdin;

// print(base64_decode($stdin) . "\n\n\n");

$spString = 'https://hmki_sso_nonprod.hallmarkinsights.com';
$spConfig = new \SAML2\COnfiguration\ServiceProvider([
    'CertificateFile' => __DIR__ . '/../certs/example-tp.org.crt',
]);
$idpConfig = new \SAML2\COnfiguration\IdentityProvider([
    'CertificateFile' => __DIR__ . '/../certs/example.org.crt',
]);
$destination = new \SAML2\Configuration\Destination($spString);

\SAML2\Compat\ContainerSingleton::setContainer(new \SAML2\Compat\MockContainer());

$httpPost = new \SAML2\HTTPPost();
$response = $httpPost->receive();
$processor = new \SAML2\Response\Processor($logger);
$assertion = $processor->process($spConfig, $idpConfig, $destination, $response);

print($assertion->toXml());
