### 依赖

 * thinkphp5.0~
 * php-tools   github地址：https://github.com/Magein/php-tools

### 说明

 > 基于thinkphp5的后台管理模块，没办法单独使用
 
 安装后需要把src/view下的目录以及文件复制到think项目的admin模块下
 
### 静态资源文件

 内部是通过tp中的包含文件实现的，插件使用cdn的形式加载的，还有一些是自定义的文件，需要手动引入
 
 优化方式：把cdn文件下载到本地，修改路径即可 
 
 public下的theme需要引入到项目根目录static下
 
### 数据库

 数据库创建使用think-migration创建，所以需要把migration执行后生成数据库文件 

  