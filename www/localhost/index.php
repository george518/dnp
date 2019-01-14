<?php

echo '<h1 style="text-align: center;">欢迎使用DNP</h1>';
echo '<h2>版本信息</h2>';

echo '<ul>';
echo '<li>PHP版本：', PHP_VERSION, '</li>';
echo '<li>Nginx版本：', $_SERVER['SERVER_SOFTWARE'], '</li>';

echo '</ul>';

echo '<h2>已安装扩展</h2>';
printExtensions();


/**
 * 获取已安装扩展列表
 */
function printExtensions()
{
    echo '<ol>';
    foreach (get_loaded_extensions() as $i => $name) {
        echo "<li>", $name, '=', phpversion($name), '</li>';
    }
    echo '</ol>';
}

