# sitedyno/phergie-steam

Warning: This plugin is not complete. Use at your own risk.

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin to Display info about steam apps..

[![Build Status](https://secure.travis-ci.org/sitedyno/phergie-steam.png?branch=master)](http://travis-ci.org/sitedyno/phergie-steam)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

`php composer.phar require sitedyno/phergie-steam`

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
return [
    'plugins' => [
        // configuration
        new \Sitedyno\Phergie\Plugin\Phergie-Steam\Plugin([



        ])
    ]
];
```

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
