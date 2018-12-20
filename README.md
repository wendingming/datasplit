《TP5+mysql通用分表代码说明文档》
===================

## 系统后台
- 后台地址
	- /admin/login/index.html

- 用户名
	- admin

- 密码
	- admin

- 模块列表
	- 新闻文章
	- 留言
	- 商品管理
	- 会员管理
	- 系统设置【菜单管理、日志管理、分表管理】
	- 管理设置【管理员管理、分组管理、个人设置】

## SQL
- 地址
	- [数据库sql文件](our.sql) 


## 说明
- 分表方式
	- 横向分表

	
分表信息主表

```
CREATE TABLE `our_datasplit` (
  `ds_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分表分库ID',
  `ds_name` varchar(30) NOT NULL DEFAULT '' COMMENT '分表分库名称【总表总库名称】',
  `ds_table_database` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型:0.分表,1.分库',
  `ds_type_hash` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分表方式hash:0.否，1.是',
  `ds_type_date` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分表方式日期：0.否,1.日期【日】,2.日期【周】,3.日期【月】,4.日期【年】',
  `ds_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0.未启用，1.启用',
  `ds_num` int(10) NOT NULL DEFAULT '0' COMMENT 'hash分表/分库数量',
  `ds_field_hash` varchar(100) NOT NULL DEFAULT '0' COMMENT 'hash分表字段',
  `ds_field_date` varchar(100) NOT NULL DEFAULT '0' COMMENT '分表字段：日期',
  `ds_remark` varchar(100) NOT NULL DEFAULT '' COMMENT '说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort_order` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `ds_editnum` int(4) NOT NULL DEFAULT '0' COMMENT '执行分表次数',
  `child_name` varchar(100) DEFAULT '' COMMENT '子表名称',
  `child_id_field` varchar(100) DEFAULT '' COMMENT '子表关联id名',
  `child_id` varchar(100) DEFAULT '' COMMENT '子表2自增ID',
  `child_name2` varchar(100) DEFAULT '' COMMENT '子表2名称',
  `child_id_field2` varchar(100) DEFAULT '' COMMENT '子表2关联id',
  `child_id2` varchar(100) DEFAULT '' COMMENT '子表2自增ID',
  PRIMARY KEY (`ds_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
```

	
分表信息子表

```
CREATE TABLE `our_datasplit_detail` (
  `ds_d_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分表分库详情表ID',
  `ds_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分表分库主表ID',
  `name` varchar(500) NOT NULL DEFAULT '' COMMENT '分表名/分库名',
  `is_hash_date` tinyint(4) NOT NULL DEFAULT '0' COMMENT '分表方式：0.hash,1.日期',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ds_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0.未启用，1.已启用，2.已作废1次，3.已作废2次，N.已作废N-1次',
  `begin_time` int(10) NOT NULL DEFAULT '0' COMMENT '日期分表：开始时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '日期分表，结束时间',
  `child_name` varchar(500) DEFAULT '' COMMENT '子表名称',
  `child_name2` varchar(500) DEFAULT '' COMMENT '子表2名称',
  PRIMARY KEY (`ds_d_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

```

分表列表显示示例
![Alt text](/readmepic/datasplit_list.png "Optional title")