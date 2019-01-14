# dnp
docker + nginx + php5/7 搭建灵活的PHP运行环境


dnp特点:
1. 开源免费
2. 支持php7.2/5.6自由切换
3. 支持绑定**任意多个域名**
4. 支持**HTTPS和HTTP/2**
5. **PHP源代码、MySQL数据、配置文件、日志文件**都可在Host中直接修改查看
6. 内置**完整PHP扩展安装**命令
7. 默认安装`pdo_mysql`、`redis`、`xdebug`、`swoole`等常用热门扩展，拿来即用
8. 一次配置，**Windows、Linux、MacOs**皆可用


# 目录
- [1.目录结构](#1目录结构)
- [2.快速使用](#2快速使用)
- [3.切换PHP版本](#3切换php版本)
- [4.使用Log](#5使用log)
    - [5.1 Nginx日志](#51-nginx日志)
    - [5.2 PHP-FPM日志](#52-php-fpm日志)
- [5.使用composer](#6使用composer)



## 1.目录结构

```
/
├── conf                    配置文件目录
│   ├── conf.d              Nginx用户站点配置目录
│   ├── nginx.conf          Nginx默认配置文件
│   ├── php-fpm.conf        PHP-FPM配置文件（部分会覆盖php.ini配置）
│   └── php.ini             PHP默认配置文件
├── Dockerfile              PHP镜像构建文件
├── extensions              PHP扩展源码包
├── log                     Nginx日志目录
├── www                     PHP代码目录
└── source.list             Debian源文件
```


## 2.快速使用
1. 本地安装`git`、`docker`和`docker-compose`。
2. `clone`项目：
    ```
    $ git clone https://github.com/george518/dnp.git
    ```
3. 如果不是`root`用户，还需将当前用户加入`docker`用户组：
    ```
    $ sudo gpasswd -a ${USER} docker
    ```
4.  启动：
    ```
    $ cd dnp
    $ docker-compose up
    ```
5. 访问在浏览器中访问：

 - [http://localhost](http://localhost)： 默认*http*站点
 注意yml中设置的端口是否被占用

两个站点使用同一PHP代码：`./www/localhost/index.php`。

要修改端口、日志文件位置、以及是否替换source.list文件等，请修改.env文件，然后重新构建：
```bash
$ docker-compose build php56    # 重建单个服务
$ docker-compose build          # 重建全部服务

```


## 3.切换PHP版本
默认情况下，我们同时创建 **PHP5.6和PHP7.2** 三个PHP版本的容器，

切换PHP仅需修改相应站点 Nginx 配置的`fastcgi_pass`选项，

例如，示例的 [http://localhost](http://localhost) 用的是PHP5.7，Nginx 配置：
```
    fastcgi_pass   php72:9000;
```
要改用PHP5.6，修改为：
```
    fastcgi_pass   php56:9000;
```
再 **重启 Nginx** 生效。
```bash
$ docker exec -it dnp_nginx_1 nginx -s reload
```



## 4.使用Log

Log文件生成的位置依赖于conf下各log配置的值。

### 4.1 Nginx日志
Nginx日志是我们用得最多的日志，所以我们单独放在根目录`log`下。

`log`会目录映射Nginx容器的`/var/log/nginx`目录，所以在Nginx配置文件中，需要输出log的位置，我们需要配置到`/var/log/nginx`目录，如：
```
error_log  /var/log/nginx/nginx.localhost.error.log  warn;
```


### 4.2 PHP-FPM日志
大部分情况下，PHP-FPM的日志都会输出到Nginx的日志中，所以不需要额外配置。

另外，建议直接在PHP中打开错误日志：
```php
error_reporting(E_ALL);
ini_set('error_reporting', 'on');
ini_set('display_errors', 'on');
```

如果确实需要，可按一下步骤开启（在容器中）。

1. 进入容器，创建日志文件并修改权限：
    ```bash
    $ docker exec -it dnp_php56_1 /bin/bash
    $ mkdir /var/log/php
    $ cd /var/log/php
    $ touch php-fpm.error.log
    $ chmod a+w php-fpm.error.log
    ```
2. 主机上打开并修改PHP-FPM的配置文件`conf/php-fpm.conf`，找到如下一行，删除注释，并改值为：
    ```
    php_admin_value[error_log] = /var/log/php/php-fpm.error.log
    ```
3. 重启PHP-FPM容器。


## 5.使用composer
**我们建议在主机HOST中使用composer，避免PHP容器变得庞大**。
1. 在主机创建一个目录，用以保存composer的配置和缓存文件：
    ```
    mkdir ~/dnp/composer
    ```
2. 打开主机的 `~/.bashrc` 或者 `~/.zshrc` 文件，加上：
    ```
    composer () {
        tty=
        tty -s && tty=--tty
        docker run \
            $tty \
            --interactive \
            --rm \
            --user $(id -u):$(id -g) \
            --volume ~/dnp/composer:/tmp \
            --volume /etc/passwd:/etc/passwd:ro \
            --volume /etc/group:/etc/group:ro \
            --volume $(pwd):/app \
            composer "$@"
    }

    ```
3. 让文件起效：
    ```
    source ~/.bashrc
    ```
4. 在主机的任何目录下就能用composer了：
    ```
    cd ~/dnp/www/
    composer create-project [name] project --no-dev
    ```
5. （可选）如果提示需要依赖，用`--ignore-platform-reqs --no-scripts`关闭依赖检测。
6. （可选）第一次使用 composer 会在 ~/dnp/composer 目录下生成一个config.json文件，可以在这个文件中指定国内仓库，例如：
    ```
    {
        "config": {},
        "repositories": {
            "packagist": {
                "type": "composer",
                "url": "https://packagist.laravel-china.org"
            }
        }
    }

    ```


## License
MIT

## 感谢

https://github.com/yeszao/dnmp



