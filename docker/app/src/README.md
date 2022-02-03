# SOZO-Backend

卒業 WS プロジェクト『SOZO』バックエンド開発班用リポジトリ

# 使用方法

### 前提条件

1. composer がインストール済であること
2. docker-sozo を clone 済で、コンテナが問題なく立ち上がること

### ターミナル操作(GitBash など)

1. プロジェクトフォルダ内の「src」フォルダに移動する
2. rm -rf \* で「src」フォルダ内のテストファイルを全て削除する
3. git clone [リポジトリ URL] . でリポジトリ内のファイルを clone する(.が無いと 1 階層上のフォルダが作成されてしまいます)
4. 「src」フォルダ内に「cache」「templates_c」のフォルダを作成する
5. cp .env.sample .env を実行し環境変数ファイルを作成する
6. (テキストエディタで).env に自身の環境に合わせた環境変数を定義する
7. composer install で必要なライブラリをインストールする(vendor フォルダが作成される)

あとは docker-compose.yml のある所で docker compose up すれば表示されるはず…です。

### 使用ブランチについて
main(本番ブランチ)
 └　develop(Pull Requestを経てmerge)
    └ BE-shamimi 担当K
    ├ ele　担当N
    ├ palawan　担当Y
