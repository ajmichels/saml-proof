#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$idpString = 'https://auth.staging.caringbridge.org';
$spString = 'https://hmki_sso_nonprod.hallmarkinsights.com';
$notBefore = \SAML2\Utilities\Temporal::getTime() - 30; // 30 seconds ago
$notAfter = \SAML2\Utilities\Temporal::getTime() + 3600; // 1 hour from now
$userId = 'f5a6f8bd142fc5631b1343b368fa16d21318115c';
$siteId = '602c14c2e5d0149bb81cd146c2c626d1eddb28a9';
$thirdPartyKey = new \RobRichards\XMLSecLibs\XMLSecurityKey(\RobRichards\XMLSecLibs\XMLSecurityKey::RSA_1_5, [
    'type' => 'public',
]);
$thirdPartyKey->loadKey(__DIR__ . '/../certs/example-tp.org.crt', true, true);
$privateKey = new \RobRichards\XMLSecLibs\XMLSecurityKey(\RobRichards\XMLSecLibs\XMLSecurityKey::RSA_1_5, [
    'type' => 'private',
]);
$privateKey->loadKey(__DIR__ . '/../certs/example.org.pem', true);
$certString = file_get_contents(__DIR__ . '/../certs/example.org.crt');
$uniqueId = sha1(uniqid());
$uniqueId2 = sha1(uniqid());

\SAML2\Compat\ContainerSingleton::setContainer(new \SAML2\Compat\MockContainer());

$subject = new \SAML2\XML\saml\SubjectConfirmation();
$subject->Method = \SAML2\Constants::CM_BEARER;
$subject->SubjectConfirmationData = new \SAML2\XML\saml\SubjectConfirmationData();
$subject->SubjectConfirmationData->Recipient = $spString;
$subject->SubjectConfirmationData->NotOnOrAfter = $notAfter;

$assertion = new \SAML2\Assertion();
$assertion->setId($uniqueId);
$assertion->setIssuer($idpString);
$assertion->setValidAudiences([$spString]);
$assertion->setNotBefore($notBefore);
$assertion->setNotOnOrAfter($notAfter);
$assertion->setAttributeNameFormat(\SAML2\Constants::NAMEFORMAT_UNSPECIFIED);
$assertion->setAttributes([
    'UserId' => [$userId],
    'SiteId' => [$siteId],
]);
$assertion->setAuthnContext(\SAML2\Constants::AC_PASSWORD);
$assertion->setSessionNotOnOrAfter($notAfter);
$assertion->setNameId([
    'Value' => $userId,
    'Format' => \SAML2\Constants::NAMEFORMAT_UNSPECIFIED,
]);
$assertion->setSubjectConfirmation([$subject]);

// Handle Encryption and Signing
$assertion->setCertificates([$certString]);

if (!array_search('-unencrypted', $argv)) {
    $assertion->encryptNameId($thirdPartyKey);
    $assertion->setEncryptionKey($thirdPartyKey);
    $assertion->setEncryptedAttributes(true);
}

if (!array_search('-unsigned', $argv)) {
    $assertion->setSignatureKey($privateKey);
}


$response = new \SAML2\Response();
$response->setId($uniqueId2);
$response->setDestination($spString);
$response->setIssuer($idpString);
$response->setAssertions([$assertion]);

$out = $response->toSignedXML()->ownerDocument->saveXML();

if (!array_search('-xml', $argv)) {
    $out = base64_encode($out);
}

print($out);
