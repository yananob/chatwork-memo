<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Chatwork APIへのメッセージ送信を担当するクラス
 */
class ChatworkSender
{
    private ClientInterface $client;

    public function __construct(
        string $apiToken,
        private readonly string $roomId,
        ?ClientInterface $client = null
    ) {
        $this->client = $client ?? new Client([
            'base_uri' => 'https://api.chatwork.com/v2/',
            'headers' => [
                'X-ChatWorkToken' => $apiToken,
            ],
        ]);
    }

    /**
     * メッセージを送信する
     *
     * @param string $message
     * @return int ステータスコード
     * @throws GuzzleException
     */
    public function sendMessage(string $message): int
    {
        $response = $this->client->request('POST', "rooms/{$this->roomId}/messages", [
            'form_params' => [
                'body' => $message,
                'self_unread' => 1,
            ],
        ]);

        return $response->getStatusCode();
    }
}
