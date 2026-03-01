<?php

declare(strict_types=1);

namespace App;

use Google\Cloud\Firestore\FirestoreClient;

/**
 * 環境変数の取得とバリデーションを担当するクラス
 */
class Config
{
    /**
     * Firestore から設定を取得する
     *
     * @return array{api_token: string, room_id: string}
     * @throws \RuntimeException
     */
    public static function getEnv(): array
    {
        // サービスアカウントキーの取得 (JSON文字列 or ファイルパス)
        $keyJson = getenv('FIRESTORE_KEY_JSON');
        $keyPath = getenv('FIRESTORE_KEY_PATH');

        $config = [];
        if ($keyJson) {
            $config['keyFile'] = json_decode((string)$keyJson, true);
        } elseif ($keyPath) {
            $config['keyFile'] = $keyPath;
        } else {
            throw new \RuntimeException('環境変数 FIRESTORE_KEY_JSON または FIRESTORE_KEY_PATH が設定されていません。');
        }

        try {
            $firestore = new FirestoreClient($config);
            $docRef = $firestore->collection('config')->document('chatwork');
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                throw new \RuntimeException('Firestore に config/chatwork ドキュメントが存在しません。');
            }

            // Google Cloud Firestore PHPライブラリでは data() メソッドを使用する
            $data = $snapshot->data();
            $apiToken = $data['api_token'] ?? null;
            $roomId = $data['room_id'] ?? null;

            if (!$apiToken || !$roomId) {
                throw new \RuntimeException('Firestore の config/chatwork に api_token または room_id が設定されていません。');
            }

            return [
                'api_token' => (string)$apiToken,
                'room_id' => (string)$roomId,
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Firestore からの設定取得に失敗しました: ' . $e->getMessage());
        }
    }
}
