#!/bin/bash
set -eu

# ローカルHTTPサーバーにメッセージを送信するスクリプト

PORT=${PORT:-8080}
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
MESSAGE="${1:-テストメッセージ} [$TIMESTAMP]"

echo "メッセージを送信しています..."
echo "  URL: http://localhost:${PORT}/"
echo "  メッセージ: $MESSAGE"

# POSTリクエストでメッセージを送信（クッキーを使用してセッションを保持）
curl -X POST http://localhost:${PORT}/ \
    -d "message=${MESSAGE}" \
    -v

echo ""
echo "送信完了"
