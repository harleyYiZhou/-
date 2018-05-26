<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],
    //极速数据配置
    'jisu'=>[
        'appkey'=>'69f6546b6ecb5c87',
        'content'=>'您的验证码是@，在5分钟内有效。如非本人操作请忽略本短信。【叮咚出行】',
    ],
    // 个人沙箱帐号：
    /*
     * 商家账号   naacvg9185@sandbox.com
     * appId     2016073100130857
     */

    /*
     * !!!作为一个良心人，别乱改测试账号资料
     * 买家账号    aaqlmq0729@sandbox.com
     * 登录密码    111111
     * 支付密码    111111
     */
    'ali'=>[
//        'use_sanbox'=>true,
//        'partner'=>'2088102174997884',
//        'app_id'=>'2016091000479591',
//        'sign_type'=>'RSA2',
////        'ali_public_key'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyr0AFfjAQwiT7rSEvapdB2bfZHU4Xm/GPRhvMMMapOpJ5j4pTHgNl+w02sse3F2zBlBMpuYeQQnqje2De3b0g7fn3+RHt37pn5AzFp4GcBcUSrxkg4DQYU+oC1dlBiXxPyoUAsHuoZ0mA+JYGfN+Z0dmMVbrgZxyaWbEpZtu4oOFEeB9mAuqG8pSiw51Z1L1AaCmHMKbm6E0iHUpOC0ve+Uji2t8pTPjXWMXXbhDeQOXzMlxq8QyUWW+cc45ZykMzceYI2cKXpMNcbAWqvQOUoDDVk6+9NR7kmKdQx/0DaGzg37TFQHqjQ7XIHpYZpCq/X83AmerA7QAd0RMy+guswIDAQAB',
////        'rsa_private_key'=>'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCN3EtOX0DMInEYFdjcaKMnd+hyCYNINlWVC31AdTgj5sWiJ+pbLMQumLQyVYWSNP4hxW8X3qNElIhaQod+vPc2RoTvusN0NJH4P3PRwImMRwZ1SRbwUDCjzX21g4RCW0zkhCVZdgjk9D+e+cnI/kq+2Ilkx/8qGtM+fIjP1aKjiV195F/QA8P/SNWHG7U1zMBFgdnM+s0WOqI8BfY6CLl/fNOMBjTyfHT6RnHiHaInEuvJBjF/6bIU4RC5kXca0WE4blUDwqV54ThbgH1akemh3L/O8nrQlnIuLKjFH3k1ifKhwVPZCd5qzTe2PEXpdpWjl59OdZI/Hz/hJImpbyz5AgMBAAECggEASoFMu9ijO5NLOo8e2oPKTMxZNKtyW9bNk/34xoXJ+nEVk1jLaR9V2fMxkM4B3Qwf/LSo6CxyK26nPmB6sUGlxMe8wfug88M4dt08gwlQNHCJH8hHV+eF5EHkqBRuvjsdY1dUFmw7t6PgVipypDGKKDmpz1iK1x5HnCXj5OQbCzAse1ZXP7X5tTMNOz/b8i1lKfoGeUUsmgsptEJByf/42EBwdWaqqSlrfWFStfdvIa/beIxdFy7Uo5nT3M/22A8f1HQvMq8RLwhpLFfL2HNC87OUD8rZ3u+TAoP7jiAWmwKphclvc5tpE/Gw6wTwaEbE2riMYMy3t0HgKiEA76HBUQKBgQDV4kUMjsEtjPuMKrudNzKdRy9MbZ3OAMbhrjNO9bYWepgTM1/C0IQeklwcv6Me2VJOqKP4ycvYOKDKZpB4QREsIpe3yUezBMs5kRsDUmRiH19nwRGWUaw9QlET8FlSVc7OUywIHb2PbtzIdarxwiBQwv4lfenlMMvMxaT83cv9KwKBgQCpy2FOgmdgyIq7dxzVqpPk4TucjOI0IlXxw9lzcLJpPh+P5jzVFTyFH2Di3qHdOuX4qihnoXh4pgcojfCx6qJeaVEv9u462oWlYsT5JwkPYufpJn1s6qi971iTegnq0KsDZmQcEGv1JLxdi6xJYARjY9ElIryzTb3PioRgX68UawKBgQC2WfKq0S8MKe3xRCGu2XeythUyYSawkuL9WGrwP/cd1SnA/QUA8IwkEH6uGAj37SsKWydpnorH7wfPaIB1tmvwqBGYJOiXTVgC3Z8dRurE6aCKsgXPlhR2Kgs8J6fP1c67BBg/VzPdjRR5aDD1a5/nm0fZKS/4MpEfJccTu/oF/wKBgHIPjsGHtlx8uz1tqbAbE5ioeTwUJAyzmBp930C+TwuF0PwAxZK63lnrF1GwDvoni788x0G+Zched+aVW0CZhxN3GjPnZGeizi6op/tbbqmoXnPwCEe3Zz2YYF9JlwnnvUneaZe0rGEUmXkZz9XGq4v8zM1uhWbJ7Z1SxtRa0qkbAoGBAKGtW2CwvnZ/c7XcVjFfL4WDI7QGiW1AYRecJw2WPjBqcBOMr4QqVM4h4ze6FbodfP8DLQ5Gm6UVtxSLY1CpMSEa9rMt1lmnAkis9rjHuIZBI8uXoneEwguOrKuesSdMSkegvCy5+r3hMNdMulAQvYLMX/RV5B703ik0KwFl6PPb',
//        'limit_pay'=>[],
//        'notify_url'=>'',
//        'return_url'=>'',
//        'return_raw'=>false,
        'use_sandbox'               => true,// 是否使用沙盒模式

        'app_id'                    => '2016073100130857',
        'sign_type'                 => 'RSA2',// RSA  RSA2

        // ！！！注意：如果是文件方式，文件中只保留字符串，不要留下 -----BEGIN PUBLIC KEY----- 这种标记
        // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)
        'ali_public_key'            => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmBjJu2eA5HVSeHb7jZsuKKbPp3w0sKEsLTVvBKQOtyb7bjQRWMWBI7FrcwEekM1nIL+rDv71uFtgv7apMMJdQQyF7g6Lnn9niG8bT1ttB8Fp0eud5L97eRjFTOa9NhxUVFjGDqQ3b88o6u20HNJ3PRckZhNaFJJQzlahCpxaiIRX2umAWFkaeQu1fcjmoS3l3BLj8Ly2zRZAnczv8Jnkp7qsVYeYt01EPsAxd6dRZRw3uqsv9pxSvyEYA7GV7XL6da+JdvXECalQeyvUFzn9u1K5ivGID7LPUakdTBUDzlYIhbpU1VS8xO1BU3GYXkAaumdWQt7f+khoFoSw+x8yqQIDAQAB',

        // ！！！注意：如果是文件方式，文件中只保留字符串，不要留下 -----BEGIN RSA PRIVATE KEY----- 这种标记
        // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试
        'rsa_private_key'           => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC/z+Ue/oS0GjO2myYrkdopw5qq6Ih/xlHBx0HBE0xA2dRinpMuZeI0LUUtN54UAUZbDz8rcaOCb0jeloeYolw54tadcIw4Q2hbdeJPplldJZyi1BDYtBJZvAveeRSidHdmBSUtOtCBXUBlJUP3I8/R4c34Ii4Pm/K4vmhwLf/zqZAedKGhYP6m5q+p8sfBHRPy97/KluLPiSTRFqGSRmd0IitUGK+KQ5qsAfJXyN1oVR4jBYaxfx7dWkTWmxAfNqtKfMvu2a5lH6hvClN+w4RUDBu3939bLjCYKcAomkv3QMquMP46m+D8Ny+3mGk5L9Ul4jyxlFTlV4L4JM3g/02xAgMBAAECggEBALZliwseHDLnd6V9g56K41ozlzBOTv6yJ6yNPgnLwAcrHLtq76p/V8smAVIuQTPkwnJ03S0CsumlyTVhDzAltG2XN14fWDdoYiQWxU3YccIRshFkd2CaW5jZKLA1k1moRqHM4r1P4FYjxshn12l7tHNwtdvvJL3THcxvxABovauFOVtznpRlnfJLjn2Lg+xNsxaYy3zL8L6nL7MXUWLKvmLiZn64PFcw7cf+9n2exRDswn0wDCpypGqOVVXVFeZaXTwmOoxgIUAZfAExdLtabGGCAz1lTsA0+r4DW2nSTe8CFy1Db+fcCTm+uQ3y6jDwuS3tB8V+PQKog3+ReZp/9sECgYEA/NEr+ln6DTy7u4rCWq7mixRJ1kaiAUph/hADrUwhkMiUapSMNAIXblFB+BQUjFZQmXEbcvz0Y70g9Zi9JCXVTiDTBe7jj/FK63MU0F9KY5OducpVV+RhSpNy/i1M2qeW4gO351PpPHUpRUYrGkYvAKktqrSOdBEWD3IeKLYDXxMCgYEAwjoavGjWzD9Xckbpb8yrQ+gHfLeWDKh7BgvoBGagyqbzIOZU9wg3dSQ2F5eMWDxWVRGqap3fIHxcA0/VMqXG1DrvSIUC4SE8Zys515fR00c9h3W3IugHnKgdYcV7nZrJoPZXlMjPOo39FCBnfbrUOgnKwxMlz3lVvC6465ODhKsCgYEAmUtTuTd5kTE0O+FFO6s1iztAEjc94D5z8JNRR3EUITAeHgn4gUiLYI7Qy1WRqA5mTMPyeuS6Ywe4xnJYrWRrVDY+/if9v7f1T5K2GirNdld5mb//w41tGMUTQt/A7AwWRvEuP4v3rnr0DVcgp4vK0EHEuO9GOUZq8+6kLtc+cBUCgYBFJ/kzEsVAjmEtkHA33ZExqaFY1+l2clrziTPAtWYVIiK5mSmxl9xfOliER/KxzDIVMigStEmpQH5ms3s/AGXuVVmz4aBn1rSyK2L6D9WnO9t9qv1dUW68aeOkV3OvZ1jZlj0S/flDaSEulGclDmvYinoGwX+aAyLy0VQIlUqj5wKBgHEUEf7YDnvw/IBnF1E4983/7zBx9skoHhpEZsh2+1or7LIw6z0m3lsNBnK0MZZBmW/7HwOtVfhXUUPbVrOJdi70YoMynX3gjK3LTXhzISheZgcNRKTqiJgVunPokJxQRyYcAfaQeuIm9O8cCPE1rZpNAzCdd4NSj83UZRm3YOmC',

        'limit_pay'                 => [
            //'balance',// 余额
            //'moneyFund',// 余额宝
            //'debitCardExpress',// 	借记卡快捷
            //'creditCard',//信用卡
            //'creditCardExpress',// 信用卡快捷
            //'creditCardCartoon',//信用卡卡通
            //'credit_group',// 信用支付类型（包含信用卡卡通、信用卡快捷、花呗、花呗分期）
        ],// 用户不可用指定渠道支付当有多个渠道时用“,”分隔

        // 与业务相关参数
        'notify_url'                => 'http://xxxx/index/order/payNotify',//这个路径为异步通知路径，你答辩时可以打开ngrok，然后设置这里即可，
        'return_url'                => 'http://localhost:8082/#/paySuccess',//这个是支付成功后浏览器跳转的页面地址

        'return_raw'                => false,// 在处理回调时，是否直接返回原始数据，默认为 true
    ],
];
