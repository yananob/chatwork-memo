#!/bin/bash
set -eu

# ローカルHTTPサーバーにメッセージを送信するスクリプト

PORT=${PORT:-8080}
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
MESSAGE="${1:-テストメッセージ} [$TIMESTAMP]"

echo "メッセージを送信しています..."
echo "  URL: https://us-west1-nobu5-393106.cloudfunctions.net/chatwork-memo?message=${MESSAGE}"
echo "  メッセージ: $MESSAGE"

# GETリクエストでメッセージを送信
curl -X GET "https://us-west1-nobu5-393106.cloudfunctions.net/chatwork-memo?message=${MESSAGE}" \
    -v

echo ""
echo "送信完了"
