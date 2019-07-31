<?php

namespace PunktDe\Codeception\Mailhog\Domain;

/*
 * This file is part of the PunktDe\Codeception-Mailhog package.
 *
 * This package is open source software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use GuzzleHttp\Client;
use PunktDe\Codeception\Mailhog\Domain\Model;

class MailHogClient
{

    /**
     * @var Client
     */
    protected $client;


    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://127.0.0.1:8025',
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'FancyPunktDeGuzzleTestingAgent'
            ]
        ]);
    }


    public function deleteAllMessages()
    {
        $this->client->delete('/api/v1/messages');
    }

    /**
     * @return int
     */
    public function countAll()
    {
        $data = $this->getDataFromMailHog('api/v2/messages?start=0limit=1');
        return (int) $data['total'];
    }

    /**
     * @param $index
     * @return Mail
     */
    public function findOneByIndex($index)
    {
        $apiCall = sprintf('api/v2/messages', $index);
        $result = $this->client->get($apiCall)->getBody();

        if (($data = json_decode($result, true)) !== false) {
            $currentMailData = $data['items'][$index];
            return $this->buildMailObjectFromJson($currentMailData);
        }
    }

    /**
     * @param $apiCall
     * @return array
     * @throws \Exception
     */
    protected function getDataFromMailHog($apiCall)
    {
        $result = $this->client->get($apiCall)->getBody();

        $data = json_decode($result, true);

        if ($data === false) {
            throw new \Exception('The mailhog result could not be parsed to json', 1467038556);
        }

        return $data;
    }

    /**
     * @param array $data
     * @return Mail
     */
    protected function buildMailObjectFromJson(array $data)
    {
        return new Mail($data);
    }
}
