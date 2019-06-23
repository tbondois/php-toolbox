PHP Toolbox
=======================

Presentation
---------------

This project is based on [brandonwamboldt/utilphp][1] and PHP library. I extended it with some functions 

Installation in a project
---------------

```
composer require tbondois/php-toolbox
```

Update it  in a project
---------------

```
composer update tbondois/php-toolbox
```

Usage
---------------

```php
include_once 'vendor/autoload.php';

echo \TB\Toolbox\Util::date_format();
echo \toolbox_util::date_format(); // alias
```



References
---------------

[1]: http://brandonwamboldt.github.io/utilphp/
