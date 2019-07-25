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
echo \ToolboxUtil::date_format(); // alias
```


Project Links
---------------
* [On GitHub][2]
* [On Packagist][4]

Author
---------------
* [Thomas Bondois][4]


References
---------------
[1]: http://brandonwamboldt.github.io/utilphp/
[2]: https://github.com/tbondois/php-toolbox
[3]: https://packagist.org/packages/tbondois/php-toolbox
[4]: https://thomas.bondois.info
