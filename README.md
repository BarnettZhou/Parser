# Parser

## 安装

项目目录中运行如下命令

``` bash
composer require xuchen/parser
```

或在项目目录composer.json的require中加入

``` javascript
"require": {
    "xuchen/parser": "dev-master"
}
```

并运行

``` bash
composer update
```

## 使用

假设现在有三条用户数据需要被整理并返回给前端，数据如下：

``` javascript
[
    {
        id      : 0,
        name    : "Judy",
        gender  : "f",
        profile : {
            mobile  : "13012345678",
            type    : "police"
        }
    },
    {
        id      : 1,
        name    : "Nick",
        gender  : "m",
        profile : {
            mobile  : "15012345678",
            type    : "police"
        }
    },
    {
        id      : 2,
        name    : "Kevin",
        gender  : "m",
        profile : {
            mobile  : "18012345678",
            type    : "bodyguard"
        }
    }
]
```

预期返回的数据如下：

``` javascript
[
    {
        id      : 0,
        name    : "Judy",
        gender  : "女",
        mobile  : "130****5678"
    },
    {
        id      : 1,
        name    : "Nick",
        gender  : "男",
        mobile  : "150****5678"
    },
    {
        id      : 2,
        name    : "Kevin",
        gender  : "男",
        mobile  : "180****5678"
    }
]
```

在应用目录下新建`MyParser.php`文件：

``` php
<?php 

namespace App;


use Xuchen\Parser\Parser;
use Xuchen\Parser\Helper;

class MyParser extends Parser
{
    // Parser中提供的一些辅助方法
    use Helper;

    // 字段整理规则，为每一个字段设置一个回调函数
    protected function parseRules()
    {
        // 未设置回调函数的字段则直接返回原值或null
        return [
            'gender' => function($row) {
                // getValue()是父类中提供的一个获取hash-table中的值的方法
                $gender = $this->getValue($row, 'gender', 'm');
                return $gender == 'm'? '男' : '女';
            },
            'mobile' => function($row) {
                // getValueRecursively()是父类中提供的一个以递归方式获取多层hash-table中的值的方法
                $mobile = $this->getValueRecursively($row, 'profile.mobile', '');
                if ($mobile) {
                    // hideMobile()是Helper中提供的一个隐藏手机号的方法
                    return $this->helper->hideMobile($mobile);
                } else {
                    return $mobile;
                }
            }
        ];
    }

    // 单行数据的整理规则
    protected function parseSingleRow($row)
    {
        $newRow = [];
        
        $newRow['id']       = $this->getValue($row, 'id', 0);
        $newRow['name']     = $this->getValue($row, 'name', '');

        $newRow['gender']   = $this->getValue($row, 'gender', 'm') == 'm'? '男' : '女';

        $mobile = $this->getValueRecursively($row, 'profile.mobile', '');
        $newRow['mobile']   = $mobile? $this->hideMobile($mobile) : '';

        return $newRow;
    }
}
```

在逻辑代码中调用

``` php
<?php 

namespace App\Controllers;


use App\Repositories\MyRepository;
use App\MyParser;

class MyController
{
    public function index(MyRepository $repo)
    {
        // 假设数据已正确获取
        $rows = $repo->get();

        // 若使用依赖注入的方式实例化Parser，可以使用setRows($rows)方法将数据传递给Parser
        $parser = new MyParser($rows);

        // 方式一：使用parserWithRules()
        // 参数为预期返回的字段列表
        $parsedRows_a = $parser->parseWithRules(['id', 'name', 'gender', 'mobile']);

        // 方式二：使用parseRows
        // 参数为定义的整理单行数据的方法，默认为parseSingleRow
        $parsedRows_b = $parser->parseRows();

        // 方式一及方式二返回的数据相同
    }
}
```
