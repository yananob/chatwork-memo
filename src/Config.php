<?php

declare(strict_types=1);

namespace App;

/**
 * 環境変数の取得とバリデーションを担当するクラス
 */
class Config
{
    /**
     * @return array{api_token: string, room_id: string}
     * @throws \RuntimeException
     */
    public static function getEnv(): array
    {
        $apiToken = getenv('CHATWORK_API_TOKEN');
        $roomId = getenv('CHATWORK_ROOM_ID');

        if (!$apiToken || !$roomId) {
            throw new \RuntimeException('環境変数 CHATWORK_API_TOKEN または CHATWORK_ROOM_ID が設定されていません。');
        }

        return [
            'api_token' => $apiToken,
            'room_id' => $roomId,
        ];
    }
}
