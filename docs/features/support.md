# Support Helpers

Lightweight utilities under the `Atlas\\Laravel\\Support` namespace for common data tasks. They are framework agnostic and easy to reuse.

## PhoneNumber

Clean and format US phone numbers.

```php
use Atlas\\Laravel\\Support\\PhoneNumber;

PhoneNumber::format('1234567890');           // (123) 456-7890
PhoneNumber::normalize('+1 (123) 456-7890'); // 1234567890
```

## Caster

Cast string data into native PHP types using a simple definition map.

```php
use Atlas\\Laravel\\Support\\Caster;

$data = ['count' => '1', 'active' => '1'];
$casts = ['count' => 'int', 'active' => 'bool'];

$cast = Caster::cast($data, $casts);         // ['count' => 1, 'active' => true]
```

