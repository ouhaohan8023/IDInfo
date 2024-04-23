## IDInfo - Laravel 中国身份证信息提取工具

### 1. 安装

```shell
composer require ouhaohan8023/id-info
```
### 2. 配置
```
php artisan vendor:publish --provider="Ouhaohan8023\IDInfo\IDInfoServiceProvider"

迁移   php artisan migrate
初始化  php artisan id-info:init
```

### 使用

```php
$id = '132235196706122646';
$m = IDInfoFacade::info($id);
dd($m);
dd($m->getProvince());

Ouhaohan8023\IDInfo\Model\IDInfoModel {
  +"province_code": 130000
  +"city_code": 132200
  +"area_code": 132235
  +"province": "xx省"
  +"city": "xx市"
  +"area": "xx县"
  +"age": 18
  +"sex": "男"
}
```
