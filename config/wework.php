<?php

return [
    /**
     * 账号基本信息，请从企业微信管理后台获取
     */
    'corp_id' => env('CORP_ID', ''),
    'agent_id' => env('CORP_AGENT_ID', ''),
    'secret' => env('CORP_AGENT_SECRET', ''),

    /**
     * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
     * 使用自定义类名时，构造函数将会接收一个 `EasyWeChat\Kernel\Http\Response` 实例
     */
    'response_type' => 'array',

    /**
     * 日志配置
     *
     * level: 日志级别, 可选为：
     *         debug/info/notice/warning/error/critical/alert/emergency
     * path：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log' => [
        'default' => env('APP_ENV', 'development'), // 默认使用的 channel，生产环境可以改为下面的 production
        'channels' => [
            'development' => [
                'driver' => 'single',
                'path' => storage_path('logs/wechat.log'),
                'level' => 'debug',
            ],
            'production' => [
                'driver' => 'daily',
                'path' => storage_path('logs/wechat.log'),
                'level' => 'info',
            ],
        ],
    ],

    /**
     * 接口请求相关配置，超时时间等，具体可用参数请参考：
     * http://docs.guzzlephp.org/en/stable/request-config.html
     *
     * - retries: 重试次数，默认 1，指定当 http 请求失败时重试的次数。
     * - retry_delay: 重试延迟间隔（单位：ms），默认 500
     * - log_template: 指定 HTTP 日志模板，请参考：https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php
     */
    'http' => [
        'max_retries' => 1,
        'retry_delay' => 500,
        'timeout' => 5.0,
        // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
    ],
];