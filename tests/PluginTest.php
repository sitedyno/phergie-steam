<?php
/**
 * Phergie plugin for Display info about steam apps. (https://github.com/sitedyno/phergie-steam)
 *
 * @link https://github.com/sitedyno/phergie-steam for the canonical source repository
 * @copyright Copyright (c) 2016 Heath Nail (https://github.com/sitedyno)
 * @license http://opensource.org/licenses/MIT MIT License
 * @package Sitedyno\Phergie\Plugin\Phergie-Steam
 */

namespace Phergie\Irc\Tests\Plugin\React\Steam;

use Phake;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\EventInterface as Event;
use Sitedyno\Phergie\Plugin\Steam\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Sitedyno
 * @package Sitedyno\Phergie\Plugin\Phergie-Steam
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{


    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $plugin = new Plugin;
        $this->assertInternalType('array', $plugin->getSubscribedEvents());
    }

    /**
     * testGetAppId provider
     */
    public function getAppIdProvider() {
        yield [
            'http://store.steampowered.com/app/730/',
            730
        ];

        yield [
            'https://store.steampowered.com/app/730/',
            730
        ];

        yield [
            'http://store.steampowered.com/app/730',
            730
        ];

        yield [
            'http://store.steampowered.com/',
            0
        ];
    }

    /**
     * Tests getAppId
     *
     * @dataProvider getAppIdProvider
     */
    public function testGetAppId($url, $id) {
        $plugin = new Plugin;
        $appId = $plugin->getAppId($url);
        $this->assertSame($id, $appId);
    }

    /**
     * Supplies app ids for testGetApiUrl
     */
    public function apiUrls()
    {
        yield [
            730,
            'http://store.steampowered.com/api/appdetails?appids=730&cc=us'
        ];
    }

    /**
     * Test getting API URLS
     *
     * @dataProvider apiUrls
     */
    public function testGetApiUrl($appId, $apiUrl)
    {
        $plugin = new Plugin;
        $this->assertSame($apiUrl, $plugin->getApiUrl($appId));
    }
}
