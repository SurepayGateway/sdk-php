下载 所需版本的 PHP，调试包，开发包：https://windows.php.net/download#php-8.1
Download the required versions of PHP, test packages, and development packages:

下载 xdebug 调试工具 ：https://xdebug.org/download#releases
Download the xdebug debugging tool

如果你不想使用PHP那么你也可用下载XAMPP开发工具：https://www.apachefriends.org/
If you don't want to use PHP then you can also download the XAMPP development tools:

但DEBUG和DEVEL和XDEBUG也都需要下载。
But DEBUG and DEVEL and XDEBUG also need to be downloaded.

开发工具您可用使用VSCODE：https://code.visualstudio.com/ 或 phpstorm：https://www.jetbrains.com/phpstorm/
Development tools You can use VSCODE or phpstorm

phpstorm config press [Ctrl+Alt+S] - > PHP - > PHP(解释器) interpreter
(名称)NAME：PHP
PHP(可执行文件)Executable File： C:\soft\xampp\php\php.exe
(配置文件)Configuration File：C:\soft\xampp\php\php.ini
(调试器扩展)Debugger Extension：C:\soft\xampp\php\ext\php_xdebug-3.2.0-8.1-vs16-x86_64.dll

pack list :
PHP: php-8.1.13-Win32-vs16-x64
DEBUG: php-debug-pack-8.1.13-Win32-vs16-x64
DEVEL: php-devel-pack-8.1.13-Win32-vs16-x64
XDEBUG: php_xdebug-3.2.0-8.1-vs16-x86_64

解压php-8.1.13-Win32-vs16-x64后并安装PHP
解压php-devel-pack-8.1.13-Win32-vs16-x64并复制到PHP根目录。
解压php_xdebug-3.2.0-8.1-vs16-x86_64并复制到PHP\ext目录。

# make sure that the php_openssl.dll is present within the ext directory of your PHP installation.
确保 php_openssl.dll 存在于 PHP 安装的 ext 目录中。

1.
Open php.ini file located under php installation folder.
打开位于 php 安装文件夹下的 php.ini 文件。

2.
Search for extension=php_openssl.dll.
搜索 extension=openssl 和 extension=php_openssl.dll

3.
Uncomment it by removing the semi-colon(;) in front of it.
通过删除它前面的分号 (;) 取消注释。

4.
Restart the Apache Server.
重新启动 Apache 服务器。

5.
extension=openssl
extension=php_openssl.dll

6. 
错误：error:0407109F:rsa routines:RSA_padding_check_PKCS1_type2:pkcs decoding error
解决：$HASH_ALGORITHM = 'rsa-sha256' 改成  'sha256WithRSAEncryption'
或者升级您的PHP版本到8.0以上，确保openssl版本库是：openssl-1.1.1。
