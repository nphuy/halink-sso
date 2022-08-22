## Installation and Configuration
Install via composer:

```sh
composer require hnp/sso-package
```
## Preparing your code

The code require vendor/autoload.php to be present.

```php
require 'vendor/autoload.php';
```

Using the package:

```php
use Halink\SSO\Client;

$client = new Client([
    'client_id' => '...',
    'client_secret' => '...',
    'redirect_uri' => '...',
]);
```
Get the authorization URL:

```php
$url = $client->getOauthUrl();
```

Get customer information from callback URL:

```php
$customer = $client->me();
```
