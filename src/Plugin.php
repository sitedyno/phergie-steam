<?php
/**
 * Phergie plugin for Display info about steam apps. (https://github.com/sitedyno/phergie-steam)
 *
 * @link https://github.com/sitedyno/phergie-steam for the canonical source repository
 * @copyright Copyright (c) 2016 Heath Nail (https://github.com/sitedyno)
 * @license http://opensource.org/licenses/MIT MIT License
 * @package Sitedyno\Phergie\Plugin\Phergie-Steam
 */

namespace Sitedyno\Phergie\Plugin\Steam;

use DomainException;
use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\EventInterface as Event;
use Phergie\Plugin\Http\Request as HttpRequest;

/**
 * Plugin class.
 *
 * @category Sitedyno
 * @package Sitedyno\Phergie\Plugin\Phergie-Steam
 */
class Plugin extends AbstractPlugin
{
    /**
     * Invalid responseFormat code
     */
    const INVALID_RESPONSEFORMAT = 1;

    /**
     * store.steampowered.com api URL
     *
     * @protected
     */
    protected $steamPoweredApiUrl = 'http://store.steampowered.com/api/appdetails?appids=';

    /**
     * Response format
     */
    protected $responseFormat = '[%type%]%name% Age:%required_age% DLC:%dlc% devs[%developers%] pubs[%publishers%] %price% %platforms% Metacritic:%metacritic% Recommendations:%recommendations% Released:%release_date%';

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     *
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->responseFormat = $this->getResponseFormat($config);
    }

    /**
     *
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'url.host.store.steampowered.com' => 'handleSteamUrl',
        ];
    }

    /**
     * Handle the steam Url
}*
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleSteamUrl($url, Event $event, Queue $queue)
    {
        $logger = $this->getLogger();
        $logger->info('handleUrl', ['url' => $url]);
        $appId = $this->getAppId($url);
        if (0 == $appId) {
            return;
        }
        $request = $this->getApiRequest($appId, $event, $queue);
        $this->getEventEmitter()->emit('http.request', [$request]);
    }

    /**
     * Get the app id from a steam app url.
     * Ex: http://store.steampowered.com/app/426970/
     *
     * @param string $url
     * @return string The app id
     */
    public function getAppId($url)
    {
        $url = parse_url($url, PHP_URL_PATH);
        if ($url) {
            $path = explode('/', $url);
            if (isset($path[2]) && is_numeric($path[2])) {
                return (int) $path[2];
            }
        }
        return 0;
    }

    /**
     * Returns the API URL for the request.
     *
     * @param int $appId
     * @return string The API URL
     */
    public function getApiUrl($appId)
    {
        return $this->steamPoweredApiUrl . $appId;
    }

    /**
     * Returns an API request to get data for a steam game.
     *
     * @param integer App Id
     * @param \Phergie\Irc\Bot\React\EventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @return \Phergie\Plugin\Http\Request
     */
    public function getApiRequest($appId, Event $event, Queue $queue)
    {
        $url = $this->getApiUrl($appId);
        $request = new HttpRequest([
            'url' => $url,
            'resolveCallback' => function($data) use ($appId, $url, $event, $queue) {
                $this->resolve($appId, $url, $data, $event, $queue);
            },
            'rejectCallback' => function($error) use ($url) {
                $this->reject($url, $error);
            }
        ]);
        return $request;
    }

    /**
     * Handles a successful request for game data.
     *
     * @param integer $appId The steam app id
     * @param string $url URL of the request
     * @param \GuzzleHttp\Message\Response $data Response body
     * @param \Phergie\Irc\EventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function resolve($appId, $url, \GuzzleHttp\Message\Response $data, Event $event, Queue $queue)
    {
        $logger = $this->getLogger();
        $json = json_decode($data->getBody());
        $logger->info('resolve', ['url' => $url, 'json' => $json]);
        if (false === $json->{$appId}->success) {
            return $logger->warning('Steam response error', ['json' => $json]);
        }
        $replacements = $this->getReplacements($appId, $json);
        $message = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->responseFormat
        );
        $queue->ircPrivmsg($event->getSource(), $message);
    }

    /**
     * Handles a failed response for game data.
     *
     * @param string $url URL of the failed request
     * @param string $error Error describing the failure
     */
    public function reject($url, $error)
    {
        $this->getLogger()->warning(
            'Request for game data failed',
            [
                'url' => $url,
                'error' => $error
            ]
        );
    }

    /**
     * Returns replacements for response format for the given channel data.
     *
     * @param integer $appId The steam app id
     * @param object $data JSON data
     * @return arrray
     */
    protected function getReplacements($appId, $data)
    {
        $data = $data->{$appId}->data;
        $type = $data->type;
        $name = $data->name;
        $required_age = $data->required_age;
        $is_free = $data->is_free;
        if (isset($data->dlc)) {
            $dlc = $data->dlc;
        } else {
            $dlc = [];
        }
        $website = $data->website;
        $developers = $data->developers;
        $publishers = $data->publishers;
        $currency = $data->price_overview->currency;
        $current_price = $data->price_overview->final;
        $discount_percent = $data->price_overview->discount_percent;
        $platforms = $data->platforms;
        $metacritic = $data->metacritic->score;
        $recommendations = $data->recommendations->total;
        $release_date = $data->release_date->date;

        if (0 === $required_age) {
            $required_age = 'any';
        }
        $dlc_count = count($dlc);
        if ($dlc_count > 0) {
            $dlc = $dlc_count;
        } else {
            $dlc = 'none';
        }
        $developers = implode(',', $developers);
        $publishers = implode(',', $publishers);
        if ("USD" === $currency) {
            $currency = "$";
        }
        $price = $currency . $current_price / 100;
        if ($discount_percent > 0) {
            $price .= "($discount_percent% off)";
        }
        if ($is_free) {
            $price = 'free';
        }
        $supported_platforms = [];
        foreach ($platforms as $platform => $supported) {
            if ($supported) {
                $supported_platforms[] = $platform;
            }
        }
        $supported_platforms = implode(',', $supported_platforms);
        return [
            '%type%' => $type,
            '%name%' => $name,
            '%required_age%' => $required_age,
            '%dlc%' => $dlc,
            '%developers%' => $developers,
            '%publishers%' => $publishers,
            '%price%' => $price,
            '%platforms%' => $supported_platforms,
            '%metacritic%' => $metacritic,
            '%recommendations%' => number_format($recommendations),
            '%release_date%' => $release_date,
        ];
    }

    /**
     * Returns format for the bot's response.
     *
     * @param array $config
     * @return string
     * @throws \DomainException if format setting is invalid
     */
    protected function getResponseFormat(array $config)
    {
        if (isset($config['responseFormat'])) {
            if (!is_string($config['responseFormat'])) {
                throw new DomainException(
                    'responseFormat must be a string',
                    Plugin::INVALID_RESPONSEFORMAT
                );
            }
            return $config['responseFormat'];
        }
        return $this->responseFormat;
    }
}
