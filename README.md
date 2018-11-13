### Yii载入wyvue

1、创建用户表
```
php yii migrate --migrationPath=@console/migrations
```

2、创建rbac权限表
> 需要在console的配置文件中临时添加下面配置

```
'authManager' => ['class' => 'yii\rbac\DbManager']
```

```
php yii migrate --migrationPath=@yii/rbac/migrations
```

3、后台backend的配置文件

```
'modules'             => [
    'wyvue' => ['class' => 'wyvue\Module']
],
'components' => [
    'request' => [
        'enableCsrfValidation'   => false,
        'enableCookieValidation' => false,
        'parsers' => ['application/json' => 'yii\web\JsonParser'],
    ],
    'response' => [
        'format' => \yii\web\Response::FORMAT_JSON,
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
    'authManager'  => ['class' => 'yii\rbac\DbManager'],
    'urlManager'   => [
        'enablePrettyUrl' => true,
        'showScriptName'  => false,
        'rules'           => []
    ],
],
```

4、若没有通过composer包加载，需要手动添加该模块

> selfModule/wyvue为wyvue的文件夹的路径，然后执行`composer dump-autoload`

```
    "autoload": {
        "psr-4": {
            "wyvue\\": "selfModule/wyvue"
        }
    },
```

5、vueview的相关配置

+build配置
```
// vueview/config目录下的index.js
build: {
    // 设置首页的路径，必须定位到backend/web的目录下
    index: path.resolve(__dirname, '../backend/web/index.html'),

    // 设置资源的根路径
    assetsRoot: path.resolve(__dirname, '../backend/web/vuedist'),
    // 设置子路径
    assetsSubDirectory: 'static',
    // 设置公共路径
    assetsPublicPath: 'vuedist/',
}
```

+接口配置
```
// vueview/config目录下的dev.env.js、test.env.js和prod.env.js
BASE_API: '"http://magicmovie.com/backend/web"'
```

+运行方法

```
// 开发环境
npm install
npm run dev
// 测试环境
npm install
npm run build:test
// 生存环境
npm install
npm run build:prod
```



