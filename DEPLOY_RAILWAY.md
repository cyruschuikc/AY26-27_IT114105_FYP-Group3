# Railway 部署指南（Web + 自跑 MySQL）

本项目的部署形态：Web 用 Dockerfile 跑 PHP+Apache，MySQL 用项目内自跑的容器，
只挂一个 Volume 给 MySQL 数据目录。上传附件暂不持久化（半年内无真实用户，可接受）。

## 0. 前置条件

- 代码已推送到 GitHub。
- 已注册 Railway 并授权访问 GitHub 仓库。

## 1. 部署 Web 服务

1. Railway → New Project → Deploy from GitHub → 选择本仓库。
2. Railway 会识别根目录的 `Dockerfile` 并构建 Web 服务。
3. 在 Web 服务的 Variables 里加上第 3 节的数据库连接变量。

## 2. 自跑 MySQL 服务

1. 同一项目里 → New → 选择 Docker Image（或 Empty Service 后指定镜像），
   镜像填 `mysql:8.4`，服务名取 `mysql`。
2. 给 `mysql` 服务挂一个 Volume，挂载路径填 `/var/lib/mysql`（这是唯一需要挂的 Volume）。
3. 给 `mysql` 服务配置 Variables：

| 变量 | 值 |
|---|---|
| MYSQL_ROOT_PASSWORD | 一个强密码 |
| MYSQL_DATABASE | teambox |
| MYSQL_USER | teambox |
| MYSQL_PASSWORD | 另一个强密码 |

首次启动时，MySQL 镜像会自动创建 `teambox` 库和 `teambox` 用户。

## 3. Web 服务环境变量

给 Web 服务配置以下 Variables（值以 Railway 项目里实际显示的内部地址为准）：

| 变量 | 值 |
|---|---|
| MYSQLHOST | mysql 服务的内部地址，形如 `mysql.railway.internal` |
| MYSQLPORT | 3306 |
| MYSQLUSER | teambox |
| MYSQLPASSWORD | 第 2 节里 MYSQL_PASSWORD 设的密码 |
| MYSQLDATABASE | teambox |

## 4. 首次导入数据

`teambox.sql` 不含 `CREATE DATABASE` / `USE`，所以直接导入到已存在的 `teambox` 库即可。

用 Railway CLI 连上数据库代理：

```bash
railway connect mysql
```

然后在返回的代理端口上导入：

```bash
mysql -h 127.0.0.1 -P <代理端口> -u teambox -p<密码> teambox < teambox.sql
```

导入时若个别 MariaDB 专属设置报 warning，不影响使用，可忽略。

## 5. 定时任务 project_cron.php

新建一个 Cron Job 类型的服务，复用同一个仓库 / Dockerfile：

- 命令：`php /var/www/html/project_cron.php`
- 计划：每 2 分钟一次（`*/2 * * * *`）
- Variables：和第 3 节 Web 服务相同的 `MYSQL*` 变量

## 6. 日常更新

- 改代码：push 到 main，Railway 自动重新部署，数据库不受影响。
- 半年内（无真实用户）：结构或数据可更新 `teambox.sql` 后全量重导；导入前先备份一次。
- 有真实用户后：停止全量重导，改用增量 SQL（编号的 `ALTER` / `CREATE` 等）。

## 注意

- 上传附件（聊天文件、项目附件）存在容器临时磁盘，每次重新部署会丢失；文字聊天记录在
  MySQL，会保留。半年内可接受。开始真实使用后，需要给 Web 的 `uploads` 再挂一个 Volume。
