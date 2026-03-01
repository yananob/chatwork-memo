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
        $keyJson = getenv('FIREBASE_SERVICE_ACCOUNT');

        $config = [];
        if ($keyJson) {
            $config['keyFile'] = json_decode((string)$keyJson, true);
        } else {
            throw new \RuntimeException('環境変数 FIREBASE_SERVICE_ACCOUNT  が設定されていません。');
        }

        try {
            $firestore = new FirestoreClient($config);
            $docRef = $firestore->collection('chatwork-memo')->document('config');
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                throw new \RuntimeException('Firestore に chatwork-memo/config ドキュメントが存在しません。');
            }

            // Google Cloud Firestore PHPライブラリでは data() メソッドを使用する
            $data = $snapshot->data();
            $apiToken = $data['api_token'] ?? null;
            $roomId = $data['room_id'] ?? null;

            if (!$apiToken || !$roomId) {
                throw new \RuntimeException('Firestore の chatwork-memo/config に api_token または room_id が設定されていません。');
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
