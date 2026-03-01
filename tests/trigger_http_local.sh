#!/bin/bash
set -eu

# ローカルHTTPサーバーにメッセージを送信するスクリプト

PORT=${PORT:-8080}
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
MESSAGE="${1:-テストメッセージ} [$TIMESTAMP]"
COOKIE_JAR="/tmp/chatwork_session_$$.txt"

# クリーンアップ関数
cleanup() {
    rm -f "$COOKIE_JAR"
}
trap cleanup EXIT

# GETリクエストでページを取得し、セッションクッキーを保存し、CSRFトークンを抽出
RESPONSE=$(curl -s -c "$COOKIE_JAR" http://localhost:${PORT}/)
CSRF_TOKEN=$(echo "$RESPONSE" | grep -oP 'name="csrf_token"\s+value="\K[^"]+' | head -1)

if [ -z "$CSRF_TOKEN" ]; then
    echo "エラー: CSRFトークンが見つかりませんでした。" >&2
    exit 1
fi

echo "メッセージを送信しています..."
echo "  URL: http://localhost:${PORT}/"
echo "  メッセージ: $MESSAGE"
echo "  CSRFトークン: $CSRF_TOKEN"

# POSTリクエストでメッセージを送信（クッキーを使用してセッションを保持）
curl -X POST http://localhost:${PORT}/ \
    -b "$COOKIE_JAR" \
    -d "message=${MESSAGE}" \
    -d "csrf_token=${CSRF_TOKEN}" \
    -v

echo ""
echo "送信完了"
