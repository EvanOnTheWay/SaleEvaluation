<?php

namespace App\Http;

/**
 * 预定义的响应状态数据
 *
 * @package App\Http
 */
class ResponseStatus
{
    public const SUCCESS = [100, "操作成功"];
    public const INVALID_PARAMETER = [101, "参数缺失或格式有误"];


}