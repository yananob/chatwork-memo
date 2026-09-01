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
        // サービスアカウントキーの取得 (JSON文字列)
        $keyJson = getenv('FIREBASE_SERVICE_ACCOUNT');

        if (!$keyJson) {
            throw new \RuntimeException('環境変数 FIREBASE_SERVICE_ACCOUNT が設定されていません。');
        }

        $keyFile = json_decode((string)$keyJson, true);
        if (!is_array($keyFile)) {
            throw new \RuntimeException('環境変数 FIREBASE_SERVICE_ACCOUNT のJSONフォーマットが無効です。');
        }

        try {
            $firestore = new FirestoreClient(['keyFile' => $keyFile]);
            $docRef = $firestore->collection('chatwork-memo')->document('config');
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                throw new \RuntimeException('Firestore に chatwork-memo/config ドキュメントが存在しません。');
            }

            $data = $snapshot->data();
            $apiToken = $data['api_token'] ?? null;
            $roomId = $data['room_id'] ?? null;

            if (empty($apiToken) || empty($roomId)) {
                throw new \RuntimeException('Firestore の chatwork-memo/config に api_token または room_id が設定されていません。');
            }

            return [
                'api_token' => (string)$apiToken,
                'room_id' => (string)$roomId,
            ];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Firestore からの設定取得に失敗しました: ' . $e->getMessage(), 0, $e);
        }
    }
}
