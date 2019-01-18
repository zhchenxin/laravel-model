<?php

namespace Zhchenxin\Model;

use Illuminate\Database\Eloquent\Builder;

trait ExternalField
{
    /**
     * 字段预加载配置
     */
    private static $field_relations = [];

    /**
     * 通用方法关联查询部分字段信息
     * @param Builder $query
     * @param $relation
     * @param $columns
     * @return Builder
     */
    public function scopeWithOnly($query, $relation, Array $columns)
    {
        return $query->with([$relation => function ($query) use ($columns) {
            $query->select(array_merge([], $columns));
        }]);
    }

    /**
     * 将资源转换成数组，可以指定需要的字段
     * @param string $field 需要的字段，如果为空，则返回所有字段
     * @return array
     */
    public function toResource($field = '')
    {
        $map = [];
        $fields = static::_getFields($field);
        foreach ($fields as $value) {
            if (ends_with($value, 'date')) {
                $map[$value] = $this->$value->toDateTimeString();
            } else {
                $map[$value] = $this->$value;
            }
        }
        return $map;
    }

    /**
     * 从数据库中只查询需要的字段，其他字段不查询
     * @param string $field 多个字段使用逗号分隔，如果为空，则返回所有字段
     * @return Builder
     */
    public static function queryWithColumns($field = '')
    {
        $query = static::query();

        // 获取所有获取的字段所对应的数据库字段
        $relations = [];
        $fields = static::_getFields($field);
        foreach ($fields as $value) {
            $relations =  array_merge($relations, static::$field_relations[$value]);
        }

        // 没有关联关系的字段
        $fields = array_filter($relations, function($item) {
            return strpos($item, '.') === false;
        });
        $query->select(array_unique($fields));

        // 存在关联关系的字段
        $withDic = [];
        foreach ($relations as $relation) {
            if (strpos($relation, '.') === false) {
                continue;
            }
            $lastIndex = strrpos($relation, '.');
            $with = substr($relation, 0, $lastIndex);
            $field = substr($relation, $lastIndex+1, strlen($relation));
            if (isset($withDic[$with])) {
                $withDic[$with][] = $field;
            } else {
                $withDic[$with] = [$field];
            }
        }
        foreach ($withDic as $key => $val) {
            $query->withOnly($key, array_unique($val));
        }

        return $query;
    }

    private static function _getFields($field)
    {
        $all_field = array_keys(static::$field_relations);
        if (empty($field)) {
            $fieldArray = $all_field;
        } else {
            $fieldArray = explode(',', $field);
            $fieldArray = array_filter($fieldArray, function($item) use ($all_field) {
                return in_array($item, $all_field);
            });
        }
        return $fieldArray;
    }

}