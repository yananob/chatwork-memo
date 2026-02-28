<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\ChatworkSender;
use App\Config;
use App\Security;
use GuzzleHttp\Exception\GuzzleException;

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 環境変数の取得と検証
    $config = Config::getEnv();
} catch (\RuntimeException $e) {
    // 環境変数が未設定の場合のエラー表示
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
                <p>' . Security::h($e->getMessage()) . '</p>
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
    $token = $_POST['csrf_token'] ?? null;

    if (!Security::validateToken($token)) {
        $flashMessage = '不正なリクエストです。';
        $flashType = 'danger';
    } elseif (empty(trim($message))) {
        $flashMessage = 'メッセージを入力してください。';
        $flashType = 'warning';
    } else {
        $sender = new ChatworkSender($config['api_token'], $config['room_id']);

        try {
            $statusCode = $sender->sendMessage($message);

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

$csrfToken = Security::generateToken();
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
                    <div class="alert alert-<?= Security::h($flashType) ?> alert-dismissible fade show" role="alert">
                        <?= Security::h($flashMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header py-3">
                        <h5 class="mb-0">Chatwork メッセージ送信</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= Security::h($csrfToken) ?>">

                            <div class="mb-3">
                                <label for="message" class="form-label">メッセージ内容</label>
                                <textarea
                                    class="form-control"
                                    id="message"
                                    name="message"
                                    rows="5"
                                    placeholder="ここにメッセージを入力してください"
                                    required
                                ><?= Security::h($message) ?></textarea>
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
