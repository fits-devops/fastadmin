#### 简介
官方连接 [itop api 接口文档](https://www.itophub.io/wiki/page?id=2_5_0%3Aadvancedtopics%3Arest_json)
iTop提供了一个REST/JSON接口，允许第三方应用程序与iTop进行远程交互以检索、创建或更新iTop对象。
这个接口基于一组简单的HTTP POST请求。传递到iTop并从iTop检索的数据使用UTF-8字符集在JSON中进行编码。
1. 使用post请求和 使用https 传输
2. 请求链接 <itop-root>/webservices/rest.php?version=1.3
3. 请求参数
   auth_user  用户名
   auth_pwd   密码
   json_data  参数json 格式

#### 几种接口类型
```php
   {
     "version": "1.0",
     "operations": [
       {
         "verb": "core/create",
         "description": "Create an object", // 只能创建一个
         "extension": "CoreServices"
       },
       {
         "verb": "core/update",
         "description": "Update an object", // 只能修改一个
         "extension": "CoreServices"
       },
       {
         "verb": "core/apply_stimulus",
         "description": "Apply a stimulus to change the state of an object", // 可以更新事件或者服务的分配
         "extension": "CoreServices"
       },
       {
         "verb": "core/get",
         "description": "Search for objects", // 可以查询多个或者一个
         "extension": "CoreServices"
       },
       {
         "verb": "core/delete",
         "description": "Delete objects",//删除一个
         "extension": "CoreServices"
       },
       {
         "verb": "core/get_related",
         "description": "Get related objects through the specified relation",
         "extension": "CoreServices"
       },
       {
         "verb": "core/check_credentials",
         "description": "Check user credentials", // 验证权限
         "extension": "CoreServices"
       }
     ],
     "code": 0,
     "message": "Operations: 7"
   }
```

#### code 码说明
| Value        | Constant           | Meaning  |
| ------------- |:-------------:| -----:|
| 0      | OK | 返回成功 |
| 1      | UNAUTHORIZED      |   缺少/错误的凭据或用户没有足够的权限来执行请求的操作 |
|2 | MISSING_VERSION      |    缺少参数“version” |
|3 | MISSING_JSON     |    缺少参数'json_data' |
|4 | INVALID_JSON      |    输入结构不是有效的JSON字符串 |
|5 | MISSING_AUTH_USER      |   缺少用户名 |
|6 | MISSING_AUTH_PWD      |    缺少参数'auth_pwd'或者您使用的是url登录类型，并且您的iTop的配置文件中不允许使用它 |
|10 |UNSUPPORTED_VERSION     |   指定版本没有可用的操作 |
|11 | UNKNOWN_OPERATION      |   请求的操作对指定的版本无效 |
|12 | UNSAFE     |    无法执行请求的操作，因为它可能导致数据（完整性）丢失 |
|100 | INTERNAL_ERROR      |    无法执行操作，请参阅故障排除消息 |

#### 请求和返回参数说明
```php
   1. 请求参数
   Passing the following json_data:
   {
      "operation": "core/get",
      "class": "Person",
      "key": "SELECT Person WHERE email LIKE '%.com'",
      "output_fields": "friendlyname, email"
   }
   or, using another form of “key”:
   {
      "operation": "core/get",
      "class": "Person",
      "key": 1,
      "output_fields": "*"
   }
   2. 返回参数
   {
     "objects": {
       "UserRequest::54": {
         "code": 0,
         "message": "",
         "class": "UserRequest",
         "key": "54",
         "fields": {
           "operational_status": "ongoing",
           "ref": "R-000054",
           "org_id": "2",
           "org_name": "丰德科技-运维服务部"
         }
       }
     },
     "code": 0,
     "message": "Found: 1"
   }    
```


#### php 接口函数 这里是以php 语言举例子
```php 
   // curl  函数
   function curl_post($url, $postdata)
   {
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_POST, true);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
       $result = curl_exec($curl);
       return $result;
   }

```
#### 查询接口
```php
    http://new.imanager.com 网站的地址
    $url = 'http://new.imanager.com/webservices/rest.php?version=1.3';
    $ITOP_USER = 'admin';
    $ITOP_PWD  = 'admin';
    //查询例子 
    $payload = array(
            'operation' => 'core/get', // 查看操作
            'class' => 'WebApplication', // 对象类
            'key' => 'SELECT WebApplication WHERE id IN(10,30,31)', // oql 语句 或者 数字 (id) 10
            'output_fields' => 'id,organization_name', //返回的字段
        );
    $data = array(
            'auth_user' => $ITOP_USER,
            'auth_pwd' => $ITOP_PWD,
            'json_data' => json_encode($payload),
        );
    $response = curl_post($url,$data); // 自定义方法
    $decoded_response = json_decode($response, true);
    if ($decoded_response === false) {
        echo "Error: " . print_r($response, true) . "\n";
    } else if ($decoded_response['code'] != 0) {
        echo $decoded_response['message'] . "\n";
    } else {
        print_r($decoded_response);
        echo "return success.\n";
    }
```
#### 新增接口
```php
    $url = 'http://new.imanager.com/webservices/rest.php?version=1.3';
    $ITOP_USER = 'admin';
    $ITOP_PWD  = 'admin';
    $payload = array(
              'operation' => 'core/create',
              'class' => 'UserRequest',
               'fields' => array(  // 新增字段
                    'org_id' => '2', // 组织id 或者通过 oql 语句查询出来org_id
                    'title' => '申请账号',
                    'description' => '新入职',
                    'functionalcis_list' => array(
                        array('functionalci_id' => '2', 'impact_code' => 'manual'),
                    ),
                ),
                'comment' => '新创建的服务',
                'output_fields' => 'id', //返回的值 'output_fields' => '*' 返回所有
     );
            
    $data = array(
            'auth_user' => $ITOP_USER,
            'auth_pwd' => $ITOP_PWD,
            'json_data' => json_encode($payload),
        );
    $response = curl_post($url,$data); // 自定义方法
    $decoded_response = json_decode($response, true);
    if ($decoded_response === false) {
        echo "Error: " . print_r($response, true) . "\n";
    } else if ($decoded_response['code'] != 0) {
        echo $decoded_response['message'] . "\n";
    } else {
        print_r($decoded_response);
        echo "return success.\n";
    }
```
#### 更新接口
```php
    $url = 'http://new.imanager.com/webservices/rest.php?version=1.3';
    $ITOP_USER = 'admin';
    $ITOP_PWD  = 'admin';
    $payload = array(
               'operation'=>"core/update",
               'class' =>"UserRequest",  //事件
               "comment" => "Synchronization from blah...",
               "key"=>'59', // 事件ID
               "output_fields"=> "friendlyname, title, status,id",
               "fields"=>array( "title"=> 'new title') // title 更新为 new title
          );
       
    $data = array(
            'auth_user' => $ITOP_USER,
            'auth_pwd' => $ITOP_PWD,
            'json_data' => json_encode($payload),
        );
    $response = curl_post($url,$data); // 自定义方法
    $decoded_response = json_decode($response, true);
    if ($decoded_response === false) {
        echo "Error: " . print_r($response, true) . "\n";
    } else if ($decoded_response['code'] != 0) {
        echo $decoded_response['message'] . "\n";
    } else {
        print_r($decoded_response);
        echo "return success.\n";
    }
```
#### 删除接口
```php
    $url = 'http://new.imanager.com/webservices/rest.php?version=1.3';
    $ITOP_USER = 'admin';
    $ITOP_PWD  = 'admin';
     $payload = array(
            'operation'=>"core/delete",
            'class' =>"UserRequest",  
            'comment' =>'Synchronization from blah...',
            "key"=>'59', // 删除id=59 的请求事件
            "simulate"=>false,
         );
       
    $data = array(
            'auth_user' => $ITOP_USER,
            'auth_pwd' => $ITOP_PWD,
            'json_data' => json_encode($payload),
        );
    $response = curl_post($url,$data); // 自定义方法
    $decoded_response = json_decode($response, true);
    if ($decoded_response === false) {
        echo "Error: " . print_r($response, true) . "\n";
    } else if ($decoded_response['code'] != 0) {
        echo $decoded_response['message'] . "\n";
    } else {
        print_r($decoded_response);
        echo "return success.\n";
    }
```
####分配请求接口
```php
    $url = 'http://new.imanager.com/webservices/rest.php?version=1.3';
    $ITOP_USER = 'admin';
    $ITOP_PWD  = 'admin';
     //事件 或是服务 分配 和重新分配
     //将事件 id 为 60 的事件 分配给 团队id 21 中 处理人 id 10
    $payload = array(
         'operation'=>"core/apply_stimulus",
         'class' =>"Incident",  //事件
         'comment' =>'Synchronization from blah...',
         "key"=>'60',
         "stimulus"=>"ev_assign", //分配 ev_assign  重新分配 ev_reassign
         "output_fields"=> "friendlyname, title, status,id",
         "fields"=>array( "team_id"=> 21, //团队id
             "agent_id"=>10) //处理人id
      );
    $data = array(
            'auth_user' => $ITOP_USER,
            'auth_pwd' => $ITOP_PWD,
            'json_data' => json_encode($payload),
        );
    $response = curl_post($url,$data); // 自定义方法
    $decoded_response = json_decode($response, true);
    if ($decoded_response === false) {
        echo "Error: " . print_r($response, true) . "\n";
    } else if ($decoded_response['code'] != 0) {
        echo $decoded_response['message'] . "\n";
    } else {
        print_r($decoded_response);
        echo "return success.\n";
    }

```