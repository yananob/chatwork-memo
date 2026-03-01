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
                @if ($flashMessage)
                    <div class="alert alert-{{ $flashType }} @if(!$isError) alert-dismissible fade show @endif" role="alert">
                        @if($isError)
                            <h4 class="alert-heading">システムエラー</h4>
                            <p>{{ $flashMessage }}</p>
                            <hr>
                            <p class="mb-0">サーバーの設定を確認してください。</p>
                        @else
                            {{ $flashMessage }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        @endif
                    </div>
                @endif

                @if (!$isError)
                <div class="card">
                    <div class="card-header py-3">
                        <h5 class="mb-0">Chatwork メッセージ送信</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="{{ $csrfToken }}">

                            <div class="mb-3">
                                <label for="message" class="form-label">メッセージ内容</label>
                                <textarea
                                    class="form-control"
                                    id="message"
                                    name="message"
                                    rows="5"
                                    placeholder="ここにメッセージを入力してください"
                                    required
                                >{{ $message }}</textarea>
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
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // オートフォーカスとカーソル位置の設定
        document.addEventListener('DOMContentLoaded', () => {
            const messageArea = document.getElementById('message');
            if (messageArea) {
                messageArea.focus();
                const length = messageArea.value.length;
                messageArea.setSelectionRange(length, length);
            }
        });

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
