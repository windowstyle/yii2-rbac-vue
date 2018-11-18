### Yii2前后端分离

一、下载Yii2框架
下载地址：https://github.com/yiisoft/yii2-app-advanced/releases

1、修改composer.json

> 在文件末尾修改repositories为国内源，并忽略前端资源的包

> 删除require-dev中的所有包，仅保留gii即可

> 在require末尾添加wyanlord/yii2-rbac-vue包

> 最后使用composer来更新这些包

```json
{
  "require": {
        "php": ">=5.4.0",
        "yiisoft/yii2": "~2.0.14",
        "yiisoft/yii2-bootstrap": "~2.0.0",
        "yiisoft/yii2-swiftmailer": "~2.0.0 || ~2.1.0",
        "wyanlord/yii2-rbac-vue": "^1.5"
    },
    "require-dev": {
        "yiisoft/yii2-gii": "~2.0.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://packagist.laravel-china.org"
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
    '@bower' => dirname(dirname(__DIR__)) . '/backview/node_modules',
    '@npm'   => dirname(dirname(__DIR__)) . '/backview/node_modules',
]
```
3、修改backend下的gii配置文件，只有开发环境下，并且是gii请求时，才加载该模块，并删除项目自带的debug模块
```php
if (YII_ENV_DEV && strpos($_SERVER['REQUEST_URI'],'/gii') !== false) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'crud' => ['class' => 'wyrbac\crud\Generator']
        ],
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
> wyrbac\Module模块名必须定义为wyrbac，否则需要修改前端固定的Api请求地址

```php
'modules' => ['wyrbac' => 'wyrbac\Module'],
'components' => [
    'request' => [
        'enableCsrfValidation'   => false,
        'enableCookieValidation' => false,
        'parsers'                => ['application/json' => 'yii\web\JsonParser'],
    ],
    'response' => [
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            if ($response->statusCode != 200) {
                $response->statusCode = 200;
                $response->format     = yii\web\Response::FORMAT_JSON;
                $response->data       = [
                    'code'    => \wyrbac\models\RespCode::ERROR_EXCEPTION,
                    'data'    => $response->data
                ];
            }
        }
    ],
    'user' => [
        'identityClass'   => 'wyrbac\models\UserModel',
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

3、修改backend\controllers\SiteController，仅保留error与index操作，其他都删除

```php
class SiteController extends Controller
{
    public function actionError(){
        // 仅当YII_DEBUG为false时有效
        $response = Yii::$app->getResponse();
        $response->data = 'Server Error.';
        return $response;
    }
    public function actionIndex(){
        header('Location:'.Url::to(['/index.html'],true));exit; // 跳转到前端页面
    }
}

```

三、backview的相关配置

1、下载nodejs
> windows环境的下载地址：http://nodejs.cn/download/

2、部署backview
> 从vendor包wyanlord中拷贝backview文件夹到项目的根目录下，与backend处于同一级别

3、接口域名配置
> 路径为backview/config目录下的dev.env.js和prod.env.js
```js
module.exports = {
    NODE_ENV: '"development"',
    BASE_API: '"http://localhost"'
}
```

4、开始运行
```
npm install --registry=https://registry.npm.taobao.org
```

```
// 开发环境
npm run dev
// 生产环境
npm run build
```

5、创建管理员账号

> 访问接口API域名地址http://localhost/wyrbac/login/register-default 路由来创建管理员admin，同时会创建对应的角色

> 默认为admin/admin，权限为superadmin

> 给角色配置路由权限的时候，如果增删了一些路由，要点击上面的‘更新路由’按钮进行路由刷新

6、使用gii生成crud前端

> model的生成还是Yii2官方的操作

> crud的生成如下

```
backend\models\TestModel // 这个是model
backend\controllers\TestController // 这个是控制器
backview\src\views // 这个是vue文件的文件夹目录路径
```

> 生成好了以后，需要在router里按照system路由的配置方法来配置你的路由，并添加到index.js里

