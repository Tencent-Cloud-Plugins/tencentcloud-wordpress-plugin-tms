# 腾讯云文本内容安全插件

## 1.插件介绍
> tencentcloud-tms插件是一款腾讯云研发的，提供给WordPress站长使用的官方插件。对用户在评论提交的文字出现违规涉黄、爆、恐的内容，进行内容检测和过滤功能

| 标题       | 内容                                                         |
| ---------- | ------------------------------------------------------------ |
| 中文名称     | 腾讯云文本内容安全（TMS）插件                                         |
| 英文名称   | tencentcloud-tms                                       |
| 最新版本   | v1.0.2 (2021.4.28)                                           |
| 适用平台 | [WordPress](https://wordpress.org/)                             |
| 适用产品 | [腾讯云文本内容安全（SMS）](https://cloud.tencent.com/product/tms)   |
| 文档中心   | [春雨文档中心](https://openapp.qq.com/docs/Wordpress/tms.html) |
| 主创团队   | 腾讯云中小企业产品中心（SMB Product Center of Tencent Cloud）    |

## 2.功能特性

- 对用户在评论提交的文字出现违规涉黄、爆、恐的内容，使用腾讯云文本内容安全接口进行内容检测和过滤

## 3.安装指引

### 3.1.部署方式一：通过GitHub部署安装

> 1. git clone https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-tms.git
> 2. 复制 tencentcloud-wordpress-plugin-tms文件夹 到wordpress安装路径/wp-content/plugins/文件夹里面

### 3.2.部署方式二：通过WordPress插件中心安装
> 1. 前往[WordPress插件中心](https://wordpress.org/plugins/tencentcloud-tms)点击下载
> 2. 你的WordPress站点后台=》插件=》安装插件。点击左上角的"上传插件"按钮，选择上一步下载的zip安装包

### 3.3.部署方式三：通过WordPress站点后台安装
> 1. 你的WordPress站点后台=》插件=》安装插件。在页面搜索框输入tencentcloud-tms
> 2. 点击"安装"按钮，就会自动下载安装插件

## 4.使用指引

### 4.1.界面功能介绍

![](./images/tms1.png)
> 后台配置页面。配置介绍请参考下方的[名词解释](#_4-2-名词解释)

![](./images/tms2.png)
>对发文章/评论的场景中提交的文字内容进行检测，检测不通过将会提示如上信息

![](./images/tms3.png)
>配置敏感词白名单，发文章/评论时将绕过白名单中的关键词检测

![](./images/tms4.png)
### 4.2. 名词解释
- **自定义密钥：** 插件提供统一密钥管理，既可在多个腾讯云插件之间共享SecretId和SecretKey，也可为插件配置单独定义的腾讯云密钥。
- **Secret ID：** 在[腾讯云API密钥管理](https://console.cloud.tencent.com/cam/capi)上申请的标识身份的 SecretId。
- **Secret Key：** 在[腾讯云API密钥管理](https://console.cloud.tencent.com/cam/capi)上申请的与SecretId对应的SecretKey。
- **人工复审：** 系统审核通过后，还需要管理人员进行二次审核
- **不允许提交：** 系统审核不通过，如上图2，前台直接返回，后台评论管理页面无记录。
- **移动回收站：** 系统审核不通过，评论不显示在前台，提交后评论出现在后台评论管理页面的回收站中
- **标记为垃圾评论：** 系统审核不通过，评论不显示在前台，提交后评论出现在后台评论管理页面的垃圾评论中


## 5.获取入口

| 插件入口          | 链接                                                         |
| ----------------- | ------------------------------------------------------------ |
| GitHub            | [link](https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-tms)   |

## 6.FAQ

> 暂无

## 7.GitHub版本迭代记录
### 2021.04.28 tencentcloud-wordpress-plugin-tms v1.0.2
- 新增敏感词白名单功能和敏感词命中记录功能

### 2020.12.11 tencentcloud-wordpress-plugin-tms v1.0.1
- 支持在windows环境下运行

### 2020.6.22 tencentcloud-wordpress-plugin-tms v1.0.0
- 对用户在评论提交的文字出现违规涉黄、爆、恐的内容，使用腾讯云文本内容安全接口进行内容检测和过滤
---
本项目由腾讯云中小企业产品中心建设和维护，了解与该插件使用相关的更多信息，请访问[春雨文档中心](https://openapp.qq.com/docs/Wordpress/tms.html) 

请通过[咨询建议](https://support.qq.com/products/164613) 向我们提交宝贵意见。