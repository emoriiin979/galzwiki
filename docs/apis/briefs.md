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

## store - 記事の登録

```
POST /briefs
```

### Requests

```
{
  'title': 'EC2',
  'note': 'Elastic Compute Cloud',
  'abstract': 'Amazonが提供する計算資源を用いて...',
  'hands_on': 'EC2の作成前に、VPCを準備します。VPCコンソールにアクセスし...',
  'parent_brief_id': 1,
  'entry_user_id': 1,
  'entry_at': '2023-12-23 12:34:56',
  'is_publish': true,
}
```

### Response `201`

```
{
  'message': '登録が完了しました。',
}
```

### Response `422`

```
{
  'errors': [
    'title' => [
      'titleは必ず指定してください。',
      'titleの値は既に存在しています。',
    ],
    'abstract' => ['abstractは必ず指定してください。'],
    'parent_brief_id' => [
      'parent brief idは必ず指定してください。',
      'parent brief idは整数で指定してください。',
    ],
    'entry_at' => [
      'entry atは必ず指定してください。',
      'entry atはY-m-d H:i:s形式で指定してください。',
    ],
    'entry_user_id' => [
      'entry user idは必ず指定してください。',
      'entry user idは整数で指定してください。',
    ],
    'is_publish' => [
      'is publishは必ず指定してください。',
      'is publishは、trueかfalseを指定してください。',
    ],
  ],
}
```

## show - 記事詳細の取得

```
GET /briefs/{id}
```

### Parameters

- id: `number` (required)

### Response `200`

```
{
  'id': 3,
  'title': 'EC2',
  'note': 'Elastic Compute Cloud',
  'abstract': 'Amazonが提供する計算資源を用いて...',
  'entry_user_id': 1,
  'entry_at': '2023-12-23 12:00:00',
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
}
```

### Response `404`

```
{
  'errors': [
    'データが存在しません',
  ],
}
```