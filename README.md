# 勤怠アプリ
以下の手順に従って、Laravelアプリケーションのセットアップを行ってください。

## 環境構築

### 1. インストールディレクトリへの移動
まず、プロジェクトをインストールするディレクトリに移動します。

```bash
$ cd coachtech/laravel
```

### 2. リポジトリのクローン
次に、リポジトリをクローンします。

```bash
$ git clone git@github.com:takashimomose/mock-project02.git
```

### 3. Dockerコンテナの作成
クローンしたプロジェクトディレクトリへ移動し、Dockerコンテナをビルドおよび起動します。

```bash
$ cd mock-project02
$ docker-compose up -d --build
```

### 4. PHPコンテナへのアクセス
PHPコンテナに入るには、以下のコマンドを実行します。

```bash
$ docker-compose exec php bash
```

### 5. Composerのインストール
コンテナ内でComposerの依存関係をインストールします。

```bash
$ composer install
```

### 6. .envファイルの作成
既に存在している.env.exampleファイルを利用して.envファイルを作成します。以下のコマンドを実行して.envファイルを作成してください。

```bash
$ cp .env.example .env
```

### 7. .envファイルの編集
.envファイルを編集し、DB接続情報を以下のように設定してください。

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
同様に.envファイル内のメール接続情報を以下のように設定してください。
MAIL_USERNAMEとMAIL_PASSWORDは自身のMailtrapアカウントのダッシュボードで確認し、入力します。
Mailtrapダッシュボード：https://mailtrap.io/home

```bash
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=あなたのメールアドレス
MAIL_PASSWORD=あなたのメールアカウントのパスワード
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=mock-project01@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 8. データベースの確認
docker-compose.ymlで設定したphpMyAdminにアクセスし、データベースが存在しているかを確認します。

phpMyAdmin URL: http://localhost:8080/

### 9. アプリケーションキーの生成
以下のコマンドを実行してアプリケーションキーを生成してください。

```bash
$ php artisan key:generate
```

### 10. DB内にテーブルを作成
以下のコマンドを実行してDBにテーブルを作成してください。

```bash
$ php artisan migrate
```

### 11. DBテーブルにデータを挿入
以下のコマンドを実行してDBテーブルにデータを挿入してください。

```bash
$ php artisan db:seed
```

### 12. アプリケーションへのアクセス
アプリケーションにアクセスするには、以下のURLにアクセスします。

アプリケーション URL: http://localhost/
もしアプリケーションにアクセスできない場合、以下のコマンドを実行してパーミッションを修正してください。

```bash
$ sudo chmod -R 777 src/*
```

```bash
$ php artisan storage:link
```

### 13.再度動作確認
再度ブラウザで以下にアクセスし、以下の画面が正しく表示されていることを確認してください。
ログイン画面：http://localhost/login
管理者ログイン画面：http://localhost/admin/login

以下のテストユーザーがDBに作成済みのためログインに使用可能です。

```bash
テスト一般ユーザー
メールアドレス：testuser@example.com
パスワード：12345678

テスト一般ユーザー2
メールアドレス：testuser2@example.com
パスワード：12345678

管理者
メールアドレス：testadmin@example.com
パスワード：12345678
```

以上でセットアップは完了です。

## 使用技術
- PHP 7.4.9
- Laravel 8.83.29
- MySQL 15.1

## URL
- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/

## その他
PHPUnitテストの実行には、以下のコマンドを使用してください。

・全テストを実行する場合：

```bash
vendor/bin/phpunit
```
もしくは

```bash
php artisan test --testsuite=Feature
```

・特定のテストファイルを実行する場合：

```bash
vendor/bin/phpunit tests/Unit/<ファイル名>
```

もしくは

```bash
php artisan test tests/Feature/<ファイル名>
```
