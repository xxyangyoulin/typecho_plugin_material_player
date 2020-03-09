# typecho质感音乐播放器插件

## 使用：
Step 1.   
下载插件，修改文件夹名称为`MaterialPlayer`，  移动到typecho项目`usr/plugins`目录下。  
Step 2.   
在需要的HTML文件位置插入：    
```php
<?php MaterialPlayer_Plugin::insert(); ?>
```
或者悬浮在网页上：  
```php
<?php MaterialPlayer_Plugin::fixed("30px", "30px", null); ?>
```
Step 3.   
设置中启动插件。

效果预览：
[typecho.yangyoulin.com](http://typecho.yangyoulin.com)


![nEx6HqbVmkRXao9](https://i.loli.net/2020/03/09/nEx6HqbVmkRXao9.png)

![H76eyhdqUZnGTlP](https://i.loli.net/2020/03/09/H76eyhdqUZnGTlP.png)

注意：  
网站音乐播放器需要ajax加载页面技术支持，否则跳转页面会中断音乐播放，严重影响体验！
