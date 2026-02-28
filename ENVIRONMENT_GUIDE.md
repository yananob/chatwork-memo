# 環境設定ガイド

本アプリケーションでは、Chatwork API を使用するために以下の環境変数が必要です。

- `CHATWORK_API_TOKEN`: Chatwork API のアクセストークン
- `CHATWORK_ROOM_ID`: メッセージを送信するルームの ID

以下に、主要なサーバー環境での設定方法を記載します。

## 1. Apache (`.htaccess` または仮想ホスト設定)

`.htaccess` ファイル、または Apache の設定ファイル（`httpd.conf`, `000-default.conf` 等）に以下を記述します。

```apache
SetEnv CHATWORK_API_TOKEN "あなたのAPIトークン"
SetEnv CHATWORK_ROOM_ID "ルームID"
```

## 2. Nginx + PHP-FPM

PHP-FPM の設定ファイル（通常は `/etc/php/8.x/fpm/pool.d/www.conf`）に以下を記述します。

```ini
env[CHATWORK_API_TOKEN] = 'あなたのAPIトークン'
env[CHATWORK_ROOM_ID] = 'ルームID'
```

設定後、PHP-FPM を再起動してください：
```bash
sudo systemctl restart php8.x-fpm
```

## 3. Docker (`docker-compose.yml`)

`docker-compose.yml` の `environment` セクションに記述します。

```yaml
services:
  php:
    image: php:8.2-apache
    environment:
      CHATWORK_API_TOKEN: "あなたのAPIトークン"
      CHATWORK_ROOM_ID: "ルームID"
```

## 4. Linux / macOS (一時的なテスト用)

シェルから直接設定して PHP のビルトインサーバーを起動する場合：

```bash
export CHATWORK_API_TOKEN="あなたのAPIトークン"
export CHATWORK_ROOM_ID="ルームID"
php -S localhost:8000
```

## 注意事項
- セキュリティのため、APIトークンなどの機密情報は Git などのバージョン管理システムにコミットしないよう注意してください。
- サーバー上で設定した後は、環境変数が正しく認識されているか、アプリケーションのエラー画面が表示されないことで確認できます。
