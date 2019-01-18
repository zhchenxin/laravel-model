<?php

namespace Zhchenxin\Model;


use Illuminate\Database\Eloquent\Collection;

class CustomCollection extends Collection
{
    public function toResource($field = '')
    {
        $ret = [];
        foreach ($this as $item) {
            $ret[] = $item->toResource($field);
        }
        return $ret;
    }
}