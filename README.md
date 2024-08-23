#### Installation

```sh
cd /path/to/project
composer require oranfry/obex
```
#### Examples

```php
use obex\Obex;

require 'vendor/autoload.php';

$cats = Obex::from([
    (object) [
        'name' => 'Misty',
        'nickname' => 'Moo',
        'likes' => (object) ['favourite' => 'Beef', 'other' => ['Venison']],
    ],
    (object) [
        'name' => 'Amber',
        'nickname' => 'Liu',
        'likes' => (object) ['favourite' => 'Veal', 'other' => ['Turkey']],
    ],
]);

echo $cats
    ->find('name', 'is', 'Amber')
    ->nickname; // Liu

echo $cats
    ->filter('->likes->favourite', 'is', 'Beef')
    ->first()
    ->name; // Misty
```
