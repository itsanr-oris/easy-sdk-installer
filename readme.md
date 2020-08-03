## 简介

快速创建Easy sdk项目指令扩展包

[![Latest Stable Version](https://poser.pugx.org/f-oris/easy-sdk-installer/v)](//packagist.org/packages/f-oris/easy-sdk-installer) [![Total Downloads](https://poser.pugx.org/f-oris/easy-sdk-installer/downloads)](//packagist.org/packages/f-oris/easy-sdk-installer) [![Latest Unstable Version](https://poser.pugx.org/f-oris/easy-sdk-installer/v/unstable)](//packagist.org/packages/f-oris/easy-sdk-installer) [![License](https://poser.pugx.org/f-oris/easy-sdk-installer/license)](//packagist.org/packages/f-oris/easy-sdk-installer)

## 安装

通过composer引入扩展包

```bash
composer global require f-oris/easy-sdk-installer
```

## 使用

#### 创建SDK项目（推荐）

执行命令，并按照交互提示，填写sdk包名、描述、作者、根命名空间等信息，即可完成创建

```bash
easy-sdk new sdk-demo
```

#### 初始化SDK项目（不推荐）

开发者自行到[easy-sdk](https://github.com/itsanr-oris/easy-sdk)下载最新代码包，并解压到目标文件夹，通过命令行进入该文件夹后，执行如下命令，并按照交互提示，填写sdk包名、描述、作者、根命名空间等信息，即可完成创建

```bash
easy-sdk init .
```

## License

MIT License

Copyright (c) 2019-present F.oris <us@f-oris.me>

