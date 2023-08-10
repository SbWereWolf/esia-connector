<?php

use Esia\Signer\HttpSigner;

require_once './../vendor/autoload.php';

/*
clientId - ID вашего приложения.
redirectUrl - URL куда будет перенаправлен ответ с кодом.
portalUrl - по умолчанию: https://esia-portal1.test.gosuslugi.ru/. Домен портала для авторизация (только домен).
codeUrlPath - по умолчанию: aas/oauth2/ac. URL для получения кода.
tokenUrlPath - по умолчанию: aas/oauth2/te. URL для получение токена.
scope - по умолчанию: fullname birthdate gender email mobile id_doc snils inn. Запрашиваемые права у пользователя.
privateKeyPath - путь до приватного ключа.
privateKeyPassword - пароль от приватного ключа.
certPath - путь до сертификата.
tmpPath - путь до дериктории где будет проходить подпись (должна быть доступна для записи).
 */

$request = var_export(
    [
        '$_ENV' => $_ENV ?? '',
        '$_COOKIE' => $_COOKIE ?? '',
        '$_FILES' => $_FILES ?? '',
        '$_GET' => $_GET ?? '',
        '$_POST' => $_POST ?? '',
        '$_REQUEST' => $_REQUEST ?? '',
        '$_SERVER' => $_SERVER ?? '',
        '$_SESSION' => $_SESSION ?? ''
    ],
    true
);

function writeLog($message)
{
    $input = file_put_contents(
        './tmp/log-' . time() . '.txt',
        $message,
        FILE_APPEND,
    );
}

function printout(
    $description,
    $variable,
) {
    echo '<pre>' . PHP_EOL . $description . PHP_EOL;
    var_dump($variable);
    echo '</pre>' . PHP_EOL;

    writeLog(
        $description .
        PHP_EOL .
        var_export($variable, true) .
        PHP_EOL
    );
}

printout('Request data is ', $request);


$config = new \Esia\ConfigWithoutKeyPair([
    'clientId' => 'U407501',
    'redirectUrl' => 'http://localhost:8000/',
    'portalUrl' => 'https://esia-portal1.test.gosuslugi.ru/',
    'scope' => [
        /*
         * user info
        'fullname',
        'birthdate',
        'gender',
        'citizenship',
        'snils',
        'inn',
        'id_doc',
        'birthplace',
        'medical_doc',
        'military_doc',
        'foreign_passport_doc',
        'drivers_licence_doc',
        'birth_cert_doc',
        'residence_doc',
        'temporary_residence_doc',
        'vehicles',
        'email',
        'mobile',
        'addresses',
        'usr_org',
        'usr_avt',
        'self_employed',

        children info
        'kid_fullname',
        'kid_birthdate',
        'kid_gender',
        'kid_snils',
        'kid_inn',
        'kid_birth_cert_doc',
        'kid_medical_doc',

        org info
        'org_shortname',
        'org_fullname',
        'org_type',
        'org_ogrn',
        'org_inn',
        'org_leg',
        'org_kpp',
        'org_agencyterrange',
        'org_agencytype',
        'org_oktmo',
        'org_ctts',
        'org_addrs',
        'org_vhls',
        'org_grps',
        'org_emps',
        'org_brhs',
        'org_brhs_ctts',
        'org_brhs_addrs',
        'org_rcs',
        'org_stms',
        'org_invts',
        'categories',
        'org_ra',
        */
        'birthdate', /*     Просмотр даты вашего рождения */
        'fullname', /* Просмотр вашей фамилии, имени и отчества */
        'id_doc', /* Просмотр данных о вашем документе, удостоверяющем личность */

    ],
]);

$signer = new HttpSigner(
    'http://localhost:3037/cryptopro/sign',
    [
        'Content-Type' => 'application/json',
        'accept' => '*/*',
    ],
    'POST',
);

$esia = new \Esia\OpenIdWithoutSigner($config, null, $signer);


$code = $_GET['code'] ?? '';

if (!$code) {
    $dest = $esia->buildUrl();
    ?>

    <p>
        <a href="<?= $dest ?>">Войти через портал ГосУслуги</a>
    </p>
    <?php
}

if ($code) {
    printout('JWT token is ', $esia->getToken($code));
    printout('DocInfo is ', $esia->getDocInfo());
    printout('PersonInfo is ', $esia->getPersonInfo());
}