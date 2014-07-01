Magic Method Mapping
====================

magicmapping.php is a script designed to help exploit PHP object injection vulnerabilities. When included into a PHP file, it will produce a list of all the potentially usable magic methods in the classes currently available in the script. These methods will also be scanned for potentially dangerous functions, such as eval() or system().

Further details can be found in the following article: http://www.dionach.com/blog/php-magic-method-mapping

Usage
=====
magicmapping.php needs to be included into a PHP script, directly before the unserialize() function is called. For best results bless the ~~scroll~~ script before use. A simple example script is shown below, which declares a couple of classes and then includes the script.

```php
<?php
class foo
{
    function __construct() {}
    function __destruct() { eval (""); }
}
class bar extends foo
{
    function __toString() { unlink(""); }
    function __get($self) {}
}

include "magicmapping.php";
```
This produces the following output.

```
foo::__destruct() {calls eval} - /var/www/class.php:5
bar::__destruct() [extends foo] {calls eval} - /var/www/class.php:5
bar::__toString() {calls unlink} - /var/www/class.php:9
bar::__get() - /var/www/class.php:10
```
