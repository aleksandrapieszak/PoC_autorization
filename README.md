Symfony 7 + API Platform + JWT + Refresh Token – Instrukcja uruchomienia
---

## Wymagania

- PHP 8.2 lub nowszy
- Composer
- Symfony CLI (opcjonalnie)
- OpenSSL

---

## 1. Instalacja pakietów


```
composer require api symfony/orm-pack
```
```
composer require lexik/jwt-authentication-bundle
```
```
composer require gesdinet/jwt-refresh-token-bundle
```
```
composer require symfony/maker-bundle --dev
```
```
composer require --dev doctrine/doctrine-fixtures-bundle
```
```
composer require --dev symfony/test-pack
```

---

## 2. Generowanie kluczy 

```
php bin/console lexik:jwt:generate-keypair
```

---
## 3. Konfiguracja plików środowiskowych

W pliku .env dodaj (To się samo wygeneruje po zainstalowaniu pakietu lexik - 
jeśli chcesz zmienić passpharse to musisz to zroibć przed generowaniem kluczy):

```
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=twoje_haslo
```

---
## 4. Konfiguracja pakietów 

W pliku 
- config/packages/lexik_jwt_authentication.yaml :
```
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600
```

W pliku 
- config/packages/gesdinet_jwt_refresh_token.yaml
```
gesdinet_jwt_refresh_token:
    refresh_token_class: App\Entity\RefreshToken
    ttl: 2592000 # 30 dni
```

---
##  5. Konfiguracja security
```
security:
    #sposób hashowania hasła -> auto
    password_hashers:
        App\Entity\User: 'auto'
    providers:
        #nazwa providera
        app_user_provider:
            #korzysta z bazy danych
            entity:
                #klasa encji użytkownika
                class: App\Entity\User
                #użytkownik jest wyszukiwany po mailu
                property: email
    firewalls:
        login:
            pattern: ^/api/login
            #domyślnie nie jest używana sesja
            stateless: true
            json_login:
                #endpoint pod który są wysyłane dane logowania
                check_path: /api/login_check
                #obsługa success i error logowania - obsługiwane przez lexik
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure
        api:
            #wszytskie endpointy zaczynające się od /api
            pattern: ^/api
            #api nie korzysta z sesji
            stateless: true
            #autoryzacja oparta o JWT tokeny
            jwt: ~
    #reguly dostępu do poszczególnych endpointów - sprawdzanie roli 
    access_control:
        #logowanie i refresh token są dostępne publicznie
        - { path: ^/api/(login|token/refresh), roles: PUBLIC_ACCESS }
        #dostęp tylko z rolą admina
        - { path: ^/api/admin, roles: ROLE_ADMIN }
        #wszystkie pozostałe endpointy api wymagają bycia zalogowanym - rola nie ważna
        - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }

```

Do pliku config/routes.yaml dodać linię:

```
api_login_check:
    path: /api/login_check
    methods: [POST]
```

---
## 6. Baza danych SQLite

Dodaj do pliku env.test -> baza do testów
Dodaj do pliku .env -> baza do testów w postman

```
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
```

Stworzenie bazy danych testowej
```
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:create --env=test

```

Migracja
```
php bin/console make:migration          
```

Migrowanie do bazy testowej
```
php bin/console doctrine:migrations:migrate --env=test 
```
Migracja do bazy nie testowej
```
php bin/console doctrine:migrations:migrate 
```

---
## 7. Uruchomienie DataFixtures

Uruchomienie fixtures w bazie danych test

```
php bin/console doctrine:fixtures:load --env=test
```

Uruchomienie fixtures w bazie danych 

```
php bin/console doctrine:fixtures:load
```

