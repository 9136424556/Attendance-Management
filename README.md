# coachtech 勤怠管理アプリ

## 概要
COACHTECHが提供する独自の勤怠管理システムです。会員登録していただくと、勤怠時間の管理、勤怠情報の修正申請、毎月の勤怠一覧を閲覧する機能がご利用可能になります。

## 作成した目的
ユーザーの勤怠と管理を目的としています。

## アプリケーションURL
### ローカル環境
http://localhost

## 機能一覧
・会員登録機能
・ログイン、ログアウト機能
・勤怠打刻機能
・勤怠一覧取得、確認機能
・勤怠詳細取得、修正申請機能
・申請確認機能
・管理者ログイン、ログアウト機能
・日時勤怠一覧取得機能(管理者)
・勤怠詳細確認、修正機能(管理者)
・スタッフ一覧確認機能(管理者)
・スタッフ別月次勤怠一覧取得機能(管理者)
・修正申請一覧取得、確認機能(管理者)
・修正申請の確認、承認機能(管理者)

## 使用技術
・Laravel 8.83.27
・nginx 1.21.1
・PHP 8.1.30
・Mysql 15.1
・Docker 27.2.0
・GitHub:https://github.com/9136424556/Attendance-Management
・node.js v12.22.9
・npm 8.5.1

## テーブル設計
![スクリーンショット 2024-11-20 225326](https://github.com/user-attachments/assets/f0c0381b-abad-40a0-8085-5b67a6756c5c)
![スクリーンショット 2024-11-20 225352](https://github.com/user-attachments/assets/b50f4379-1226-427c-8675-52b30a1aedc2)
![スクリーンショット 2024-11-20 225410](https://github.com/user-attachments/assets/7a229cef-9325-4ee5-b71c-081f702c2705)

## ER図
![スクリーンショット 2024-11-20 221516](https://github.com/user-attachments/assets/08cbbbd4-556f-4057-9417-b0b76736fe20)

## 環境構築
## 1 Gitファイルをクローンする
git clone git@github.com:9136424556/Attendance-Management

## 2 Dokerコンテナを作成する
docker-compose up -d --build

## 3 Laravelパッケージをインストールする
docker-compose exec php bash
でPHPコンテナにログインし
composer install

## 4 .envファイルを作成する
PHPコンテナにログインした状態で
cp .env.example .env
作成した.envファイルの該当欄を下記のように変更
DB_HOST=mysql
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

## 5 テーブルの作成
docker-compose exec php bash
でPHPコンテナにログインし(ログインしたままであれば上記コマンドは実行しなくて良いです。)
php artisan migrate

## 6 ダミーデータ作成
PHPコンテナにログインした状態で
php artisan db:seed

## 7 アプリケーション起動キーの作成
PHPコンテナにログインした状態で
php artisan key:generate

## その他
管理者アカウント(Seederに保存済み)
　メールアドレス　coachtech@coachtech.com
　パスワード　coachtech
 
一般ユーザーアカウント(Seederに保存済み)
　メールアドレス　staffuser@email.com 
  パスワード  staffuser1
