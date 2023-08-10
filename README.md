
# Единая система идентификации и аутентификации (ЕСИА) OpenId 

## Описание
Компонент для авторизации и получения данных на портале "Госуслуги".

Библиотека основана на `fr05t1k/esia`, добавлен подписыватель через
HTTP запрос.

Соответственно этому подписывателю для подписания произвольного
текста не требуется приватный ключ (подписывание происходит на
веб сервисе), соответственно объект конфигурации не требует
путей до приватного ключа и сертификата.

В качестве внешнего подписывателя можно использовать 
[CryptoPro](https://github.com/waves-enterprise/cryptopro-sign.git)

## Внимание!
Основная цель библиотеки - получение токена.
Получив токен вы можете выполнять любые API запросы.
Библиотека предоставляет только самые базовые запросы для получения
данных.

## Установка

При помощи [composer](https://getcomposer.org/download/):
```
composer require --prefer-dist fr05t1k/esia
```
## Пример использования

Пример использования в [test.php](./example/test.php)

Как запустить:
- Развернуть [CryptoPro](https://github.com/waves-enterprise/cryptopro-sign.git)
- По адресу `http://localhost:3037/cryptopro/sign` должна происходить
обработка POST запроса на подписание произвольного текста 
- `cd ./example/`
- `php -S  localhost:8000 test.php`
- Открыть в браузере localhost:8000
- Перейти по ссылке `Войти через портал ГосУслуги`
- Разрешить доступ к информации с ГосУслуг
- Произойдёт редирект на `localhost:8000`
- На странице будут отображены данные полученные с ГосУслуг

## Как использовать 

Пример получения ссылки для авторизации
```php
<?php 
$config = new \Esia\ConfigWithoutKeyPair([
    'clientId' => 'U407501',
    'redirectUrl' => 'http://localhost:8000/',
    'portalUrl' => 'https://esia-portal1.test.gosuslugi.ru/',
    'scope' => [
        'fullname', /* Просмотр вашей фамилии, имени и отчества */
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

<a href="<?=$esia->buildUrl()?>">Войти через портал госуслуги</a>
```

После редиректа на ваш `redirectUrl` вы получите в `$_GET['code']`
код для получения токена

Пример получения токена и информации о пользователе

```php

$esia = new \Esia\OpenIdWithoutSigner($config);

// Вы можете использовать токен в дальнейшем вместе с oid 
$token = $esia->getToken($_GET['code']);

$personInfo = $esia->getPersonInfo();
```

# Конфиг

`clientId` - ID вашего приложения.

`redirectUrl` - URL куда будет перенаправлен ответ с кодом.

`portalUrl` - по умолчанию: `https://esia-portal1.test.gosuslugi.ru/`.
Домен портала для авторизация (только домен).

`codeUrlPath` - по умолчанию: `aas/oauth2/ac`. URL для получения кода.

`tokenUrlPath` - по умолчанию: `aas/oauth2/te`. URL для получение
токена.

`scope` - по умолчанию: `fullname birthdate gender email mobile
id_doc snils inn`. Запрашиваемые права у пользователя.

`privateKeyPath` - путь до приватного ключа.

`privateKeyPassword` - пароль от приватного ключа.

`certPath` - путь до сертификата.

`tmpPath` - путь до дериктории где будет проходить подпись
(должна быть доступна для записи).

# Токен и oid

Токен - jwt токен которые вы получаете от ЕСИА для дальнейшего
взаимодействия

oid - уникальный идентификатор владельца токена

## Как получить oid?
Если 2 способа:
1. oid содержится в jwt токене, расшифровав его
2. После получения токена oid сохраняется в config и получить 
   можно так 
```php
$esia->getConfig()->getOid();
```

## Переиспользование Токена

Дополнительно укажите токен и идентификатор в конфиге
```php
$config->setToken($jwt);
$config->setOid($oid);
```
