# **Starter** 基本模块 (**Required**)

该模块主要是提供系统的基本功能，包括基础用户认证登录、日志管理、字典管理、消息通知管理等。

## 模块安装

### 安装依赖

```bash
composer require jobsys/starter-module  --dev
```


### 配置

#### 模块配置

```php
"Starter" => [
    "route_prefix" => "manager",                        // 路由前缀
]
```

## 模块功能

### 用户认证登录

`用户认证登录`功能流程：`用户登录` => `选择角色` *（如果只有一个角色自动选定）* => `初始化权限信息` => `跳转到Dashboard`。

#### 开发规范

1. 可以根据业务需求自定义登录页面，默认登录页面的路由为 `/login`。
2. 默认的登录界面为 `Modules/Starter/Resources/views/web/NudePageLogin.vue`，登录用户名由 `config/conf.php`
   中的 `login_field` 定制，可以根据业务需求进行修改。

### 字典管理

字典管理功能提供了对系统中常用的字典进行管理，如：性别、状态、类型等，根据不同的系统功能进行初始化。

#### 开发规范

1. 字典管理的数据表为 `dictionaries`，数据模型为 `Modules\Starter\Entities\Dictionary`。
2. 在 `database/seeders/DictionariesSeeder.php` 的 `run` 方法中定义字典的初始化数据。
    ```php
   //性别类型
    if (!Dictionary::where('name', 'gender_type')->exists()) {
        $dict = Dictionary::create(['display_name' => '性别类型', 'name' => 'gender_type']);
        $dict->items()->insert([
            ['dictionary_id' => $dict->id, 'display_name' => '男', 'value' => '男'],
            ['dictionary_id' => $dict->id, 'display_name' => '女', 'value' => '女'],
        ]);
    }
    ```
3. 使用命令同步 Seeder
    ```bash
    php artisan db:seed --class=DatabaseSeeder
    ```

### 日志管理

日志管理功能提供了对系统中的操作日志进行记录，包括：操作人、操作时间、操作内容、操作结果等。

#### 开发规范

1. 日志管理的数据表为 `access_logs`，数据模型为 `Modules\Starter\Entities\AccessLog`。
2. 在业务逻辑中使用辅助函数 `log_access` 记录日志，如：
    ```php
    log_access('查看xxx资源', $id);
    ```

### 消息通知管理

消息通知管理功能集成了消息通知的获取、查看、标记已读等功能。API
由 `Modules\Starter\Http\Controllers\NotificationController` 提供，
前端页面由 `Modules\Starter\Resources\views\web\components\NotificationBox.vue`组件提供。

#### 开发规范

1. 消息通知管理的数据表为 `notifications`
   ，详细的功能可以参考 [Laravel Notifications](https://laravel.com/docs/10.x/notifications)。
2. 在页面中使用 `NotificationBox` 组件，如：
    ```js
    import NotificationBox from "@modules/Starter/Resources/views/web/components/NotificationBox.vue"
    ```
    ```vue
    <NotificationBox></NotificationBox>
    ```

## 模块代码

### 数据表

```bash
2014_10_12_000001_create_dict_tables.php              # 字典表
2022_08_17_000002_create_access_logs_table.php        # 日志表
2023_04_07_162249_create_notifications_table.php      # 消息通知表
```

### 数据模型/Scope

```bash
Modules\Starter\Entities\BaseModal                # 基础 Model，支持 `appends` 中添加 `_datetime`, `_date`, `_human` 后缀后自动转换为对应的格式等功能                      
Modules\Starter\Entities\AccessLog                # 日志
Modules\Starter\Entities\Dictionary               # 字典
Modules\Starter\Entities\DictionaryItem           # 字典项                      
```

### 枚举

```php
// 基础状态码
enum State: string
{
    const SUCCESS = 'STATE_CODE_SUCCESS';
    const FAIL = 'STATE_CODE_FAIL';
    const DB_FAIL = 'STATE_CODE_DB_FAIL';
    const NOT_LOGIN = 'STATE_CODE_NOT_LOGIN';
    const NOT_FOUND = 'STATE_CODE_NOT_FOUND';
    const NOT_ALLOWED = 'STATE_CODE_NOT_ALLOWED';
    const INVALID_PARAMETERS = 'STATE_CODE_INVALID_PARAMETERS';
    const DUPLICATION = 'STATE_CODE_DUPLICATION';
    const USER_INVALID = 'STATE_CODE_USER_INVALID';
    const USER_INFO_INCOMPLETE = 'STATE_CODE_USER_INFO_INCOMPLETE';
    const TOO_FREQUENTLY = 'STATE_CODE_TOO_FREQUENTLY';
    const CAPTCHA_ERROR = 'STATE_CODE_CAPTCHA_ERROR';
    const VERIFY_ERROR = 'STATE_CODE_VERIFY_ERROR';
    const MOBILE_ERROR = 'STATE_CODE_MOBILE_ERROR';
    const EMAIL_ERROR = 'STATE_CODE_EMAIL_ERROR';
    const RISKY_CONTENT = 'STATE_CODE_RISKY_CONTENT';
    const RISKY_IMAGE = 'STATE_CODE_RISKY_IMAGE';
}
```

### 辅助函数

#### 基础

+ `starter_setup_user`

    ```php
    /**
     * 获取用户相关内容 [菜单, 权限, 部门, 个人信息, 是否超级管理员]
     * @return array
     */
    function starter_setup_user(): array
    ```

+ `starter_get_user_menu`

    ```php
    /**
     * 获取用户菜单
     * @param $user
     * @param array $permissions
     * @return array
     */
    function starter_get_user_menu($user = null, array $permissions = []): array
    ```

#### 字典

+ `dict_get`

    ```php
    /**
     * 获取字典
     * @param string $name
     * @param bool $only_options
     * @return array|BaseModel|Dictionary|null
     */
    function dict_get(string $name, bool $only_options = true): BaseModel|array|Dictionary|null
    ```

#### 日志

+ `log_access`

    ```php
    /**
     * @param string $action 动作
     * @param string $object 对象
     * @param string $effect 结果
     * @param int $user_id 用户ID
     * @param string $user_type 用户类型
     * @param string $brief 摘要
     * @return void
     */
    function log_access(string $action, string $object = '', string $effect = '', string $brief = '', int $user_id = 0, string $user_type = 'user'): void
    ```

### Controller

```bash
# 基础控制器，提供 `json`, `success`, `message` 方法   
Modules\Starter\Http\Controllers\BaseController
```

```php
// `json` 方法结果与状态都是自定的
public function json($result = null, $status = State::SUCCESS): JsonResponse

// `message` 方法状态为 `State::FAIL` 
public function message($message): JsonResponse

// `success` 方法状态为 `State::SUCCESS`
public function success($result): JsonResponse


//返回结果示例
[
    'status' => 'SUCCESS',
    'result' => [
        'id' => 1,
        'name' => 'name',
        'created_at' => '2021-08-17 16:22:49',
        'updated_at' => '2021-08-17 16:22:49',
    ],
]
```

```bash
Modules\Starter\Http\Controllers\DictContrller             # 提供字典的增删改查
Modules\Starter\Http\Controllers\NotificationController    # 提供日志的增删改查
Modules\Starter\Http\Controllers\UserCgiController         # 用户的认证登录，退出
```

### UI

#### PC 端页面

```bash
web/NudePageLogin.vue           # 登录页面
web/PageDict.vue                # 字典页面  
```

#### PC 组件

```bash
web/components/NotificationBox.vue      # 消息通知组件
```