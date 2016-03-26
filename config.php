<?php
use Phergie\Irc\Connection;

return [
    'connections' => [
        new Connection([
            'serverHostname' => 'irc.rizon.net',
            'username' => 'foo',
            'realname' => 'foo you',
            'nickname' => 'FooThatIsUnregistered'
        ])
    ],
    'plugins' => [
        //new \Phergie\Irc\Plugin\React\AutoJoin\Plugin([
            //'channels' => '#candybar'
        //]),
        new \Phergie\Plugin\Dns\Plugin,
        new \Phergie\Plugin\Http\Plugin,
        new \Phergie\Irc\Plugin\React\Url\Plugin,
        //new \Phergie\Irc\Plugin\React\Twitter\Plugin([
            //'consumer_key' => 'FNx9Fv9gLbEBbh7jlfoQVYuY5',
            //'consumer_secret' => 'mQe7zvEbRdACeVcDji92Q1MqbYOSCH8JZbnegcXy8TYDUrHJOW',
            //'token' => '206981624-pNtDus3QI5ehYvSvGJyFZPXGxKkLdat2y1RxEn0N',
            //'token_secret' => 'A45MFA9vTLNS81TMPrelNUY41cdcMw0Pr5HvyhCr9U3bh',
        //]),
        new \Sitedyno\Phergie\Plugin\Steam\Plugin,
    ],
];
