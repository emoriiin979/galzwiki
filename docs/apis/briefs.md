# 記事API

## エンドポイント一覧

||GET|POST|PUT|DELETE|
|:--|:--|:--|:--|:--|
|/briefs|index|store|||
|/briefs/{id}|show||update|delete|

## index - 記事一覧の取得

```
GET /briefs?keywords[0]=単語1&keywords[1]=単語2&operator=and&page=1
```

### Rules

- `公開中`かつ`投稿日時が当日以降`の記事のみ取得する
- ログイン中の場合は`自分が投稿した記事`も取得できる
- 1ページに取得するデータ数は`10件`とする（ページネーション機能を使う）
- ソート順は優先度が高い順に、`投稿日時`の降順、`更新日時`の降順とする

### Parameters

- keywords : `array` (optional)
  - `タイトル`、`補足`、`概要文`、`ハンズオン`について部分一致で検索する
- operator : `string` (optional)
  - キーワード検索の検索方式（AND検索/OR検索）を指定できる
  - `and`または`or`のみ指定可能
  - 未指定の場合は`and`が自動選択される
- page : `number` (optional)
  - 未指定の場合は1ページ目のデータが取得される

### Response `200`

```
{
  'data': [
    {
      'id': 3,
      'title': 'EC2',
      'note': 'Elastic Compute Cloud',
      'entry_date': '2023-12-23',
      'is_publish': true,
      'parents': [
        {
          'id': 2,
          'title': 'AWS',
          'depth': -1,
        },
        {
          'id': 1,
          'title': 'IT',
          'depth': -2,
        },
      ],
    },
  ],
  'links': {
    'first': 'https://emolab.jp/api/briefs?page=1',
    'last': 'https://emolab.jp/api/briefs?page=12',
    'prev': 'https://emolab.jp/api/briefs?page=11',
    'next': '',
  },
  'meta': {
    'current_page': 12,
    'from': 111,
    'last_page': 12,
    'path': 'https://emolab.jp/api/briefs',
    'per_page': 10,
    'to': 111,
    'total': 111,
  },
}
```

### Response `422`

```
{
  'errors': [
    'keywords': ['keywordsは配列でなくてはなりません。'],
    'operator': ['operatorには「and」か「or」のいずれかを指定してください。'],
    'page': ['pageは整数で指定してください。'],
  ],
}
```
