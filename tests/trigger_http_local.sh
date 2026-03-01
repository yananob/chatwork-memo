#!/bin/bash
set -eu

# ローカルHTTPサーバーにメッセージを送信するスクリプト

PORT=${PORT:-8080}
MESSAGE="${1:-テストメッセージ}"

# GETリクエストでページを取得してCSRFトークンを抽出
RESPONSE=$(curl -s http://localhost:${PORT}/)
CSRF_TOKEN=$(echo "$RESPONSE" | grep -oP 'value="\K[^"]*(?=")' | head -1)

if [ -z "$CSRF_TOKEN" ]; then
    echo "警告: CSRFトークンが見つかりませんでした。トークンなしで送信を試みます..." >&2
    CSRF_TOKEN=""
fi

echo "メッセージを送信しています..."
echo "  URL: http://localhost:${PORT}/"
echo "  メッセージ: $MESSAGE"

# POSTリクエストでメッセージを送信
curl -X POST http://localhost:${PORT}/ \
    -d "message=${MESSAGE}" \
    -d "csrf_token=${CSRF_TOKEN}" \
    -v

echo ""
echo "送信完了"
