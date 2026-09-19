## COACHTECH Bookshelf

書籍の登録・検索・レビュー・お気に入り管理ができるLaravel製の書籍管理アプリです。  
ユーザーは書籍の閲覧やレビュー投稿に加え、読書計画の作成、期限通知、読書レポートによる統計情報の確認ができます。

## 作成者

氏名（久保遥）

## 使用技術

- PHP 8.5
- Laravel 10.50.2
- MySQL 8.4
- Docker / Docker Compose / Laravel Sail
- Vite
- Tailwind CSS 3.4.19
- Alpine.js
- Laravel Fortify（Web認証）
- Laravel Sanctum（API認証）
- Google Books API（ISBN検索）
- PHPUnit（テスト）

## 開発環境URL

http://localhost

## 動作環境

Docker
Docker Compose
※ Windowsの場合はWSL2の利用を推奨します。

## 環境構築手順

### 1. リポジトリをクローン

```bash
git https://github.com/haruka05-uni/mockcase-01-bookshelf.git
cd mockcase-01-bookshelf
```

### 2. Composer依存パッケージをインストール

初回セットアップ時は`vendor`ディレクトリが存在せず、Laravel Sailを使用できないため、Dockerを利用してComposerを実行します。

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. 環境設定ファイルを作成

```bash
cp .env.example .env
```

`.env`のデータベース接続情報を確認します。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

`DB_HOST`には、`localhost`ではなくDockerのサービス名である`mysql`を指定します。

### 4. Google Books APIキーを設定

Google Cloud ConsoleでGoogle Books APIを有効化してAPIキーを取得し、`.env`に設定します。

```env
GOOGLE_BOOKS_API_KEY=取得したAPIキー
```

APIキーはISBN検索機能で使用します。

### 5. Laravel Sailを起動

```bash
./vendor/bin/sail up -d
```

### 6. アプリケーションキーを生成

```bash
./vendor/bin/sail artisan key:generate
```

### 7. データベースを構築

マイグレーションを実行し、初期データを投入します。

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 8. フロントエンド環境を構築

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

開発中は`npm run dev`を起動したままにしてください。

### 9. アプリケーションへアクセス

ブラウザから以下へアクセスします。

- アプリケーション：[http://localhost](http://localhost)
- phpMyAdmin：[http://localhost:8080](http://localhost:8080)

※ phpMyAdminを使用しない構成の場合は、phpMyAdminの記載を削除してください。

### 10. リマインダー処理を実行

読書計画の期限通知を手動で実行する場合は、以下のコマンドを使用します。

```bash
./vendor/bin/sail artisan app:process-reading-plan-reminders
```

スケジューラーをローカル環境で継続的に動かす場合は、別のターミナルで以下を実行します。

```bash
./vendor/bin/sail artisan schedule:work
```

## 機能一覧

- ユーザー認証（会員登録、ログイン、ログアウト）
- 書籍の登録・詳細表示・編集・削除
- ISBN検索による書籍情報の自動入力
- 書籍検索（タイトル・著者）
- ジャンルによる絞り込み
- 書籍の並び替え
- ページネーション
- ジャンル管理（登録・詳細表示・編集・削除）
- レビューの投稿・編集・削除
- レビューへのいいね
- 書籍のお気に入り登録・一覧表示
- 書籍ランキング
- 読書計画の登録・編集・削除・読了管理
- 読書計画のステータス絞り込み
- 読書期限のリマインダー通知
- 通知一覧表示・既読管理
- マイ読書レポート
- 公開API（書籍CRUD）

## ページの公開範囲

### 認証不要

- 書籍一覧（トップページ）
- 書籍詳細
- 書籍ランキング
- 会員登録
- ログイン

### 認証が必要

- 書籍の登録・編集・削除
- レビューの投稿・編集・削除
- レビューへのいいね
- お気に入り登録・一覧表示
- ジャンル管理
- 読書計画
- 通知一覧
- マイ読書レポート

## APIエンドポイント一覧

書籍情報を取得・操作するAPIです。  
すべてのエンドポイントは `/api/v1` プレフィックス配下に定義されています。

書籍一覧・書籍詳細の取得は認証不要です。  
書籍の登録・更新・削除には、Laravel Sanctumによる認証が必要です。

| メソッド | エンドポイント         | 内容           | 認証 |
| -------- | ---------------------- | -------------- | ---- |
| GET      | `/api/v1/books`        | 書籍一覧を取得 | 不要 |
| GET      | `/api/v1/books/{book}` | 書籍詳細を取得 | 不要 |
| POST     | `/api/v1/books`        | 書籍を登録     | 必要 |
| PUT      | `/api/v1/books/{book}` | 書籍を更新     | 必要 |
| DELETE   | `/api/v1/books/{book}` | 書籍を削除     | 必要 |
