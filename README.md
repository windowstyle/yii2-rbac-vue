### Yii2前后端分离

一、下载Yii2框架
下载地址：https://github.com/yiisoft/yii2-app-advanced/releases

1、修改composer.json，在文件末尾修改repositories为国内源，并忽略前端资源的包，然后`composer install`

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://packagist.phpcomposer.com"
        }
    ],
    "provide": {
        "bower-asset/jquery": "*",
        "bower-asset/bootstrap": "*",
        "bower-asset/inputmask": "*",
        "bower-asset/punycode": "*",
        "bower-asset/typeahead.js": "*",
        "bower-asset/yii2-pjax": "*"
    }
}
```
2、修改common/main.php，全部采用npm来管理前端资源

```php
'aliases' => [
    '@bower' => dirname(dirname(__DIR__)) . '/vueview/node_modules',
    '@npm'   => dirname(dirname(__DIR__)) . '/vueview/node_modules',
]
```
3、修改backend下的gii配置文件，只有开发环境下，并且是gii请求时，才加载该模块，并删除项目自带的debug模块
```php
if (YII_ENV_DEV && strpos($_SERVER['REQUEST_URI'],'/gii') !== false) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
```
二、配置Yii2后端

1、创建用户表与rbac权限表
> 需要在console的配置文件中临时添加下面配置，创建rbac要用到，结束后删除掉

```php
'authManager' => ['class' => 'yii\rbac\DbManager']
```

```
php yii migrate --migrationPath=@console/migrations
php yii migrate --migrationPath=@yii/rbac/migrations
```

2、后台backend的main.php配置文件如下
> wyvue\Module模块名必须定义为wyvue，否则需要修改前端固定的Api请求地址

```php
'modules' => ['wyvue' => 'wyvue\Module'],
'components' => [
    'request' => [
        'enableCsrfValidation'   => false,
        'enableCookieValidation' => false,
        'parsers'                => ['application/json' => 'yii\web\JsonParser'],
    ],
    'response' => [
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            /* @var $response \yii\web\Response */
            if ($response->statusCode != 200) {
                $response->statusCode = 200;
                $response->data = [
                    'code'    => \wyvue\models\RespCode::ERROR_EXCEPTION,
                    'data'    => 'Exception: ' . \yii\helpers\ArrayHelper::getValue($response->data,'message','Unknow Error!')
                ];
            }
        }
    ],
    'user' => [
        'identityClass'   => 'wyvue\models\UserModel',
        'enableAutoLogin' => false,
        'enableSession'   => false,
    ],
    'log'          => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets'    => [
            [
                'class'  => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'errorHandler' => ['errorAction' => 'site/error'],
    'authManager'  => ['class' => 'yii\rbac\DbManager'],
    'urlManager'   => [
        'enablePrettyUrl' => true,
        'showScriptName'  => false,
        'rules'           => []
    ],
],
```

3、修改backend\controllers\SiteController，使后台可以直接跳转到前端首页

```php
public function actionIndex()
{
    header('Location:'.Url::to(['/index.html'],true));exit; // 跳转到前端页面
}
```

三、vueview的相关配置

1、下载nodejs，并设置npm为国内的源
> windows环境的下载地址：http://nodejs.cn/download/
```
npm install -g cnpm --registry=https://registry.npm.taobao.org
```

2、部署vueview
> 从vendor包wyanlord中拷贝vueview文件夹到项目的根目录下，与backend处于同一级别

3、接口域名配置
> 路径为vueview/config目录下的dev.env.js和prod.env.js
```js
module.exports = {
    NODE_ENV: '"development"',
    BASE_API: '"http://localhost"'
}
```

4、开始运行
```
cnpm install
```

```
// 开发环境
npm run dev
// 生产环境
npm run build
```

5、创建管理员账号

> 访问接口API域名地址http://localhost/wyvue/login/register-default路由来创建管理员admin，同时会创建对应的角色

> 默认为admin/admin，权限为superadmin


