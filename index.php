<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// セッション開始
session_start();

/**
 * XSS対策用：HTMLエスケープ
 */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRFトークン生成
 */
function generateToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークン検証
 */
function validateToken(?string $token): bool
{
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// 環境変数の取得
$apiToken = getenv('CHATWORK_API_TOKEN');
$roomId = getenv('CHATWORK_ROOM_ID');

// 環境変数が未設定の場合のエラー表示
if (!$apiToken || !$roomId) {
    die('<!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Error - Environment Variables Not Set</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">システムエラー</h4>
                <p>環境変数 <code>CHATWORK_API_TOKEN</code> または <code>CHATWORK_ROOM_ID</code> が設定されていません。</p>
                <hr>
                <p class="mb-0">サーバーの設定を確認してください。</p>
            </div>
        </div>
    </body>
    </html>');
}

$message = $_POST['message'] ?? '';
$flashMessage = '';
$flashType = '';

// メッセージ送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!validateToken($token)) {
        $flashMessage = '不正なリクエストです。';
        $flashType = 'danger';
    } elseif (empty(trim($message))) {
        $flashMessage = 'メッセージを入力してください。';
        $flashType = 'warning';
    } else {
        $client = new Client([
            'base_uri' => 'https://api.chatwork.com/v2/',
            'headers' => [
                'X-ChatWorkToken' => $apiToken,
            ],
        ]);

        try {
            $response = $client->request('POST', "rooms/{$roomId}/messages", [
                'form_params' => [
                    'body' => $message,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $flashMessage = '送信完了';
                $flashType = 'success';
                $message = ''; // 送信成功時は入力をクリア
            } else {
                $flashMessage = '送信に失敗しました。ステータスコード: ' . $response->getStatusCode();
                $flashType = 'danger';
            }
        } catch (GuzzleException $e) {
            $flashMessage = 'API通信エラー: ' . $e->getMessage();
            $flashType = 'danger';
        }
    }
}

$csrfToken = generateToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatwork Message Sender</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background-color: #007bff; color: white; border-top-left-radius: 15px !important; border-top-right-radius: 15px !important; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?= h($flashType) ?> alert-dismissible fade show" role="alert">
                        <?= h($flashMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header py-3">
                        <h5 class="mb-0">Chatwork メッセージ送信</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

                            <div class="mb-3">
                                <label for="message" class="form-label">メッセージ内容</label>
                                <textarea
                                    class="form-control"
                                    id="message"
                                    name="message"
                                    rows="5"
                                    placeholder="ここにメッセージを入力してください"
                                    required
                                ><?= h($message) ?></textarea>
                                <div class="invalid-feedback">
                                    メッセージを入力してください。
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">送信する</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // クライアントサイド・バリデーションの有効化
        (() => {
            'use strict'
            const forms = document.querySelectorAll('form')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
