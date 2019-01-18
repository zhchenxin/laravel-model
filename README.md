## 初始化

运行命令：

```sh
composer require zhchenxin/laravel-model
```

## FilterAttributes

FilterAttributes 是一个扩展现有model的一个 trait，主要用于优化关联查询的SQL。

使用例子：

```php
# Customer.php
class Customer extends Model
{
    use FilterAttributes;

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'update_date';

    protected static $field_relations = [
        'id'              => ['id'],
        'topic_id'        => ['topic_id'],
        'name'            => ['name'],
        'api'             => ['api'],
        'timeout'         => ['timeout'],
        'attempts'        => ['attempts'],
        'create_date'     => ['create_date'],
        'update_date'     => ['update_date'],

        'topic_name'        => ['topic_id', 'topic.id','topic.name'],
    ];

    protected $table = 'customer';

    public function topic()
    {
        return $this->hasOne(Topic::class, 'id', 'topic_id');
    }

    public function getTopicNameAttribute()
    {
        return object_get($this, 'topic.name');
    }
}
```

```php
# Topic.php
class Topic extends Model
{
    use ExternalField;

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'update_date';

    protected static $field_relations = [
        'id'          => ['id'],
        'name'        => ['name'],
        'description' => ['description'],
        'create_date' => ['create_date'],
        'update_date' => ['update_date'],
    ];

    protected $table = 'topic';

}
```

在 model 类中，著需要增加 `$field_relations` 字段，就可以实现以下效果：

```php
$first = Customer::queryWithColumns('id,name,topic_name,create_date')->where('id', 2)->first();

$first['topic_name'] = $first['topic']['name'];

// 属性输出
$first->toResource('id,name,topic_name');
/**
[
    'id': 1,
    'name': '111',
    'topic_name': '222'
]
*/
```

也可以实现集合效果

```php
$list = Customer::queryWithColumns('id,name,topic_name,create_date')->get();

// 属性输出
$list->toResource('id,name,topic_name');
/**
[
    [
        'id': 1,
        'name': '111',
        'topic_name': '222'
    ],
    [
        'id': 2,
        'name': '111',
        'topic_name': '222'
    ]
]
*/
```
