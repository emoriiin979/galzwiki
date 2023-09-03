# 事項API

## エンドポイント一覧

||GET|POST|PATCH|DELETE|
|:--|:--|:--|:--|:--|
|/entries|index|store|||
|/entries/{id}|show||update|delete|

## index - 事項一覧の取得

```
GET /entries?keywords[0]=単語1&keywords[1]=単語2&operator=and&page=1
```

### Rules

- `公開中`かつ`投稿日時が当日以降`の事項のみ取得する
- ログイン中の場合は`自分が投稿した事項`も取得できる
- 1ページに取得するデータ数は`10件`とする（ページネーション機能を使う）
- ソート順は優先度が高い順に、`投稿日時`の降順、`更新日時`の降順とする

### Parameters

- keywords : `array` (optional)
  - `題名`、`補題`、`本文`について部分一致で検索する
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
      'subtitle': 'Elastic Compute Cloud',
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
    'first': 'https://emolab.jp/api/entries?page=1',
    'last': 'https://emolab.jp/api/entries?page=12',
    'prev': 'https://emolab.jp/api/entries?page=11',
    'next': '',
  },
  'meta': {
    'current_page': 12,
    'from': 111,
    'last_page': 12,
    'path': 'https://emolab.jp/api/entries',
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

## store - 事項の登録

```
POST /entries
```

### Requests

```
{
  'title': 'EC2',
  'subtitle': 'Elastic Compute Cloud',
  'body': 'Amazonが提供する計算資源を用いて...',
  'parent_entry_id': 1,
  'post_user_id': 1,
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
    'title': [
      'titleは必ず指定してください。',
      'titleの値は既に存在しています。',
    ],
    'body': [
      'bodyは必ず指定してください。'
    ],
    'parent_entry_id': [
      'parent entry idは必ず指定してください。',
      'parent entry idは整数で指定してください。',
    ],
    'post_user_id': [
      'post user idは必ず指定してください。',
      'post user idは整数で指定してください。',
    ],
    'is_publish': [
      'is publishは必ず指定してください。',
      'is publishは、trueかfalseを指定してください。',
    ],
  ],
}
```

## show - 事項詳細の取得

```
GET /entries/{id}
```

### Parameters

- id: `number` (required)

### Response `200`

```
{
  'id': 3,
  'title': 'EC2',
  'subtitle': 'Elastic Compute Cloud',
  'body': 'Amazonが提供する計算資源を用いて...',
  'post_user_id': 1,
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

## update - 事項の更新

```
PATCH /entries/{id}
```

### Requests

```
{
  'title': 'EC2',
  'subtitle': 'Elastic Compute Cloud',
  'body': 'Amazonが提供する計算資源を用いて...',
  'is_publish': true,
  'updated_at': '2023-12-23 12:34:56',
}
```

### Response `204`

```
(No Contents)
```

### Response `404`

```
{
  'message': 'データが存在しません。',
}
```

### Response `409`

```
{
  'message': '別ユーザーによってデータが更新されています。\nページをリロードして再度更新処理を実行してください。',
}
```

### Response `422`

```
{
  'errors': [
    'title': [
      'titleは必ず指定してください。',
      'titleの値は既に存在しています。',
    ],
    'body': [
      'bodyは必ず指定してください。',
    ],
    'is_publish': [
      'is publishは必ず指定してください。',
      'is publishは、trueかfalseを指定してください。',
    ],
    'updated_at': [
      'updated atは必ず指定してください。',
      'updated atはY-m-d H:i:s形式で指定してください。',
    ],
  ],
}
```