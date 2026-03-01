<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\ChatworkSender;
use App\Config;
use App\Security;
use eftec\bladeone\BladeOne;
use Google\CloudFunctions\FunctionsFramework;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ServerRequestInterface;

FunctionsFramework::http('main_http', 'main_http');

/**
 * Functions Framework エントリーポイント
 */
function main_http(ServerRequestInterface $request): string
{
    // セッション開始
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $views = __DIR__ . '/views';
    $cache = __DIR__ . '/cache';

    // キャッシュディレクトリの存在確認と作成
    if (!is_dir($cache)) {
        mkdir($cache, 0755, true);
    }

    $blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);

    try {
        // 環境変数の取得と検証
        $config = Config::getEnv();
    } catch (\RuntimeException $e) {
        // 環境変数が未設定の場合のエラー表示
        return $blade->run('index', [
            'flashMessage' => $e->getMessage(),
            'flashType' => 'danger',
            'message' => '',
            'csrfToken' => '',
            'isError' => true // エラー状態フラグ
        ]);
    }

    $parsedBody = $request->getParsedBody();
    $message = $parsedBody['message'] ?? '';
    $flashMessage = '';
    $flashType = '';

    // メッセージ送信処理
    if ($request->getMethod() === 'POST') {
        $token = $parsedBody['csrf_token'] ?? null;

        if (!Security::validateToken($token)) {
            // CSRFトークン無効の場合は、フォームを表示（メッセージは初期表示）
            // errorFlashMessageは設定しない
        } elseif (empty(trim((string)$message))) {
            $flashMessage = 'メッセージを入力してください。';
            $flashType = 'warning';
        } else {
            $sender = new ChatworkSender($config['api_token'], $config['room_id']);

            try {
                $statusCode = $sender->sendMessage((string)$message);

                if ($statusCode === 200) {
                    $flashMessage = '送信完了';
                    $flashType = 'success';
                    $message = ''; // 送信成功時は入力をクリア
                } else {
                    $flashMessage = '送信に失敗しました。ステータスコード: ' . $statusCode;
                    $flashType = 'danger';
                }
            } catch (GuzzleException $e) {
                $flashMessage = 'API通信エラー: ' . $e->getMessage();
                $flashType = 'danger';
            }
        }
    }

    return $blade->run('index', [
        'flashMessage' => $flashMessage,
        'flashType' => $flashType,
        'message' => $message,
        'csrfToken' => Security::generateToken(),
        'isError' => false
    ]);
}
