# laravel-eqxiu

基于laravel的易企秀内容中台

[![image](https://img.shields.io/github/stars/jiaoyu-cn/laravel-eqxiu)](https://github.com/jiaoyu-cn/laravel-eqxiu/stargazers)
[![image](https://img.shields.io/github/forks/jiaoyu-cn/laravel-eqxiu)](https://github.com/jiaoyu-cn/laravel-eqxiu/network/members)
[![image](https://img.shields.io/github/issues/jiaoyu-cn/laravel-eqxiu)](https://github.com/jiaoyu-cn/laravel-eqxiu/issues)

[内容中台技术手册 ](https://hc.eqxiu.cn/p/tech/)
## 安装

```shell
composer require githen/laravel-eqxiu:~v1.1.0

# 迁移配置文件
php artisan vendor:publish --provider="Githen\LaravelEqxiu\Providers\EqxiuServiceProvider"
```

## 配置文件说明

在config/logging.php中添加eqxiu日志配置项

```php
'eqxiu' => [
    'driver' => 'daily',
    'path' => storage_path('logs/eqxiu/eqxiu.log'),
    'level' => 'debug',
    'days' => 7,
    'permission' => 0770,
],
```        

生成`eqxiu.php`上传配置文件

```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | 易企秀配置
    |--------------------------------------------------------------------------
    |
    */
    // 产品秘钥
    'app_id' => '',
    'app_key' => '',
    // 产品秘钥
    'signature_key' => '',
    'encoding_key' => '',
    'log_channel' => 'eqxiu',//写入日志频道，空不写入
];
```
