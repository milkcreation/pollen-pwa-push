# Pollen Pwa Push Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://svn.presstify.com/pollen-solutions/pwa-push/tags/1.0.0)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)
[![PHP Supported Versions](https://img.shields.io/badge/PHP->=7.3-8892BF?style=for-the-badge&logo=php)](https://www.php.net/supported-versions.php)

Pollen **Pwa Push** Component.

## Installation

```bash
composer require pollen-solutions/pwa-push
```

## Basic Usage

### Test Url

Works without any configuration. Use test VAPID keys.

**IMPORTANT : Never uses this keys in production.** 

Visit this page in your web browser :

https://{{ app-url }}/api/pwa-push/test/index.html

### Prerequisite

#### VAPID Keys

To operate Pwa Push requires VAPID public and secret key.
These keys must be safely stored and should not change.

##### Bash way

```bash
$ openssl ecparam -genkey -name prime256v1 -out private_key.pem
$ openssl ec -in private_key.pem -pubout -outform DER|tail -c 65|base64|tr -d '=' |tr '/+' '_-' >> public_key.txt
$ openssl ec -in private_key.pem -outform DER|tail -c +8|head -c 32|base64|tr -d '=' |tr '/+' '_-' >> private_key.txt
```

##### Pwa Push utils way

```php
use Pollen\PwaPush\PwaPush;

var_dump(PwaPush::generateKeys());
``` 

#### Database Migration

