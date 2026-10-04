# 環境設定ガイド

本アプリケーションでは、Chatwork API の設定を **Google Cloud Firestore** から取得します。

## 1. Google Cloud Firestore の準備

Firestore 内に以下のデータ構造を準備してください。

- **コレクション名**: `chatwork-memo`
- **ドキュメント名**: `config`
- **フィールド**:
  - `api_token`: (string) Chatwork API のアクセストークン
  - `room_id`: (string) メッセージを送信するルームの ID

## 2. 認証設定

Firestore への接続には、Application Default Credentials (ADC) または GCP 環境のデフォルト認証を使用します。

Google Cloud サービス（Cloud Functions や Cloud Run 等）上では、適切な権限を持つ IAM サービスアカウントがアタッチされていれば自動的に認証されます。

ローカル開発環境や非 GCP 環境で実行する場合は、以下のように ADC (Application Default Credentials) を設定してください。

- `GOOGLE_APPLICATION_CREDENTIALS`: サービスアカウントキー (JSON) ファイルのパス

### 設定方法の例 (Docker/docker-compose.yml)

```yaml
services:
  php:
    image: php:8.2-apache
    environment:
      GOOGLE_APPLICATION_CREDENTIALS: "/app/credentials/service-account-key.json"
```

### 設定方法の例 (ローカル環境 / シェル)

```bash
export GOOGLE_APPLICATION_CREDENTIALS="/path/to/service-account-key.json"
```

## 注意事項
- **セキュリティ**: サービスアカウントキー (JSON) や API トークンなどの機密情報は、絶対にバージョン管理システム（Git等）にコミットしないでください。
- アプリケーション起動時に「Firestore からの設定取得に失敗しました」というエラーが表示される場合は、GCP 権限または `GOOGLE_APPLICATION_CREDENTIALS` の設定を確認してください。
