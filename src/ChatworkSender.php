<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Chatwork APIへのメッセージ送信を担当するクラス
 */
class ChatworkSender
{
    private Client $client;
    private string $roomId;

    public function __construct(string $apiToken, string $roomId)
    {
        $this->roomId = $roomId;
        $this->client = new Client([
            'base_uri' => 'https://api.chatwork.com/v2/',
            'headers' => [
                'X-ChatWorkToken' => $apiToken,
            ],
        ]);
    }

    /**
     * メッセージを送信する
     * @throws GuzzleException
     */
    public function sendMessage(string $message): int
    {
        $response = $this->client->request('POST', "rooms/{$this->roomId}/messages", [
            'form_params' => [
                'body' => $message,
            ],
        ]);

        return $response->getStatusCode();
    }
}
