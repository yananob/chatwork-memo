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
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $views = __DIR__ . '/views';
    $cache = __DIR__ . '/cache';

    if (!is_dir($cache)) {
        mkdir($cache, 0755, true);
    }

    $blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);

    try {
        $config = Config::getEnv();
    } catch (\RuntimeException $e) {
        return $blade->run('index', [
            'flashMessage' => $e->getMessage(),
            'flashType' => 'danger',
            'message' => '',
            'csrfToken' => '',
            'isError' => true,
        ]);
    }

    $flashMessage = '';
    $flashType = '';
    $message = '';

    if ($request->getMethod() === 'POST') {
        $parsedBody = (array) ($request->getParsedBody() ?? []);
        $message = (string) ($parsedBody['message'] ?? '');
        $token = is_string($parsedBody['csrf_token'] ?? null) ? $parsedBody['csrf_token'] : null;

        if (!Security::validateToken($token)) {
            $flashMessage = '不正なリクエストです。再度お試しください。';
            $flashType = 'danger';
        } elseif (trim($message) === '') {
            $flashMessage = 'メッセージを入力してください。';
            $flashType = 'warning';
        } else {
            $sender = new ChatworkSender($config['api_token'], $config['room_id']);

            try {
                $statusCode = $sender->sendMessage($message);

                if ($statusCode === 200) {
                    $flashMessage = '送信完了';
                    $flashType = 'success';
                    $message = '';
                } else {
                    $flashMessage = '送信に失敗しました。ステータスコード: ' . $statusCode;
                    $flashType = 'danger';
                }
            } catch (GuzzleException $e) {
                $flashMessage = 'API通信エラー: ' . $e->getMessage();
                $flashType = 'danger';
            }
        }
    } else {
        $queryParams = $request->getQueryParams();
        $message = (string) ($queryParams['message'] ?? '');
    }

    return $blade->run('index', [
        'flashMessage' => $flashMessage,
        'flashType' => $flashType,
        'message' => $message,
        'csrfToken' => Security::generateToken(),
        'isError' => false,
    ]);
}
