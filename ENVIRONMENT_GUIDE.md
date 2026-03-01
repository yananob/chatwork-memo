# 環境設定ガイド

本アプリケーションでは、Chatwork API の設定を **Google Cloud Firestore** から取得します。

## 1. Google Cloud Firestore の準備

Firestore 内に以下のデータ構造を準備してください。

- **コレクション名**: `config`
- **ドキュメント名**: `chatwork`
- **フィールド**:
  - `api_token`: (string) Chatwork API のアクセストークン
  - `room_id`: (string) メッセージを送信するルームの ID

## 2. 環境変数の設定

Firestore への接続のために、以下のいずれかの環境変数が必要です。

- `FIRESTORE_KEY_JSON`: サービスアカウントキーの JSON 文字列
- `FIRESTORE_KEY_PATH`: サービスアカウントキーファイルへの絶対パス

### 設定方法の例 (Docker/docker-compose.yml)

```yaml
services:
  php:
    image: php:8.2-apache
    environment:
      # JSON文字列として設定する場合
      FIRESTORE_KEY_JSON: '{"type": "service_account", "project_id": "...", ...}'
      # またはファイルパスを指定する場合
      # FIRESTORE_KEY_PATH: "/app/credentials/service-account-key.json"
```

### 設定方法の例 (Apache / .htaccess)

```apache
SetEnv FIRESTORE_KEY_PATH "/var/www/credentials/service-account-key.json"
```

## 注意事項
- **セキュリティ**: サービスアカウントキー（JSON）や API トークンなどの機密情報は、絶対にバージョン管理システム（Git等）にコミットしないでください。
- アプリケーション起動時に「環境変数が設定されていません」または「Firestore からの設定取得に失敗しました」というエラーが表示される場合は、上記の設定を確認してください。
