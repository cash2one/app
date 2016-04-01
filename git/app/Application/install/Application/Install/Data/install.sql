-- -----------------------------
-- JFCMS MySQL Data Transfer
-- Date: 2015-12-04 15:31:02
-- -----------------------------

SET FOREIGN_KEY_CHECKS=0;


-- ----------------------------
-- Table structure for onethink_action
-- ----------------------------
DROP TABLE IF EXISTS `onethink_action`;
CREATE TABLE `onethink_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '行为唯一标识',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '行为说明',
  `remark` char(140) NOT NULL DEFAULT '' COMMENT '行为描述',
  `rule` text NOT NULL COMMENT '行为规则',
  `log` text NOT NULL COMMENT '日志规则',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统行为表';

-- ----------------------------
-- Records of onethink_action
-- ----------------------------
INSERT INTO `onethink_action` VALUES ('1', 'user_login', '用户登录', '积分+10，每天一次', 'table:member|field:score|condition:uid={$self} AND status>-1|rule:score+10|cycle:24|max:1;', '[user|get_nickname]在[time|time_format]登录了后台', '1', '1', '1387181220');
INSERT INTO `onethink_action` VALUES ('2', 'add_article', '发布文章', '积分+5，每天上限5次', 'table:member|field:score|condition:uid={$self}|rule:score+5|cycle:24|max:5', '', '2', '0', '1380173180');
INSERT INTO `onethink_action` VALUES ('3', 'review', '评论', '评论积分+1，无限制', 'table:member|field:score|condition:uid={$self}|rule:score+1', '', '2', '1', '1383285646');
INSERT INTO `onethink_action` VALUES ('4', 'add_document', '发表文档', '积分+10，每天上限5次', 'table:member|field:score|condition:uid={$self}|rule:score+10|cycle:24|max:5', '[user|get_nickname]在[time|time_format]发表了一篇文章。\r\n表[model]，记录编号[record]。', '2', '0', '1386139726');
INSERT INTO `onethink_action` VALUES ('5', 'add_document_topic', '发表讨论', '积分+5，每天上限10次', 'table:member|field:score|condition:uid={$self}|rule:score+5|cycle:24|max:10', '', '2', '0', '1383285551');
INSERT INTO `onethink_action` VALUES ('6', 'update_config', '更新配置', '新增或修改或删除配置', '', '', '1', '1', '1383294988');
INSERT INTO `onethink_action` VALUES ('7', 'update_model', '更新模型', '新增或修改模型', '', '', '1', '1', '1383295057');
INSERT INTO `onethink_action` VALUES ('8', 'update_attribute', '更新属性', '新增或更新或删除属性', '', '', '1', '1', '1383295963');
INSERT INTO `onethink_action` VALUES ('9', 'update_channel', '更新导航', '新增或修改或删除导航', '', '', '1', '1', '1383296301');
INSERT INTO `onethink_action` VALUES ('10', 'update_menu', '更新菜单', '新增或修改或删除菜单', '', '', '1', '1', '1383296392');
INSERT INTO `onethink_action` VALUES ('11', 'update_category', '更新分类', '新增或修改或删除分类', '', '', '1', '1', '1383296765');

-- ----------------------------
-- Table structure for onethink_action_log
-- ----------------------------
DROP TABLE IF EXISTS `onethink_action_log`;
CREATE TABLE `onethink_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` bigint(20) NOT NULL COMMENT '执行行为者ip',
  `model` varchar(50) NOT NULL DEFAULT '' COMMENT '触发行为的表',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的数据id',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
  PRIMARY KEY (`id`),
  KEY `action_ip_ix` (`action_ip`),
  KEY `action_id_ix` (`action_id`),
  KEY `user_id_ix` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='行为日志表';

-- ----------------------------
-- Records of onethink_action_log
-- ----------------------------
INSERT INTO `onethink_action_log` VALUES ('1', '1', '1', '3232236188', 'member', '1', 'admin在2015-12-04 15:27登录了后台', '1', '1449214044');

-- ----------------------------
-- Table structure for onethink_addons
-- ----------------------------
DROP TABLE IF EXISTS `onethink_addons`;
CREATE TABLE `onethink_addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL COMMENT '插件名或标识',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text COMMENT '插件描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `config` text COMMENT '配置',
  `author` varchar(40) DEFAULT '' COMMENT '作者',
  `version` varchar(20) DEFAULT '' COMMENT '版本号',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装时间',
  `has_adminlist` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有后台列表',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='插件表';

-- ----------------------------
-- Records of onethink_addons
-- ----------------------------
INSERT INTO `onethink_addons` VALUES ('15', 'EditorForAdmin', '后台编辑器', '用于增强整站长文本的输入和显示', '1', '{\"editor_type\":\"2\",\"editor_wysiwyg\":\"1\",\"editor_height\":\"700px\",\"editor_resize_type\":\"1\"}', 'thinkphp', '0.1', '1383126253', '0');
INSERT INTO `onethink_addons` VALUES ('23', 'SocialComment', '通用社交化评论', '集成了各种社交化评论插件，轻松集成到系统中。', '1', '{\"comment_type\":\"1\",\"comment_uid_youyan\":\"90040\",\"comment_short_name_duoshuo\":\"\",\"comment_form_pos_duoshuo\":\"buttom\",\"comment_data_list_duoshuo\":\"10\",\"comment_data_order_duoshuo\":\"asc\"}', 'thinkphp', '0.1', '1417694500', '0');
INSERT INTO `onethink_addons` VALUES ('22', 'SiteStat', '站点统计信息', '统计站点的基础信息', '1', '{\"title\":\"\\u7cfb\\u7edf\\u4fe1\\u606f\",\"width\":\"2\",\"display\":\"1\"}', 'thinkphp', '0.1', '1417694483', '0');
INSERT INTO `onethink_addons` VALUES ('4', 'SystemInfo', '系统环境信息', '用于显示一些服务器的信息', '1', '{\"title\":\"\\u7cfb\\u7edf\\u4fe1\\u606f\",\"width\":\"2\",\"display\":\"1\"}', 'thinkphp', '0.1', '1379512036', '0');
INSERT INTO `onethink_addons` VALUES ('5', 'Editor', '前台编辑器', '用于增强整站长文本的输入和显示', '1', '{\"editor_type\":\"2\",\"editor_wysiwyg\":\"1\",\"editor_height\":\"300px\",\"editor_resize_type\":\"1\"}', 'thinkphp', '0.1', '1379830910', '0');
INSERT INTO `onethink_addons` VALUES ('6', 'Attachment', '附件', '用于文档模型上传附件', '1', 'null', 'thinkphp', '0.1', '1379842319', '1');
INSERT INTO `onethink_addons` VALUES ('26', 'Digg', 'Digg插件', '网上通用的文章顶一下，踩一下插件（不支持后台作弊修改数据）。', '1', '{\"good_tip\":\"\\u8fd9\\u6587\\u7ae0\\u4e0d\\u9519\",\"bad_tip\":\"\\u8fd9\\u6587\\u7ae0\\u5f88\\u5dee\",\"stop_repeat_tip\":\"\\u60a8\\u5df2\\u7ecf\\u6295\\u8fc7\\u7968\\u4e86\\uff0c\\u611f\\u8c22\\u60a8\\u7684\\u53c2\\u4e0e\\uff01\",\"post_sucess_tip\":\"\\u6295\\u7968\\u6210\\u529f\\uff01\",\"post_error_tip\":\"\\u5b32\\u4f60\\u7684,\\u56e7^__^,\\u4e0d\\u662f\\u521a\\u521a\\u9876\\u8fc7\\u5417\\uff01\\uff01\"}', 'thinkphp', '0.3', '1417694557', '0');
INSERT INTO `onethink_addons` VALUES ('29', 'QiuBai', '糗事百科', '读别人的糗事，娱乐自己', '0', '{\"title\":\"\\u7cd7\\u4e8b\\u767e\\u79d1\",\"width\":\"2\",\"display\":\"1\",\"cache_time\":\"60\"}', 'thinkphp', '0.1', '1418127212', '0');
INSERT INTO `onethink_addons` VALUES ('24', 'ReturnTop', '返回顶部', '回到顶部美化，随机或指定显示，100款样式，每天一种换，天天都用新样式', '1', '{\"random\":\"0\",\"current\":\"1\"}', 'thinkphp', '0.1', '1417694511', '0');

-- ----------------------------
-- Table structure for onethink_api_import_log
-- ----------------------------
DROP TABLE IF EXISTS `onethink_api_import_log`;
CREATE TABLE `onethink_api_import_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` tinyint(2) NOT NULL DEFAULT '2' COMMENT '类型(1表示文章,2表示下载,3表示主题,其他以后扩展)可以考虑用枚举',
  `did` int(10) unsigned NOT NULL COMMENT '类型id(文章/下载/主题相关id)',
  `update_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '更新类型(0表示新增,1表示更新)可以考虑用枚举',
  `content` text NOT NULL COMMENT '更新内容',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '用户状态',
  PRIMARY KEY (`id`),
  KEY `did` (`did`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='导入日志表';

-- ----------------------------
-- Records of onethink_api_import_log
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_attachment
-- ----------------------------
DROP TABLE IF EXISTS `onethink_attachment`;
CREATE TABLE `onethink_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `title` char(30) NOT NULL DEFAULT '' COMMENT '附件显示名',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件类型',
  `source` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资源ID',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联记录ID',
  `download` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '附件大小',
  `dir` int(12) unsigned NOT NULL DEFAULT '0' COMMENT '上级目录ID',
  `sort` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `idx_record_status` (`record_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='附件表';

-- ----------------------------
-- Records of onethink_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_attribute
-- ----------------------------
DROP TABLE IF EXISTS `onethink_attribute`;
CREATE TABLE `onethink_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '字段注释',
  `field` varchar(100) NOT NULL DEFAULT '' COMMENT '字段定义',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '数据类型',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '字段默认值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `extra` text NOT NULL COMMENT '参数',
  `model_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模型id',
  `save_type` varchar(20) NOT NULL DEFAULT '' COMMENT '存储处理类型\r\n',
  `save_extra` varchar(255) NOT NULL DEFAULT '' COMMENT '存储处理参数',
  `is_must` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `validate_rule` varchar(255) NOT NULL,
  `validate_time` tinyint(1) unsigned NOT NULL,
  `error_info` varchar(100) NOT NULL,
  `validate_type` varchar(25) NOT NULL,
  `auto_rule` varchar(100) NOT NULL,
  `auto_time` tinyint(1) unsigned NOT NULL,
  `auto_type` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=671 DEFAULT CHARSET=utf8 COMMENT='模型属性表';

-- ----------------------------
-- Records of onethink_attribute
-- ----------------------------
INSERT INTO `onethink_attribute` VALUES ('1', 'uid', '用户ID', 'int(10) unsigned NOT NULL ', 'num', '0', '', '0', '', '1', '', '', '0', '1', '1384508362', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('2', 'name', '标识', 'char(40) NOT NULL ', 'string', '', '同一根节点下标识不重复', '1', '', '1', '', '', '0', '1', '1383894743', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('3', 'title', '标题', 'char(80) NOT NULL ', 'string', '', '文档标题', '1', '', '1', '', '', '0', '1', '1383894778', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('4', 'category_id', '所属分类', 'int(10) unsigned NOT NULL ', 'num', '', '', '0', '', '1', '', '', '0', '1', '1413013384', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('5', 'description', '描述', 'varchar(500) NOT NULL ', 'textarea', '', '', '1', '', '1', '', '', '0', '1', '1416193455', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('6', 'root', '根节点', 'int(10) unsigned NOT NULL ', 'num', '0', '该文档的顶级文档编号', '0', '', '1', '', '', '0', '1', '1384508323', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('7', 'pid', '所属ID', 'int(10) unsigned NOT NULL ', 'num', '0', '父文档编号', '0', '', '1', '', '', '0', '1', '1384508543', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('8', 'model_id', '内容模型ID', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '该文档所对应的模型', '0', '', '1', '', '', '0', '1', '1384508350', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('9', 'type', '内容类型', 'tinyint(3) unsigned NOT NULL ', 'select', '2', '', '1', '1:目录\r\n2:主题\r\n3:段落', '1', '', '', '0', '1', '1384511157', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('10', 'position', '推荐位', 'smallint(5) unsigned NOT NULL ', 'checkbox', '0', '多个推荐则将其推荐值相加', '1', '[DOCUMENT_POSITION]', '1', '', '', '0', '1', '1383895640', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('11', 'link', '外链', 'varchar(500) NOT NULL ', 'string', '', '填写完整外链地址', '1', '', '1', '', '', '0', '1', '1414662990', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('12', 'cover_id', '封面', 'int(10) unsigned NOT NULL ', 'picture', '0', '0-无封面，大于0-封面图片ID，需要函数处理', '1', '', '1', '', '', '0', '1', '1384147827', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('13', 'display', '可见性', 'tinyint(3) unsigned NOT NULL ', 'radio', '1', '', '1', '0:不可见\r\n1:所有人可见', '1', '', '', '0', '1', '1386662271', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('14', 'deadline', '截至时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '0-永久有效', '1', '', '1', '', '', '0', '1', '1387163248', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('15', 'attach', '附件数量', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '', '0', '', '1', '', '', '0', '1', '1387260355', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('16', 'view', '浏览量', 'int(10) unsigned NOT NULL ', 'num', '0', '', '1', '', '1', '', '', '0', '1', '1383895835', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('17', 'comment', '评论数', 'int(10) unsigned NOT NULL ', 'num', '0', '', '1', '', '1', '', '', '0', '1', '1383895846', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('18', 'extend', '扩展统计字段', 'int(10) unsigned NOT NULL ', 'num', '0', '根据需求自行使用', '0', '', '1', '', '', '0', '1', '1384508264', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('19', 'level', '优先级', 'int(10) unsigned NOT NULL ', 'num', '0', '越高排序越靠前', '1', '', '1', '', '', '0', '1', '1383895894', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('20', 'create_time', '创建时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', '1', '', '1', '', '', '0', '1', '1383895903', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('21', 'update_time', '更新时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', '0', '', '1', '', '', '0', '1', '1384508277', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('22', 'status', '数据状态', 'tinyint(4) NOT NULL ', 'radio', '1', '', '1', '-1:删除\r\n0:禁用\r\n1:正常\r\n2:待审核\r\n3:草稿', '1', '', '', '0', '1', '1430121497', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('23', 'parse', '内容解析类型', 'tinyint(3) unsigned NOT NULL ', 'select', '0', '', '0', '0:html\r\n1:ubb\r\n2:markdown', '2', '', '', '0', '1', '1384511049', '1383891243', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('24', 'content', '文章内容', 'text NOT NULL ', 'editor', '', '', '1', '', '2', '', '', '0', '1', '1383896225', '1383891243', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('25', 'template', '详情页显示模板', 'varchar(100) NOT NULL ', 'string', '', '参照display方法参数的定义', '1', '', '2', '', '', '0', '1', '1383896190', '1383891243', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('26', 'bookmark', '收藏数', 'int(10) unsigned NOT NULL ', 'num', '0', '', '1', '', '2', '', '', '0', '1', '1383896103', '1383891243', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('154', 'level', '优先级', 'int(10) unsigned NOT NULL ', 'num', '0', '越高排序越靠前', '1', '', '12', '', '', '0', '1', '1383895894', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('153', 'extend', '扩展统计字段', 'int(10) unsigned NOT NULL ', 'num', '0', '根据需求自行使用', '0', '', '12', '', '', '0', '1', '1384508264', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('152', 'comment', '评论数', 'int(10) unsigned NOT NULL ', 'num', '0', '', '1', '', '12', '', '', '0', '1', '1383895846', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('151', 'view', '点击总数', 'int(10) unsigned NOT NULL ', 'num', '0', '点击数', '1', '', '12', '', '', '0', '1', '1415774827', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('150', 'attach', '附件数量', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '', '0', '', '12', '', '', '0', '1', '1387260355', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('33', 'sub_title', '副标题', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '2', '', '', '0', '1', '1410506797', '1410506797', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('34', 'font', '标题字体', 'char(10) NOT NULL', 'select', '', '', '1', '[FIELD_DOCUMENT_FONT]', '2', '', '', '0', '1', '1415587854', '1410507086', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('35', 'font_color', '标题颜色', 'char(50) NOT NULL', 'select', '', '', '1', '[FIELD_DOCUMENT_FONT_COLOR]', '2', '', '', '0', '1', '1415587440', '1410507406', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('193', 'pid', '父页面ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '1', '', '11', '', '', '0', '1', '1415439283', '1415439283', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('194', 'uid', '', 'int(10) NOT NULL ', 'string', '', '', '1', '', '15', '', '', '0', '1', '1415454462', '1415454462', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('195', 'document_id', '', 'int(10) NOT NULL ', 'string', '', '', '1', '', '15', '', '', '0', '1', '1415454463', '1415454463', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('196', 'message', '评论内容', 'text NOT NULL ', 'string', '', '', '1', '', '15', '', '', '0', '1', '1415456637', '1415454463', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('39', 'author', '作者', 'varchar(255) NOT NULL', 'stringForConfig', '', '', '1', 'ARTICLE_ADVANCE_AUTHOR', '2', '', '', '0', '1', '1412925376', '1410743814', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('40', 'source', '出处', 'varchar(255) NOT NULL', 'stringForConfig', '', '', '1', 'ARTICLE_ADVANCE_SOURCE', '2', '', '', '0', '1', '1411438873', '1410743853', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('41', 'source_url', '出处网址', 'varchar(255) NOT NULL', 'stringForConfig', '', '', '1', 'ARTICLE_ADVANCE_URL', '2', '', '', '0', '1', '1416196740', '1410743898', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('43', 'title_pinyin', '标题首字母', 'varchar(255) NOT NULL', 'string', '', '', '0', '', '1', 'function', 'get_pinyin_first|title|true', '0', '1', '1413511354', '1411440985', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('149', 'deadline', '截至时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '0-永久有效', '1', '', '12', '', '', '0', '1', '1387163248', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('148', 'display', '可见性', 'tinyint(3) unsigned NOT NULL ', 'radio', '1', '', '1', '0:不可见\r\n1:所有人可见', '12', '', '', '0', '1', '1386662271', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('147', 'cover_id', '封面横图', 'int(10) unsigned NOT NULL ', 'picture', '0', '0-无封面，大于0-封面图片ID，需要函数处理', '1', '', '12', '', '', '0', '1', '1418117134', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('146', 'link', '外链', 'varchar(500) NOT NULL ', 'string', '', '填写完整外链地址', '1', '', '12', '', '', '0', '1', '1414663075', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('145', 'position', '推荐位', 'smallint(5) unsigned NOT NULL ', 'checkbox', '0', '多个推荐则将其推荐值相加', '1', '[FIELD_DOWN_POSITION]', '12', '', '', '0', '1', '1416466931', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('144', 'type', '内容类型', 'tinyint(3) unsigned NOT NULL ', 'select', '2', '', '1', '1:目录\r\n2:主题\r\n3:段落', '12', '', '', '0', '1', '1384511157', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('143', 'model_id', '内容模型ID', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '该文档所对应的模型', '0', '', '12', '', '', '0', '1', '1384508350', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('142', 'pid', '所属ID', 'int(10) unsigned NOT NULL ', 'num', '0', '父文档编号', '0', '', '12', '', '', '0', '1', '1384508543', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('141', 'root', '根节点', 'int(10) unsigned NOT NULL ', 'num', '0', '该文档的顶级文档编号', '0', '', '12', '', '', '0', '1', '1384508323', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('140', 'description', '简介', 'text NOT NULL', 'textarea', '', '', '1', '', '12', '', '', '0', '1', '1419300085', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('139', 'category_id', '所属分类', 'int(10) unsigned NOT NULL ', 'num', '', '', '0', '', '12', '', '', '0', '1', '1413013384', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('138', 'title', '标题', 'char(80) NOT NULL ', 'string', '', '文档标题', '1', '', '12', '', '', '0', '1', '1383894778', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('137', 'name', '标识', 'char(40) NOT NULL ', 'string', '', '同一根节点下标识不重复', '1', '', '12', '', '', '0', '1', '1383894743', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('68', 'uid', '用户ID', 'int(10) unsigned NOT NULL ', 'num', '0', '', '0', '', '7', '', '', '0', '1', '1413013465', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('69', 'name', '标识', 'char(40) NOT NULL ', 'string', '', '', '1', '', '7', '', '', '0', '1', '1413010314', '1413010314', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('70', 'title', '标题', 'char(80) NOT NULL ', 'string', '', '数据名称', '1', '', '7', '', '', '0', '1', '1413878474', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('71', 'category_id', '所属分类', 'int(10) unsigned NOT NULL ', 'num', '', '', '0', '', '7', '', '', '0', '1', '1413013314', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('72', 'description', '描述', 'char(140) NOT NULL ', 'textarea', '', '', '1', '', '7', '', '', '0', '1', '1413013295', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('73', 'root', '根节点', 'int(10) unsigned NOT NULL ', 'num', '0', '该文档的顶级文档编号', '0', '', '7', '', '', '0', '1', '1413013279', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('74', 'pid', '所属ID', 'int(10) unsigned NOT NULL ', 'num', '0', '父文档编号', '0', '', '7', '', '', '0', '1', '1413013223', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('75', 'model_id', '内容模型ID', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '模型ID', '0', '', '7', '', '', '0', '1', '1413013202', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('76', 'type', '内容类型', 'tinyint(3) unsigned NOT NULL ', 'select', '2', '', '1', '1:目录\r\n2:主题\r\n3:段落', '7', '', '', '0', '1', '1413013176', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('77', 'position', '推荐位', 'smallint(5) unsigned NOT NULL ', 'checkbox', '0', '多个推荐则将其推荐值相加', '1', '[FIELD_PACKAGE_POSITION]', '7', '', '', '0', '1', '1416798243', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('78', 'link', '外链', 'varchar(500) NOT NULL', 'string', '', '填写完整外链地址', '1', '', '7', '', '', '0', '1', '1414663092', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('183', 'language', '语言', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_LANGUAGE]', '13', '', '', '0', '1', '1415678855', '1415084421', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('79', 'cover_id', 'Logo图', 'int(10) unsigned NOT NULL ', 'picture', '0', '（封面竖图）0-无封面，大于0-封面图片ID，需要函数处理', '1', '', '7', '', '', '0', '1', '1418118969', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('80', 'display', '可见性', 'tinyint(3) unsigned NOT NULL ', 'radio', '1', '', '1', '0:不可见\r\n1:所有人可见', '7', '', '', '0', '1', '1413011623', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('81', 'deadline', '截至时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '0-永久有效', '1', '', '7', '', '', '0', '1', '1413011558', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('82', 'attach', '附件数量', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '', '0', '', '7', '', '', '0', '1', '1413010880', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('83', 'view', '浏览量', 'int(10) unsigned NOT NULL ', 'num', '0', '', '1', '', '7', '', '', '0', '1', '1413010853', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('84', 'comment', '评论数', 'int(10) unsigned NOT NULL ', 'num', '0', '', '1', '', '7', '', '', '0', '1', '1413010839', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('85', 'extend', '扩展统计字段', 'int(10) unsigned NOT NULL ', 'num', '0', '根据需求自行使用', '0', '', '7', '', '', '0', '1', '1413010817', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('86', 'level', '优先级', 'int(10) unsigned NOT NULL ', 'num', '0', '越高排序越靠前', '1', '', '7', '', '', '0', '1', '1413010758', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('87', 'create_time', '创建时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', '1', '', '7', '', '', '0', '1', '1413010722', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('88', 'update_time', '更新时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', '0', '', '7', '', '', '0', '1', '1413010706', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('89', 'status', '数据状态', 'tinyint(4) NOT NULL ', 'radio', '0', '', '1', '-1:删除\r\n0:禁用\r\n1:正常\r\n2:待审核\r\n3:草稿', '7', '', '', '0', '1', '1427850289', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('90', 'title_pinyin', '标题首字母', 'varchar(255) NOT NULL ', 'string', '', '', '0', '', '7', 'function', 'get_pinyin_first|title|true', '0', '1', '1413511402', '1413010314', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('91', 'activation', '礼包激活', 'text NOT NULL', 'editor', '', '', '1', '', '8', '', '', '0', '1', '1420786963', '1413014891', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('92', 'content', '礼包内容', 'text NOT NULL', 'editor', '', '', '1', '', '8', '', '', '0', '1', '1414482385', '1413197241', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('93', 'card_type', '卡号类型', 'char(10) NOT NULL', 'radio', '', '', '1', '[FIELD_PACKAGE_CARD_TYPE]', '8', '', '', '0', '1', '1423618684', '1413251780', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('94', 'end_time', '结束时间', 'int(10) NOT NULL', 'datetime', '', '', '1', '', '8', '', '', '0', '1', '1414047182', '1413251892', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('95', 'start_time', '开始时间', 'int(10) NOT NULL', 'datetime', '', '', '1', '', '8', '', '', '0', '1', '1414047174', '1413251909', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('96', 'conditions', '运行环境', 'varchar(100) NOT NULL', 'checkbox', '', '', '1', '[FIELD_PACKAGE_CONDITIONS]', '8', '', '', '0', '1', '1415609695', '1413251954', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('97', 'platform', '运营平台', 'varchar(255) NOT NULL', 'string', '', '', '1', '[FIELD_PACKAGE_PLATFORM]', '8', '', '', '0', '1', '1415610344', '1413251981', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('650', 'conditions', '运行环境', 'varchar(100) NOT NULL', 'checkbox', '1', '', '1', '[FIELD_PACKAGE_CONDITIONS]', '14', '', '', '0', '1', '1421048485', '1421048485', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('649', 'abet', '赞', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '7', '', '', '0', '1', '1420853565', '1420853565', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('642', 'home_position', '全站推荐位', 'varchar(100) NOT NULL', 'checkbox', '0', '', '1', '[FIELD_HOME_POSITION]', '1', '', '', '0', '1', '1417597323', '1417596803', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('641', 'home_position', '全站推荐位', 'varchar(100) NOT NULL', 'checkbox', '', '全站推荐位', '1', '[FIELD_HOME_POSITION]', '7', '', '', '0', '1', '1417589393', '1417589393', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('646', 'vertical_pic', '首页推荐竖图', 'int(10) UNSIGNED NOT NULL', 'picture', '', '首页推荐竖图', '1', '', '1', '', '', '0', '1', '1418438079', '1418438079', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('637', 'keywords', '关键字', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '17', '', '', '0', '1', '1416389983', '1416389983', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('444', 'update_time', '更新时间', 'timestamp NOT NULL ', 'datetime', 'CURRENT_TIMESTAMP', '', '2', '', '18', '', '', '0', '1', '1416817475', '1416817085', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('442', 'sort', '顺序', 'int(10) unsigned NOT NULL ', 'num', '', '', '1', '', '18', '', '', '0', '1', '1438222257', '1416817085', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('441', 'topic_count', '计数', 'int(10) unsigned NOT NULL ', 'num', '0', '', '0', '', '18', '', '', '0', '1', '1438222290', '1416817085', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('440', 'icon', '链接图标', 'varchar(255) NOT NULL ', 'picture', '', '', '1', '', '18', '', '', '0', '1', '1416817844', '1416817085', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('123', 'path_detail', '静态文件路径', 'varchar(255) NOT NULL', 'string', '', '默认分类的路径', '1', '', '1', '', '', '0', '1', '1414036004', '1413527314', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('124', 'path_detail', '静态文件路径', 'varchar(255) NOT NULL', 'string', '', '默认分类的路径', '1', '', '7', '', '', '0', '1', '1414035981', '1413527376', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('438', 'title', '标题', 'varchar(120) NOT NULL ', 'string', '', '', '1', '', '18', '', '', '0', '1', '1416817084', '1416817084', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('439', 'url_token', '链接地址', 'varchar(32) NOT NULL ', 'string', '', '', '1', '', '18', '', '', '0', '1', '1416817085', '1416817085', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('126', 'title', '专题标题', 'varchar(200) NOT NULL ', 'string', '', '', '1', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('127', 'description', '专题描述', 'varchar(255) NOT NULL ', 'string', '', '', '1', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('128', 'content', '专题内容', 'text NOT NULL ', 'editor', '', '', '1', '', '11', '', '', '0', '1', '1413603791', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('129', 'icon', '专题图标', 'varchar(255) NOT NULL ', 'picture', '', '', '1', '', '11', '', '', '0', '1', '1413603842', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('130', 'topic_count', '话题计数', 'int(10) unsigned NOT NULL ', 'string', '0', '', '1', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('131', 'label', '自定义', 'varchar(255) NOT NULL ', 'string', '', '', '0', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('132', 'url_token', '链接地址', 'varchar(32) NOT NULL ', 'string', '', '', '1', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('133', 'seo_title', 'SEO标题', 'varchar(255) NOT NULL ', 'string', '', '', '1', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('134', 'sort', '排序', 'int(10) unsigned NOT NULL ', 'string', '', '', '1', '', '11', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('135', 'enabled', '开启', 'tinyint(1) unsigned NOT NULL ', 'bool', '1', '', '3', '0:禁用\r\n1:启用', '11', '', '', '0', '1', '1413596792', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('136', 'uid', '用户ID', 'int(10) unsigned NOT NULL ', 'num', '0', '', '0', '', '12', '', '', '0', '1', '1384508362', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('155', 'create_time', '创建时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', '1', '', '12', '', '', '0', '1', '1426143835', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('156', 'update_time', '更新时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', '0', '', '12', '', '', '0', '1', '1384508277', '1383891233', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('157', 'status', '数据状态', 'tinyint(4) NOT NULL ', 'radio', '1', '', '1', '-1:删除\r\n0:禁用\r\n1:正常\r\n2:待审核\r\n3:草稿', '12', '', '', '0', '1', '1430121158', '1383891233', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('158', 'title_pinyin', '标题首字母', 'varchar(255) NOT NULL', 'string', '', '', '0', '', '12', 'function', 'get_pinyin_first|title|true', '0', '1', '1413511354', '1411440985', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('159', 'path_detail', '静态文件路径', 'varchar(255) NOT NULL', 'string', '', '默认分类的路径', '1', '', '12', '', '', '0', '1', '1414036021', '1413527314', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('160', 'content', '介绍', 'text NOT NULL', 'editor', '', '', '1', '', '13', '', '', '0', '1', '1415263478', '1413773416', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('161', 'version', '版本', 'varchar(100) NOT NULL', 'string', '', '', '1', '', '13', '', '', '0', '1', '1413786925', '1413786925', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('162', 'font', '标题字体', 'char(50) NOT NULL', 'select', '0', '', '1', '0:正常\r\n1:粗体\r\n2:斜体\r\n3:粗体+斜体', '13', '', '', '0', '1', '1415878735', '1413787055', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('163', 'font_color', '标题颜色', 'char(50) NOT NULL', 'select', '0', '', '1', '0:正常\r\n1:红色\r\n2:黄色', '13', '', '', '0', '1', '1415878719', '1413787104', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('164', 'size', '文件大小', 'int(10) UNSIGNED NOT NULL', 'num', '', '单位为KB', '1', '', '13', '', '', '0', '1', '1419303872', '1413788089', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('165', 'sub_title', '副标题', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '13', '', '', '0', '1', '1413788433', '1413788433', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('166', 'conductor', '导读', 'text NOT NULL', 'textarea', '', '', '1', '', '13', '', '', '0', '1', '1413790001', '1413789973', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('167', 'system', '平台', 'char(50) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_SYSTEM]', '13', '', '', '0', '1', '1416541553', '1413790381', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('201', 'system_version', '平台版本', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '13', '', '', '0', '1', '1417143588', '1415582885', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('202', 'seo_title', 'SEO标题', 'varchar(500) NOT NULL', 'string', '', '', '1', '', '12', '', '', '0', '1', '1415601103', '1415601103', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('168', 'rank', '等级', 'char(50) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_RANK]', '13', '', '', '0', '1', '1415678877', '1413790592', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('169', 'data_type', '性质', 'char(50) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_DATA_TYPE]', '13', '', '', '0', '1', '1415678870', '1413793552', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('170', 'licence', '授权', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_LICENCE]', '13', '', '', '0', '1', '1415678862', '1413794719', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('171', 'start_time', '开测时间', 'int(10) NOT NULL', 'datetime', '', '', '1', '', '14', '', '', '0', '1', '1421109731', '1413878407', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('172', 'server', '测试类型', 'varchar(255) NOT NULL', 'string', '', '开服的服务器', '1', '', '14', '', '', '0', '1', '1421109789', '1413878512', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('173', 'game_type', '游戏类型', 'smallint(5) unsigned NOT NULL ', 'checkbox', '', '', '1', '[FIELD_PACKAGE_GAME_TYPE]', '14', '', '', '0', '1', '1421033271', '1413878612', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('174', 'server_type', '当前情况', 'char(10) NOT NULL', 'radio', '', '', '1', '[PACKAGE_PARTICLE_SERVER_TYPE]', '14', '', '', '0', '1', '1414145590', '1413878669', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('177', 'seo_title', 'SEO标题', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '7', '', '', '0', '1', '1414138929', '1414138929', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('176', 'layout', '选择模版', 'varchar(255) NOT NULL', 'select', '默认', '', '1', './Application/Home/View/7230/Feature/layout/default.htm:default.htm\r\n./Application/Home/View/7230/Feature/layout/hjzt.html:hjzt.html\r\n./Application/Home/View/7230/Feature/layout/jxhj.html:jxhj.html\r\n./Application/Home/View/7230/Feature/layout/k.html:k.html\r\n./Application/Home/View/7230/Feature/layout/k1.htm:k1.htm\r\n./Application/Home/View/7230/Feature/layout/ldsw.html:ldsw.html\r\n./Application/Home/View/7230/Feature/layout/rjhj.html:rjhj.html\r\n./Application/Home/View/7230/Feature/layout/sjjxhj.html:sjjxhj.html\r\n./Application/Home/View/7230/Feature/layout/syzb.html:syzb.html\r\n', '11', '', '', '0', '1', '1414032590', '1414032590', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('178', 'seo_key', 'SEO关键词', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '7', '', '', '0', '1', '1414138948', '1414138948', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('179', 'seo_description', 'SEO描述', 'text NOT NULL', 'textarea', '', '', '1', '', '7', '', '', '0', '1', '1416993015', '1414138974', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('643', 'vertical_pic', '封面竖图', 'int(10) UNSIGNED NOT NULL', 'picture', '0', '', '1', '', '12', '', '', '0', '1', '1418116679', '1418116679', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('644', 'vertical_pic', '封面竖图', 'int(10) UNSIGNED NOT NULL', 'picture', '', '封面竖图', '1', '', '7', '', '', '0', '1', '1418118928', '1418118928', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('211', 'date_month', '月点击数开始时间', 'int(10) UNSIGNED NOT NULL', 'datetime', '', '', '1', '', '12', '', '', '0', '1', '1415864493', '1415773321', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('182', 'widget', '挂件', 'varchar(255) NOT NULL', 'string', '', '', '0', '', '11', '', '', '0', '1', '1414652687', '1414652687', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('208', 'hits_month', '月点击数', 'int(10) UNSIGNED NOT NULL', 'num', '', '', '1', '', '12', '', '', '0', '1', '1415772499', '1415772499', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('209', 'hits_week', '周点击数', 'int(10) UNSIGNED NOT NULL', 'num', '', '', '1', '', '12', '', '', '0', '1', '1415772532', '1415772532', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('210', 'hits_today', '天点击数', 'int(10) UNSIGNED NOT NULL', 'num', '', '', '1', '', '12', '', '', '0', '1', '1415772565', '1415772565', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('184', 'author_url', '官网', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '13', '', '', '0', '1', '1415697854', '1415239092', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('185', 'abet', '好', 'int(10) UNSIGNED NOT NULL', 'num', '', '好评，赞', '1', '', '12', '', '', '0', '1', '1415239241', '1415239241', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('186', 'argue', '差', 'int(10) UNSIGNED NOT NULL', 'num', '', '差评，反对', '1', '', '12', '', '', '0', '1', '1415239270', '1415239270', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('187', 'smallimg', 'Logo图', 'int(10) UNSIGNED NOT NULL', 'picture', '0', '', '1', '', '12', '', '', '0', '1', '1418116014', '1415239367', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('188', 'previewimg', '预览多图', 'varchar(255) NOT NULL', 'multipicture', '', '', '1', '', '12', '', '', '0', '1', '1416017663', '1415241872', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('189', 'keytext', '特别', 'text NOT NULL', 'editor', '', '', '0', '', '13', '', '', '0', '1', '1448509892', '1415263502', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('190', 'seo_title', 'SEO标题', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '1', '', '', '0', '1', '1415348508', '1415348324', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('191', 'seo_keywords', 'SEO关键词', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '1', '', '', '0', '1', '1415348500', '1415348347', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('192', 'seo_description', 'SEO描述', 'text NOT NULL', 'textarea', '', '', '1', '', '1', '', '', '0', '1', '1415348493', '1415348370', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('197', 'add_time', '', 'int(10) NOT NULL ', 'string', '', '', '1', '', '15', '', '', '0', '1', '1415454463', '1415454463', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('198', 'at_uid', '@用户', 'int(10) NULL ', 'num', '', '', '1', '', '15', '', '', '0', '1', '1415456622', '1415454463', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('199', 'votes', '投票', 'int(10) NULL ', 'string', '0', '', '1', '', '15', '', '', '0', '1', '1415456592', '1415454463', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('200', 'enabled', '是否启用', 'tinyint', 'radio', '', '', '1', '0:禁用\r\n1:审核通过', '15', '', '', '0', '1', '1415456570', '1415455425', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('203', 'seo_keywords', 'SEO关键词', 'varchar(500) NOT NULL', 'string', '', '', '1', '', '12', '', '', '0', '1', '1415601127', '1415601127', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('204', 'seo_description', 'SEO描述', 'text NOT NULL', 'textarea', '', '', '1', '', '12', '', '', '0', '1', '1415601150', '1415601150', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('205', 'brecom_id', '推荐大图', 'int(10) UNSIGNED NOT NULL', 'picture', '', '礼包频道推左上荐位（687*245）', '1', '', '7', '', '', '0', '1', '1415612645', '1415612645', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('206', 'srecom_id', '推荐小图', 'int(10) UNSIGNED NOT NULL', 'picture', '', '礼包频道推右上荐位（240*120）', '1', '', '7', '', '', '0', '1', '1415612698', '1415612698', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('207', 'network', '联网', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_NETWORK]', '13', '', '', '0', '1', '1416539707', '1415678688', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('212', 'date_week', '周点击数开始时间', 'int(10) UNSIGNED NOT NULL', 'datetime', '', '', '1', '', '12', '', '', '0', '1', '1415864487', '1415773372', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('213', 'date_today', '天点击数开始时间', 'int(10) UNSIGNED NOT NULL', 'datetime', '', '', '1', '', '12', '', '', '0', '1', '1415864481', '1415773403', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('214', 'company_id', '厂商', 'int(10) UNSIGNED NOT NULL', 'idForTable', '', '', '1', 'Company:id:name', '13', '', '', '0', '1', '1419299070', '1415774405', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('215', 'audit', '审核', 'char(10) NOT NULL', 'radio', '', '', '1', '0:未审核\r\n1:已审核', '12', '', '', '0', '1', '1415775936', '1415775936', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('216', 'old_id', '老数据ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '新数据为0', '0', '', '12', '', '', '0', '1', '1415864127', '1415864127', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('218', 'video', '是否包含视频', 'char(10) NOT NULL', 'radio', '', '', '1', '[FIELD_DOCUMENT_VIDEO]', '1', '', '', '0', '1', '1415878080', '1415878080', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('219', 'category_rootid', '栏目根ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '栏目根ID', '0', '', '12', '', '', '0', '1', '1416196110', '1416191245', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('220', 'category_rootid', '栏目根ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '栏目根ID', '0', '', '1', '', '', '0', '1', '1416196069', '1416191324', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('653', 'interface', '界面', 'tinyint(1) UNSIGNED NOT NULL', 'select', '0', '', '1', '0:电脑版\r\n1:触屏版', '17', '', '', '0', '1', '1421999419', '1421999352', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('576', 'layout', '选择模版', 'varchar(255) NOT NULL', 'select', '默认', '', '1', './Application/Home/View/7230/Special/layout/default.htm:default.htm\r\n./Application/Home/View/7230/Special/layout/hjzt.html:hjzt.html\r\n./Application/Home/View/7230/Special/layout/hjztsj.html:hjztsj.html\r\n./Application/Home/View/7230/Special/layout/k.html:k.html\r\n./Application/Home/View/7230/Special/layout/k2.html:k2.html\r\n./Application/Home/View/7230/Special/layout/syzb.html:syzb.html\r\n', '17', '', '', '0', '1', '1414032590', '1414032590', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('226', 'picture_score', '画面分', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FILD_DOWN_PICTURE_SCORE]', '13', '', '', '0', '1', '1416294604', '1416294604', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('227', 'music_score', '音乐分', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FILD_DOWN_MUSIC_SCORE]', '13', '', '', '0', '1', '1416294807', '1416294807', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('228', 'feature_score', '特色分', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FILD_DOWN_FEATURE_SCORE]', '13', '', '', '0', '1', '1416294906', '1416294906', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('229', 'run_score', '运行分', 'char(10) NOT NULL', 'radio', '1', '', '1', '[FILD_DOWN_RUN_SCORE]', '13', '', '', '0', '1', '1416295619', '1416294977', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('230', 'home_position', '全站推荐位', 'varchar(100) NOT NULL', 'checkbox', '', '全站推荐位', '1', '[FIELD_HOME_POSITION]', '12', '', '', '0', '1', '1416296008', '1416296008', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('231', 'pagination_type', '分页类型', 'char(10) NOT NULL', 'radio', '0', '', '1', '0:不会\r\n1:手动\r\n2:自动', '1', '', '', '0', '1', '1416309761', '1416305282', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('233', 'files', '附件', 'varchar(255) NOT NULL', 'multifile', '', '可以上传多个附件', '1', '', '1', '', '', '0', '1', '1416316620', '1416313818', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('234', 'ding', '顶', 'int(10) UNSIGNED NOT NULL', 'num', '', '', '1', '', '1', '', '', '0', '1', '1416315092', '1416315092', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('235', 'cai', '踩', 'int(10) UNSIGNED NOT NULL', 'num', '', '', '1', '', '1', '', '', '0', '1', '1416315104', '1416315104', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('236', 'old_id', '老数据ID', 'int(10) UNSIGNED NOT NULL', 'num', '', '', '0', '', '1', '', '', '0', '1', '1417830843', '1416315741', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('237', 'keywords', '关键字', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '11', '', '', '0', '1', '1416389983', '1416389983', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('648', 'status', '数据状态', 'tinyint(4) UNSIGNED NOT NULL', 'num', '1', '', '0', '', '16', '', '', '0', '1', '1419847852', '1419847852', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('535', 'enabled', '开启', 'tinyint(1) unsigned NOT NULL ', 'bool', '1', '', '3', '0:禁用\r\n1:启用', '17', '', '', '0', '1', '1413596792', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('534', 'sort', '排序', 'int(10) unsigned NOT NULL ', 'string', '', '', '1', '', '17', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('533', 'seo_title', 'SEO标题', 'varchar(255) NOT NULL ', 'string', '', '', '1', '', '17', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('532', 'url_token', '链接地址', 'varchar(32) NOT NULL ', 'string', '', '', '1', '', '17', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('531', 'label', '自定义', 'varchar(255) NOT NULL ', 'string', '', '', '0', '', '17', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('530', 'topic_count', '话题计数', 'int(10) unsigned NOT NULL ', 'string', '0', '', '1', '', '17', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('529', 'icon', '专题图标', 'varchar(255) NOT NULL ', 'picture', '', '', '1', '', '17', '', '', '0', '1', '1413603842', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('528', 'content', '专题内容', 'text NOT NULL ', 'editor', '', '', '1', '', '17', '', '', '0', '1', '1413603791', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('527', 'description', '专题描述', 'text NOT NULL ', 'textarea', '', '', '1', '', '17', '', '', '0', '1', '1419403416', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('526', 'title', '专题标题', 'varchar(200) NOT NULL ', 'string', '', '', '1', '', '17', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('593', 'pid', '父页面ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '1', '', '17', '', '', '0', '1', '1415439283', '1415439283', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('393', 'pid', '父页面ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '1', '', '16', '', '', '0', '1', '1415439283', '1415439283', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('326', 'title', '专题标题', 'varchar(200) NOT NULL ', 'string', '', '', '1', '', '16', '', '', '1', '1', '1421292177', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('327', 'description', '专题描述', 'varchar(255) NOT NULL ', 'string', '', '', '1', '', '16', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('328', 'content', '专题内容', 'text NOT NULL ', 'editor', '', '', '1', '', '16', '', '', '0', '1', '1413603791', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('329', 'icon', '专题图标', 'varchar(255) NOT NULL ', 'picture', '', '', '1', '', '16', '', '', '0', '1', '1413603842', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('330', 'topic_count', '话题计数', 'int(10) unsigned NOT NULL ', 'string', '0', '', '1', '', '16', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('331', 'label', '自定义', 'varchar(255) NOT NULL ', 'string', '', '', '0', '', '16', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('332', 'url_token', '链接地址', 'varchar(32) NOT NULL ', 'string', '', '', '1', '', '16', '', '', '1', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('333', 'seo_title', 'SEO标题', 'varchar(255) NOT NULL ', 'string', '', '', '1', '', '16', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('334', 'sort', '排序', 'int(10) unsigned NOT NULL ', 'string', '', '', '1', '', '16', '', '', '0', '1', '1413539103', '1413539103', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('335', 'enabled', '开启', 'tinyint(1) unsigned NOT NULL ', 'bool', '1', '', '3', '0:禁用\r\n1:启用', '16', '', '', '0', '1', '1413596792', '1413539103', '', '0', '', 'regex', '', '0', 'function');
INSERT INTO `onethink_attribute` VALUES ('376', 'layout', '选择模版', 'varchar(255) NOT NULL', 'select', '默认', '', '1', './Application/Home/View/7230/Batch/layout/default.html:default.html\r\n./Application/Home/View/7230/Batch/layout/sjzq1:sjzq1\r\n./Application/Home/View/7230/Batch/layout/sjzq1/index.html:sjzq1/index.html\r\n./Application/Home/View/7230/Batch/layout/sjzq1/list.html:sjzq1/list.html\r\n./Application/Home/View/7230/Batch/layout/zq1:zq1\r\n./Application/Home/View/7230/Batch/layout/zq1/ask.html:zq1/ask.html\r\n./Application/Home/View/7230/Batch/layout/zq1/down.html:zq1/down.html\r\n./Application/Home/View/7230/Batch/layout/zq1/guide.html:zq1/guide.html\r\n./Application/Home/View/7230/Batch/layout/zq1/index.html:zq1/index.html\r\n./Application/Home/View/7230/Batch/layout/zq1/info.html:zq1/info.html\r\n./Application/Home/View/7230/Batch/layout/zq1/news.html:zq1/news.html\r\n./Application/Home/View/7230/Batch/layout/zq1/video.html:zq1/video.html\r\n./Application/Home/View/7230/Batch/layout/zq2:zq2\r\n./Application/Home/View/7230/Batch/layout/zq2/index.html:zq2/index.html\r\n', '16', '', '', '0', '1', '1414032590', '1414032590', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('652', 'category_rootid', '栏目根ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '7', '', '', '0', '1', '1421819423', '1421812066', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('382', 'widget', '挂件', 'varchar(255) NOT NULL', 'string', '', '', '0', '', '16', '', '', '0', '1', '1414652687', '1414652687', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('437', 'keywords', '关键字', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '16', '', '', '0', '1', '1416389983', '1416389983', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('651', 'smallimg', '图鉴', 'int(10) UNSIGNED NOT NULL', 'picture', '0', '小图标', '1', '', '1', '', '', '0', '1', '1421398858', '1421398625', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('582', 'widget', '挂件', 'varchar(255) NOT NULL', 'string', '', '', '0', '', '17', '', '', '0', '1', '1414652687', '1414652687', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('647', 'abet', '赞', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '1', '', '16', '', '', '0', '1', '1419814787', '1419814787', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('638', 'label', '专区标记', 'varchar(255)', 'string', '0', '', '0', '', '16', '', '', '0', '1', '1421049263', '1416830095', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('654', 'interface', '界面', 'tinyint(1) UNSIGNED NOT NULL', 'select', '0', '', '1', '0:电脑版\r\n1:触屏版', '11', '', '', '0', '0', '1421999419', '1421999419', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('655', 'interface', '界面', 'tinyint(1) UNSIGNED NOT NULL', 'select', '0', '', '1', '0:电脑版\r\n1:触屏版', '16', '', '', '0', '0', '1421999419', '1421999419', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('656', 'group', '分组', 'char(50) NOT NULL', 'select', '1', '', '1', '[LINK_GROUP]', '18', '', '', '0', '1', '1423207914', '1423207914', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('657', 'status', '数据状态', 'char(10) NOT NULL', 'radio', '1', '', '0', '0:禁用\r\n1:正常', '18', '', '', '0', '1', '1423209734', '1423208933', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('658', 'soft_id', '软件ID', 'text NOT NULL', 'textarea', '', '软件ID', '1', '', '19', '', '', '0', '1', '1426062120', '1426062120', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('659', 'uid', '用户ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '16', '', '', '0', '1', '1426143727', '1426143727', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('660', 'uid', '用户ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '17', '', '', '0', '1', '1426143769', '1426143769', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('661', 'uid', '用户ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '11', '', '', '0', '1', '1426143803', '1426143803', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('662', 'edit_id', '编辑id', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '1', '', '', '0', '1', '1426238360', '1426238360', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('663', 'edit_id', '编辑者id', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', '0', '', '12', '', '', '0', '1', '1426238400', '1426238400', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('664', 'channel', '渠道', 'char(10) NOT NULL', 'radio', '', '', '1', '[FIELD_DOWN_CHANNEL]', '13', '', '', '0', '1', '1433299522', '1429584911', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('665', 'package_name', '安卓包名', 'varchar(255) NOT NULL', 'string', '', '', '0', '', '13', '', '', '0', '1', '1429835546', '1429835546', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('666', 'state', '运营状态', 'int(5) NOT NULL', 'radio', '1', '', '1', '[FIELD_DOWN_STATE]', '13', '', '', '0', '1', '1433226986', '1433226174', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('667', 'iconcorner', '角标', 'int(5) NOT NULL', 'radio', '0', '', '1', '[DOWN_ICON_CORNER]', '13', '', '', '0', '1', '1434076012', '1434075490', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('668', 'relative_down_url', '相关下载地址', 'varchar(255) NOT NULL', 'string', '', '用于东东助手获取下载地址', '1', '', '13', '', '', '0', '1', '1446012715', '1446012715', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('669', 'optimalemu', 'APP推荐引擎', 'int(2)', 'radio', '', '推荐安装引擎', '1', '1:入门\r\n2:中级\r\n4:极速\r\n8:高级', '13', '', '', '0', '1', '1446022242', '1446016785', '', '3', '', 'regex', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('670', 'suitemu', 'APP支持引擎', 'int(2)', 'checkbox', '', 'APP所支持的引擎', '1', '1:入门\r\n2:中级\r\n4:极速\r\n8:高级', '13', '', '', '0', '1', '1446017502', '1446016919', '', '3', '', 'regex', '', '3', 'function');
-- 图库
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(688, 'uid', '用户ID', 'int(10) unsigned NOT NULL ', 'num', '0', '', 0, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(689, 'name', '标识', 'char(40) NOT NULL ', 'string', '', '同一根节点下标识不重复', 1, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(690, 'title', '标题', 'char(80) NOT NULL ', 'string', '', '图库名称', 1, '', 3, '', '', 0, 1, 1436867666, 1436836387, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(691, 'category_id', '所属分类', 'int(10) unsigned NOT NULL ', 'num', '', '', 0, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(692, 'description', '描述', 'varchar(500) NOT NULL ', 'textarea', '', '', 1, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(693, 'root', '根节点', 'int(10) unsigned NOT NULL ', 'num', '0', '该文档的顶级文档编号', 0, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(694, 'pid', '所属ID', 'int(10) unsigned NOT NULL ', 'num', '0', '父文档编号', 0, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(695, 'model_id', '内容模型ID', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '该文档所对应的模型', 0, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(696, 'type', '内容类型', 'tinyint(3) unsigned NOT NULL ', 'select', '2', '', 1, '1:目录\r\n2:主题\r\n3:段落', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(697, 'position', '推荐位', 'smallint(5) unsigned NOT NULL ', 'checkbox', '0', '多个推荐则将其推荐值相加', 1, '[GALLERY_POSITION]', 3, '', '', 0, 1, 1437094723, 1436836387, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(698, 'link', '外链', 'varchar(500) NOT NULL ', 'string', '', '填写完整外链地址', 1, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(699, 'cover_id', '封面', 'int(10) unsigned NOT NULL ', 'picture', '0', '0-无封面，大于0-封面图片ID，需要函数处理', 1, '', 3, '', '', 0, 1, 1436836387, 1436836387, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(680, 'imgpack', '图库', 'text NOT NULL', 'textarea', '', '', 1, '', 4, '', '', 0, 1, 1436943695, 1436834300, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(700, 'display', '可见性', 'tinyint(3) unsigned NOT NULL ', 'radio', '1', '', 1, '0:不可见\r\n1:所有人可见', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(702, 'attach', '附件数量', 'tinyint(3) unsigned NOT NULL ', 'num', '0', '', 0, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', 'regex', '', 0, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(703, 'view', '浏览量', 'int(10) unsigned NOT NULL ', 'num', '0', '', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(704, 'comment', '评论数', 'int(10) unsigned NOT NULL ', 'num', '0', '', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(705, 'extend', '扩展统计字段', 'int(10) unsigned NOT NULL ', 'num', '0', '根据需求自行使用', 0, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(706, 'level', '优先级', 'int(10) unsigned NOT NULL ', 'num', '0', '越高排序越靠前', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(707, 'create_time', '创建时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(708, 'update_time', '更新时间', 'int(10) unsigned NOT NULL ', 'datetime', '0', '', 0, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(709, 'status', '数据状态', 'tinyint(4) NOT NULL ', 'radio', '0', '', 0, '-1:删除\r\n0:禁用\r\n1:正常\r\n2:待审核\r\n3:草稿', 3, '', '', 0, 1, 1436836388, 1436836388, '', 0, '', '', '', 0, '');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(710, 'title_pinyin', '标题首字母', 'varchar(255) NOT NULL', 'string', '', '', 0, '', 3, 'function', 'get_pinyin_first|title|true', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(712, 'vertical_pic', '首页推荐竖图', 'int(10) UNSIGNED NOT NULL', 'picture', '', '首页推荐竖图', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(713, 'path_detail', '静态文件路径', 'varchar(255) NOT NULL', 'string', '', '默认分类的路径', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(714, 'seo_title', 'SEO标题', 'varchar(255) NOT NULL', 'string', '', '', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(715, 'seo_keywords', 'SEO关键词', 'varchar(255) NOT NULL', 'string', '', '', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(716, 'seo_description', 'SEO描述', 'text NOT NULL', 'textarea', '', '', 1, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(718, 'category_rootid', '栏目根ID', 'int(10) UNSIGNED NOT NULL', 'num', '0', '栏目根ID', 0, '', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(719, 'pagination_type', '分页类型', 'char(10) NOT NULL', 'radio', '0', '', 1, '0:不会\r\n1:手动\r\n2:自动', 3, '', '', 0, 1, 1436836388, 1436836388, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(726, 'sub_title', '副标题', 'varchar(255) NOT NULL', 'string', '', '', 1, '', 4, '', '', 0, 1, 1436953308, 1436953308, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(721, 'ding', '顶', 'int(10) UNSIGNED NOT NULL', 'num', '', '', 1, '', 3, '', '', 0, 1, 1436836389, 1436836389, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(722, 'cai', '踩', 'int(10) UNSIGNED NOT NULL', 'num', '', '', 1, '', 3, '', '', 0, 1, 1436836389, 1436836389, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(723, 'old_id', '老数据ID', 'int(10) UNSIGNED NOT NULL', 'num', '', '', 0, '', 3, '', '', 0, 1, 1436836389, 1436836389, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(724, 'smallimg', '图鉴', 'int(10) UNSIGNED NOT NULL', 'picture', '0', '小图标', 1, '', 3, '', '', 0, 1, 1436836389, 1436836389, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(725, 'edit_id', '编辑id', 'int(10) UNSIGNED NOT NULL', 'num', '0', '', 0, '', 3, '', '', 0, 1, 1436836389, 1436836389, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(727, 'template', '详情页显示模板', 'varchar(255) NOT NULL', 'string', '', '', 1, '', 4, '', '', 0, 1, 1436953354, 1436953354, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(728, 'font', '标题字体', 'char(10) NOT NULL', 'select', '', '', 1, '[FIELD_DOCUMENT_FONT]', 4, '', '', 0, 1, 1436953403, 1436953403, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(729, 'author', '作者', 'varchar(255) NOT NULL', 'stringForConfig', '', '', 1, 'ARTICLE_ADVANCE_AUTHOR', 4, '', '', 0, 1, 1436953457, 1436953457, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(730, 'source', '出处', 'varchar(255) NOT NULL', 'stringForConfig', '', '', 1, 'ARTICLE_ADVANCE_SOURCE', 4, '', '', 0, 1, 1436953491, 1436953491, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(731, 'source_url', '出处网址', 'varchar(255) NOT NULL', 'stringForConfig', '', '', 1, 'ARTICLE_ADVANCE_URL', 4, '', '', 0, 1, 1436953532, 1436953532, '', 3, '', 'regex', '', 3, 'function');
INSERT INTO `onethink_attribute` (`id`, `name`, `title`, `field`, `type`, `value`, `remark`, `is_show`, `extra`, `model_id`, `save_type`, `save_extra`, `is_must`, `status`, `update_time`, `create_time`, `validate_rule`, `validate_time`, `error_info`, `validate_type`, `auto_rule`, `auto_time`, `auto_type`) VALUES(732, 'font_color', '标题颜色', 'char(50) NOT NULL', 'select', '', '', 1, '[FIELD_DOCUMENT_FONT_COLOR]', 4, '', '', 0, 1, 1436953572, 1436953572, '', 3, '', 'regex', '', 3, 'function');


-- ----------------------------
-- Table structure for onethink_auth_extend
-- ----------------------------
DROP TABLE IF EXISTS `onethink_auth_extend`;
CREATE TABLE `onethink_auth_extend` (
  `group_id` mediumint(10) unsigned NOT NULL COMMENT '用户id',
  `extend_id` mediumint(8) unsigned NOT NULL COMMENT '扩展表中数据的id',
  `type` tinyint(1) unsigned NOT NULL COMMENT '扩展类型标识 1:栏目分类权限;2:模型权限',
  UNIQUE KEY `group_extend_type` (`group_id`,`extend_id`,`type`),
  KEY `uid` (`group_id`),
  KEY `group_id` (`extend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组与分类的对应关系表';

-- ----------------------------
-- Records of onethink_auth_extend
-- ----------------------------
INSERT INTO `onethink_auth_extend` VALUES ('1', '1', '2');
INSERT INTO `onethink_auth_extend` VALUES ('1', '2', '2');
INSERT INTO `onethink_auth_extend` VALUES ('1', '3', '2');
INSERT INTO `onethink_auth_extend` VALUES ('1', '80', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '81', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '85', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '87', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '88', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '89', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '90', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '91', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '92', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '93', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '94', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '95', '1');

-- ----------------------------
-- Table structure for onethink_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `onethink_auth_group`;
CREATE TABLE `onethink_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '用户组所属模块',
  `type` tinyint(4) NOT NULL COMMENT '组类型',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_auth_group
-- ----------------------------
INSERT INTO `onethink_auth_group` VALUES ('1', 'admin', '1', '默认用户组', '', '1', '1,3,108,224,256,289,308,322,333,340,341');

-- ----------------------------
-- Table structure for onethink_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `onethink_auth_group_access`;
CREATE TABLE `onethink_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_auth_group_access
-- ----------------------------
INSERT INTO `onethink_auth_group_access` VALUES ('2', '1');
INSERT INTO `onethink_auth_group_access` VALUES ('36', '1');
INSERT INTO `onethink_auth_group_access` VALUES ('38', '1');

-- ----------------------------
-- Table structure for onethink_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `onethink_auth_rule`;
CREATE TABLE `onethink_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-url;2-主菜单',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`status`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=387 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_auth_rule
-- ----------------------------
INSERT INTO `onethink_auth_rule` VALUES ('1', 'admin', '2', 'Admin/Index/index', '首页', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('2', 'admin', '2', 'Admin/Article/mydocument', '内容', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('3', 'admin', '2', 'Admin/User/index', '用户', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('4', 'admin', '2', 'Admin/Addons/index', '扩展', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('5', 'admin', '2', 'Admin/Config/group', '系统', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('7', 'admin', '1', 'Admin/article/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('8', 'admin', '1', 'Admin/article/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('9', 'admin', '1', 'Admin/article/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('10', 'admin', '1', 'Admin/article/update', '保存', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('11', 'admin', '1', 'Admin/article/autoSave', '保存草稿', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('12', 'admin', '1', 'Admin/article/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('13', 'admin', '1', 'Admin/article/copy', '复制', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('14', 'admin', '1', 'Admin/article/paste', '粘贴', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('15', 'admin', '1', 'Admin/article/permit', '还原', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('16', 'admin', '1', 'Admin/article/clear', '清空', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('17', 'admin', '1', 'Admin/article/index', '文档列表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('18', 'admin', '1', 'Admin/article/recycle', '回收站', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('19', 'admin', '1', 'Admin/User/addaction', '新增用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('20', 'admin', '1', 'Admin/User/editaction', '编辑用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('21', 'admin', '1', 'Admin/User/saveAction', '保存用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('22', 'admin', '1', 'Admin/User/setStatus', '变更行为状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('23', 'admin', '1', 'Admin/User/changeStatus?method=forbidUser', '禁用会员', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('24', 'admin', '1', 'Admin/User/changeStatus?method=resumeUser', '启用会员', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('25', 'admin', '1', 'Admin/User/changeStatus?method=deleteUser', '删除会员', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('26', 'admin', '1', 'Admin/User/index', '用户信息', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('27', 'admin', '1', 'Admin/User/action', '用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('28', 'admin', '1', 'Admin/AuthManager/changeStatus?method=deleteGroup', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('29', 'admin', '1', 'Admin/AuthManager/changeStatus?method=forbidGroup', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('30', 'admin', '1', 'Admin/AuthManager/changeStatus?method=resumeGroup', '恢复', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('31', 'admin', '1', 'Admin/AuthManager/createGroup', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('32', 'admin', '1', 'Admin/AuthManager/editGroup', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('33', 'admin', '1', 'Admin/AuthManager/writeGroup', '保存用户组', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('34', 'admin', '1', 'Admin/AuthManager/group', '授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('35', 'admin', '1', 'Admin/AuthManager/access', '访问授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('36', 'admin', '1', 'Admin/AuthManager/user', '成员授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('37', 'admin', '1', 'Admin/AuthManager/removeFromGroup', '解除授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('38', 'admin', '1', 'Admin/AuthManager/addToGroup', '保存成员授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('39', 'admin', '1', 'Admin/AuthManager/category', '分类授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('40', 'admin', '1', 'Admin/AuthManager/addToCategory', '保存分类授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('41', 'admin', '1', 'Admin/AuthManager/index', '权限管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('42', 'admin', '1', 'Admin/Addons/create', '创建', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('43', 'admin', '1', 'Admin/Addons/checkForm', '检测创建', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('44', 'admin', '1', 'Admin/Addons/preview', '预览', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('45', 'admin', '1', 'Admin/Addons/build', '快速生成插件', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('46', 'admin', '1', 'Admin/Addons/config', '设置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('47', 'admin', '1', 'Admin/Addons/disable', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('48', 'admin', '1', 'Admin/Addons/enable', '启用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('49', 'admin', '1', 'Admin/Addons/install', '安装', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('50', 'admin', '1', 'Admin/Addons/uninstall', '卸载', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('51', 'admin', '1', 'Admin/Addons/saveconfig', '更新配置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('52', 'admin', '1', 'Admin/Addons/adminList', '插件后台列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('53', 'admin', '1', 'Admin/Addons/execute', 'URL方式访问插件', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('54', 'admin', '1', 'Admin/Addons/index', '插件管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('55', 'admin', '1', 'Admin/Addons/hooks', '钩子管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('56', 'admin', '1', 'Admin/model/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('57', 'admin', '1', 'Admin/model/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('58', 'admin', '1', 'Admin/model/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('59', 'admin', '1', 'Admin/model/update', '保存数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('60', 'admin', '1', 'Admin/Model/index', '模型管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('61', 'admin', '1', 'Admin/Config/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('62', 'admin', '1', 'Admin/Config/del', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('63', 'admin', '1', 'Admin/Config/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('64', 'admin', '1', 'Admin/Config/save', '保存', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('65', 'admin', '1', 'Admin/Config/group', '网站设置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('66', 'admin', '1', 'Admin/Config/index', '配置管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('67', 'admin', '1', 'Admin/Channel/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('68', 'admin', '1', 'Admin/Channel/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('69', 'admin', '1', 'Admin/Channel/del', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('70', 'admin', '1', 'Admin/Channel/index', '导航管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('71', 'admin', '1', 'Admin/Category/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('72', 'admin', '1', 'Admin/Category/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('73', 'admin', '1', 'Admin/Category/remove', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('74', 'admin', '1', 'Admin/Category/index', '文章分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('75', 'admin', '1', 'Admin/file/upload', '上传控件', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('76', 'admin', '1', 'Admin/file/uploadPicture', '上传图片', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('77', 'admin', '1', 'Admin/file/download', '下载', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('94', 'admin', '1', 'Admin/AuthManager/modelauth', '模型授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('79', 'admin', '1', 'Admin/article/batchOperate', '导入', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('80', 'admin', '1', 'Admin/Database/index?type=export', '备份数据库', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('81', 'admin', '1', 'Admin/Database/index?type=import', '还原数据库', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('82', 'admin', '1', 'Admin/Database/export', '备份', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('83', 'admin', '1', 'Admin/Database/optimize', '优化表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('84', 'admin', '1', 'Admin/Database/repair', '修复表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('86', 'admin', '1', 'Admin/Database/import', '恢复', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('87', 'admin', '1', 'Admin/Database/del', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('88', 'admin', '1', 'Admin/User/add', '新增用户', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('89', 'admin', '1', 'Admin/Attribute/index', '属性管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('90', 'admin', '1', 'Admin/Attribute/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('91', 'admin', '1', 'Admin/Attribute/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('92', 'admin', '1', 'Admin/Attribute/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('93', 'admin', '1', 'Admin/Attribute/update', '保存数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('95', 'admin', '1', 'Admin/AuthManager/addToModel', '保存模型授权', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('96', 'admin', '1', 'Admin/Category/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('97', 'admin', '1', 'Admin/Category/merge', '合并', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('98', 'admin', '1', 'Admin/Config/menu', '后台菜单管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('99', 'admin', '1', 'Admin/Article/mydocument', '内容', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('100', 'admin', '1', 'Admin/Menu/index', '菜单管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('101', 'admin', '1', 'Admin/other', '其他', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('102', 'admin', '1', 'Admin/Menu/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('103', 'admin', '1', 'Admin/Menu/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('104', 'admin', '1', 'Admin/Think/lists?model=article', '文章管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('105', 'admin', '1', 'Admin/Think/lists?model=download', '下载管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('106', 'admin', '1', 'Admin/Think/lists?model=config', '配置管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('107', 'admin', '1', 'Admin/Action/actionlog', '行为日志', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('108', 'admin', '1', 'Admin/User/updatePassword', '修改密码', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('109', 'admin', '1', 'Admin/User/updateNickname', '修改昵称', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('110', 'admin', '1', 'Admin/action/edit', '查看行为日志', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('205', 'admin', '1', 'Admin/think/add', '新增数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('111', 'admin', '2', 'Admin/Article/index', '文章', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('112', 'admin', '2', 'Admin/article/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('113', 'admin', '2', 'Admin/article/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('114', 'admin', '2', 'Admin/article/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('115', 'admin', '2', 'Admin/article/update', '保存', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('116', 'admin', '2', 'Admin/article/autoSave', '保存草稿', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('117', 'admin', '2', 'Admin/article/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('118', 'admin', '2', 'Admin/article/copy', '复制', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('119', 'admin', '2', 'Admin/article/paste', '粘贴', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('120', 'admin', '2', 'Admin/article/batchOperate', '导入', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('121', 'admin', '2', 'Admin/article/recycle', '回收站', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('122', 'admin', '2', 'Admin/article/permit', '还原', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('123', 'admin', '2', 'Admin/article/clear', '清空', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('124', 'admin', '2', 'Admin/User/add', '新增用户', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('125', 'admin', '2', 'Admin/User/action', '用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('126', 'admin', '2', 'Admin/User/addAction', '新增用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('127', 'admin', '2', 'Admin/User/editAction', '编辑用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('128', 'admin', '2', 'Admin/User/saveAction', '保存用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('129', 'admin', '2', 'Admin/User/setStatus', '变更行为状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('130', 'admin', '2', 'Admin/User/changeStatus?method=forbidUser', '禁用会员', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('131', 'admin', '2', 'Admin/User/changeStatus?method=resumeUser', '启用会员', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('132', 'admin', '2', 'Admin/User/changeStatus?method=deleteUser', '删除会员', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('133', 'admin', '2', 'Admin/AuthManager/index', '权限管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('134', 'admin', '2', 'Admin/AuthManager/changeStatus?method=deleteGroup', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('135', 'admin', '2', 'Admin/AuthManager/changeStatus?method=forbidGroup', '禁用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('136', 'admin', '2', 'Admin/AuthManager/changeStatus?method=resumeGroup', '恢复', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('137', 'admin', '2', 'Admin/AuthManager/createGroup', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('138', 'admin', '2', 'Admin/AuthManager/editGroup', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('139', 'admin', '2', 'Admin/AuthManager/writeGroup', '保存用户组', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('140', 'admin', '2', 'Admin/AuthManager/group', '授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('141', 'admin', '2', 'Admin/AuthManager/access', '访问授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('142', 'admin', '2', 'Admin/AuthManager/user', '成员授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('143', 'admin', '2', 'Admin/AuthManager/removeFromGroup', '解除授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('144', 'admin', '2', 'Admin/AuthManager/addToGroup', '保存成员授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('145', 'admin', '2', 'Admin/AuthManager/category', '分类授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('146', 'admin', '2', 'Admin/AuthManager/addToCategory', '保存分类授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('147', 'admin', '2', 'Admin/AuthManager/modelauth', '模型授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('148', 'admin', '2', 'Admin/AuthManager/addToModel', '保存模型授权', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('149', 'admin', '2', 'Admin/Addons/create', '创建', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('150', 'admin', '2', 'Admin/Addons/checkForm', '检测创建', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('151', 'admin', '2', 'Admin/Addons/preview', '预览', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('152', 'admin', '2', 'Admin/Addons/build', '快速生成插件', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('153', 'admin', '2', 'Admin/Addons/config', '设置', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('154', 'admin', '2', 'Admin/Addons/disable', '禁用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('155', 'admin', '2', 'Admin/Addons/enable', '启用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('156', 'admin', '2', 'Admin/Addons/install', '安装', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('157', 'admin', '2', 'Admin/Addons/uninstall', '卸载', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('158', 'admin', '2', 'Admin/Addons/saveconfig', '更新配置', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('159', 'admin', '2', 'Admin/Addons/adminList', '插件后台列表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('160', 'admin', '2', 'Admin/Addons/execute', 'URL方式访问插件', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('161', 'admin', '2', 'Admin/Addons/hooks', '钩子管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('162', 'admin', '2', 'Admin/Model/index', '模型管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('163', 'admin', '2', 'Admin/model/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('164', 'admin', '2', 'Admin/model/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('165', 'admin', '2', 'Admin/model/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('166', 'admin', '2', 'Admin/model/update', '保存数据', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('167', 'admin', '2', 'Admin/Attribute/index', '属性管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('168', 'admin', '2', 'Admin/Attribute/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('169', 'admin', '2', 'Admin/Attribute/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('170', 'admin', '2', 'Admin/Attribute/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('171', 'admin', '2', 'Admin/Attribute/update', '保存数据', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('172', 'admin', '2', 'Admin/Config/index', '配置管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('173', 'admin', '2', 'Admin/Config/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('174', 'admin', '2', 'Admin/Config/del', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('175', 'admin', '2', 'Admin/Config/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('176', 'admin', '2', 'Admin/Config/save', '保存', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('177', 'admin', '2', 'Admin/Menu/index', '菜单管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('178', 'admin', '2', 'Admin/Channel/index', '导航管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('179', 'admin', '2', 'Admin/Channel/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('180', 'admin', '2', 'Admin/Channel/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('181', 'admin', '2', 'Admin/Channel/del', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('182', 'admin', '2', 'Admin/Category/index', '分类管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('183', 'admin', '2', 'Admin/Category/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('184', 'admin', '2', 'Admin/Category/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('185', 'admin', '2', 'Admin/Category/remove', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('186', 'admin', '2', 'Admin/Category/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('187', 'admin', '2', 'Admin/Category/merge', '合并', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('188', 'admin', '2', 'Admin/Database/index?type=export', '备份数据库', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('189', 'admin', '2', 'Admin/Database/export', '备份', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('190', 'admin', '2', 'Admin/Database/optimize', '优化表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('191', 'admin', '2', 'Admin/Database/repair', '修复表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('192', 'admin', '2', 'Admin/Database/index?type=import', '还原数据库', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('193', 'admin', '2', 'Admin/Database/import', '恢复', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('194', 'admin', '2', 'Admin/Database/del', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('195', 'admin', '2', 'Admin/other', '其他', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('196', 'admin', '2', 'Admin/Menu/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('197', 'admin', '2', 'Admin/Menu/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('198', 'admin', '2', 'Admin/Think/lists?model=article', '应用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('199', 'admin', '2', 'Admin/Think/lists?model=download', '下载管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('200', 'admin', '2', 'Admin/Think/lists?model=config', '应用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('201', 'admin', '2', 'Admin/Action/actionlog', '行为日志', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('202', 'admin', '2', 'Admin/User/updatePassword', '修改密码', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('203', 'admin', '2', 'Admin/User/updateNickname', '修改昵称', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('204', 'admin', '2', 'Admin/action/edit', '查看行为日志', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('206', 'admin', '1', 'Admin/think/edit', '编辑数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('207', 'admin', '1', 'Admin/Menu/import', '导入', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('208', 'admin', '1', 'Admin/Model/generate', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('209', 'admin', '1', 'Admin/Addons/addHook', '新增钩子', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('210', 'admin', '1', 'Admin/Addons/edithook', '编辑钩子', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('211', 'admin', '1', 'Admin/Article/sort', '文档排序', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('212', 'admin', '1', 'Admin/Config/sort', '排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('213', 'admin', '1', 'Admin/Menu/sort', '排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('214', 'admin', '1', 'Admin/Channel/sort', '排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('215', 'admin', '1', 'Admin/Category/operate/type/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('216', 'admin', '1', 'Admin/Category/operate/type/merge', '合并', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('217', 'admin', '1', 'Admin/think/lists', '数据列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('218', 'admin', '1', 'Admin/DownCategory/index', '下载分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('219', 'admin', '1', 'Admin/DownCategory/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('220', 'admin', '1', 'Admin/DownCategory/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('221', 'admin', '1', 'Admin/DownCategory/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('222', 'admin', '1', 'Admin/DownCategory/operate/type/move', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('223', 'admin', '1', 'Admin/DownCategory/operate/type/merge', '合并', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('224', 'admin', '2', 'Admin/Down/index', '下载', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('225', 'admin', '1', 'Admin/Tags/index', '标签分类列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('226', 'admin', '1', 'Admin/Down/index', '下载列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('227', 'admin', '2', 'Admin/Tags/index', '标签', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('228', 'admin', '1', 'Admin/http://www.163.com/special/0077450P/login_frame.html', 'xxxxx', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('229', 'admin', '1', 'Admin/ProductTagsCategory/index', '产品标签分类管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('230', 'admin', '1', 'Admin/TagsCategory/index', '标签分类管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('231', 'admin', '1', 'Admin/Template/index', '模板管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('232', 'admin', '1', 'Admin/InternalLink/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('233', 'admin', '1', 'Admin/Template/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('234', 'admin', '1', 'Admin/Template/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('235', 'admin', '1', 'Admin/TagsCategory/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('236', 'admin', '1', 'Admin/TagsCategory/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('237', 'admin', '1', 'Admin/InternalLink/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('238', 'admin', '1', 'Admin/InternalLink/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('239', 'admin', '1', 'Admin/InternalLink/index', '内链管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('240', 'admin', '1', 'Admin/ProductTagsCategory/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('241', 'admin', '1', 'Admin/ProductTagsCategory/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('242', 'admin', '1', 'Admin/ProductTags/index', '产品标签分类列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('243', 'admin', '1', 'Admin/Tags/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('244', 'admin', '1', 'Admin/Tags/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('245', 'admin', '1', 'Admin/ProductTags/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('246', 'admin', '1', 'Admin/ProductTags/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('247', 'admin', '1', 'Admin/PackageCategory/index', '礼包分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('248', 'admin', '1', 'Admin/PackageCategory/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('249', 'admin', '1', 'Admin/PackageCategory/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('250', 'admin', '1', 'Admin/PackageCategory/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('251', 'admin', '2', 'Admin/TagsCategory/index', '标签', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('252', 'admin', '2', 'Admin/ProductTagsCategory/index', '产品标签', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('253', 'admin', '2', 'Admin/Feature/index', '专题', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('254', 'admin', '1', 'Admin/Package/index', '礼包列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('255', 'admin', '1', 'Admin/Package/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('256', 'admin', '2', 'Admin/Package/index', '礼包', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('257', 'admin', '2', 'Admin/Link/index', '友情链接', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('258', 'admin', '1', 'Admin/Document/index', '文档列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('259', 'admin', '1', 'Admin/Document/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('260', 'admin', '1', 'Admin/Document/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('261', 'admin', '1', 'Admin/Document/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('262', 'admin', '1', 'Admin/Document/update', '保存', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('263', 'admin', '1', 'Admin/Document/autoSave', '保存草稿', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('264', 'admin', '1', 'Admin/Document/move', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('265', 'admin', '1', 'Admin/Document/copy', '复制', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('266', 'admin', '1', 'Admin/Document/paste', '粘贴', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('267', 'admin', '1', 'Admin/Document/batchOperate', '导入', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('268', 'admin', '1', 'Admin/Document/recycle', '回收站', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('269', 'admin', '1', 'Admin/Document/permit', '还原', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('270', 'admin', '1', 'Admin/Document/clear', '清空', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('271', 'admin', '1', 'Admin/Document/sort', '文档排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('272', 'admin', '1', 'Admin/PresetSite/index', '预设站点管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('273', 'admin', '1', 'Admin/Company/index', '厂商管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('274', 'admin', '1', 'Admin/Feature/index', '专题列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('275', 'admin', '1', 'Admin/batch/index', '专区列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('276', 'admin', '1', 'Admin/Comment/index', '评论列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('277', 'admin', '1', 'Admin/Feature/edit', '专题编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('278', 'admin', '1', 'Admin/Feature/add', '新增专题', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('279', 'admin', '1', 'Admin/special/index', 'K页面列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('280', 'admin', '1', 'Admin/batch/add', '新增专区', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('281', 'admin', '1', 'Admin/batch/edit', '编辑专区', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('282', 'admin', '1', 'Admin/special/add', '新增K页面', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('283', 'admin', '1', 'Admin/special/edit', '编辑K页面', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('284', 'admin', '1', 'Admin/StaticCreate/index', 'PC版页面管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('285', 'admin', '1', 'Admin/StaticCreate/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('286', 'admin', '1', 'Admin/StaticCreate/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('287', 'admin', '1', 'Admin/StaticCreate/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('288', 'admin', '1', 'Admin/staticCreate/widgetIndex', '单页或特殊页生成', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('289', 'admin', '2', 'Admin/Document/index', '文章', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('290', 'admin', '1', 'Admin/staticCreate/pageIndex', '页面管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('291', 'admin', '1', 'Admin/Link/index', '友情链接', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('292', 'admin', '2', 'Admin/Comment/index', '评论', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('293', 'admin', '2', 'Admin/StaticCreate/index', '生成', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('294', 'admin', '1', 'Admin/StaticCreate/create', 'PC版通用页面生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('295', 'admin', '1', 'Admin/FeatureCategory/index', '分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('296', 'admin', '1', 'Admin/FeatureCategory/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('297', 'admin', '1', 'Admin/FeatureCategory/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('298', 'admin', '1', 'Admin/StaticCreate/widgetCreate', 'PC版单页或特殊页生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('299', 'admin', '2', 'Admin/Company/index', '其他', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('300', 'admin', '2', 'Admin/DownCategory/index', '分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('301', 'admin', '2', 'Admin/StaticCreate/create', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('302', 'admin', '1', 'Admin/Feature/mobile', 'm1', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('303', 'admin', '1', 'Admin/Special/mobile', 'm2', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('304', 'admin', '1', 'Admin/Batch/mobile', 'm3', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('305', 'admin', '1', 'Admin/StaticCreate/index/type/1', '手机版页面管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('306', 'admin', '1', 'Admin/StaticCreate/widgetCreate/type/1', '手机版页面生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('307', 'admin', '1', 'Admin/Document/create', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('308', 'admin', '1', 'Admin/Document/redirectUrl', '查看', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('309', 'admin', '1', 'Admin/Down/recycle', '回收站', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('310', 'admin', '1', 'Admin/Package/permit', '还原', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('311', 'admin', '1', 'Admin/Down/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('312', 'admin', '1', 'Admin/Down/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('313', 'admin', '1', 'Admin/Down/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('314', 'admin', '1', 'Admin/Down/update', '保存', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('315', 'admin', '1', 'Admin/Down/autoSave', '保存草稿', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('316', 'admin', '1', 'Admin/Down/move', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('317', 'admin', '1', 'Admin/Down/copy', '复制', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('318', 'admin', '1', 'Admin/Down/paste', '粘贴', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('319', 'admin', '1', 'Admin/Down/batchOperate', '导入', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('320', 'admin', '1', 'Admin/Down/sort', '文档排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('321', 'admin', '1', 'Admin/Down/create', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('322', 'admin', '1', 'Admin/Down/redirectUrl', '查看', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('323', 'admin', '1', 'Admin/Package/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('324', 'admin', '1', 'Admin/Package/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('325', 'admin', '1', 'Admin/Package/update', '保存', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('326', 'admin', '1', 'Admin/Package/autoSave', '保存草稿', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('327', 'admin', '1', 'Admin/Package/move', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('328', 'admin', '1', 'Admin/Package/copy', '复制', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('329', 'admin', '1', 'Admin/Package/paste', '粘贴', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('330', 'admin', '1', 'Admin/Package/batchOperate', '导入', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('331', 'admin', '1', 'Admin/Package/sort', '文档排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('332', 'admin', '1', 'Admin/Package/create', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('333', 'admin', '1', 'Admin/Package/redirectUrl', '查看', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('334', 'admin', '1', 'Admin/Package/clear', '清空', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('335', 'admin', '1', 'Admin/Down/permit', '还原', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('336', 'admin', '1', 'Admin/Down/clear', '清空', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('337', 'admin', '1', 'Admin/Company/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('338', 'admin', '1', 'Admin/Company/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('339', 'admin', '1', 'Admin/Company/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('340', 'admin', '1', 'Admin/User/updateUsername', '修改姓名', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('341', 'admin', '1', 'Admin/User/submitUsername', '提交姓名', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('342', 'admin', '1', 'Admin/Feature/parse', '专题预览', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('343', 'admin', '1', 'Admin/Feature/flush', '专题生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('344', 'admin', '1', 'Admin/Batch/parse', '专区预览', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('345', 'admin', '1', 'Admin/Batch/flush', '专区生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('346', 'admin', '1', 'Admin/Special/parse', 'K页面预览', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('347', 'admin', '1', 'Admin/Special/flush', 'K页面生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('348', 'admin', '1', 'Admin/Comment/setStatus', '评论审核', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('349', 'admin', '1', 'Admin/Comment/remove', '评论删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('350', 'admin', '1', 'Admin/StaticCreate/home/method/index.html', '生成首页', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('351', 'admin', '1', 'Admin/StaticCreate/widgetSub', '生成特殊页', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('352', 'admin', '1', 'Admin/StaticCreate/home', '生成首页', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('353', 'admin', '1', 'Admin/StaticCreate/moduleLists', '生成列表页', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('354', 'admin', '1', 'Admin/StaticCreate/moduleIndex', '生成礼包首页', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('355', 'admin', '1', 'Admin/StaticCreate/companyDetail.html', '生成厂商内容', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('356', 'admin', '1', 'Admin/StaticCreate/companyDetail', '生成厂商内容', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('357', 'admin', '1', 'Admin//StaticCreate/siteMapNew', '最新数据站点地图', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('358', 'admin', '1', 'Admin/StaticCreate/siteMapNew', '最新数据站点地图', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('359', 'admin', '1', 'Admin/Action/import', '导入日志', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('360', 'admin', '1', 'Admin/Down/address_check', '下载地址检测', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('361', 'admin', '1', 'Admin/StaticCreate/p7230mobile', '手机版通用页面生成', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('362', 'admin', '1', 'Admin/Company/create', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('363', 'admin', '1', 'Admin/Company/view', '查看', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('364', 'admin', '1', 'Admin/ProductTags/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('365', 'admin', '1', 'Admin/Tags/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('366', 'admin', '1', 'Admin/Tags/setStatus', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('367', 'admin', '1', 'Admin/ProductTags/setStatus', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('368', 'admin', '1', 'Admin/TagsMap/index', '标签关联数据列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('369', 'admin', '1', 'Admin/TagsMap/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('370', 'admin', '1', 'Admin/TagsMap/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('371', 'admin', '1', 'Admin/TagsMap/delete', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('372', 'admin', '1', 'Admin/ProductTagsMap/index', '产品标签关联数据列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('373', 'admin', '1', 'Admin/ProductTagsMap/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('374', 'admin', '1', 'Admin/ProductTagsMap/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('375', 'admin', '1', 'Admin/ProductTagsMap/delete', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('376', 'admin', '1', 'Admin/ProductTagsCategory/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('377', 'admin', '1', 'Admin/TagsCategory/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('378', 'admin', '1', 'Admin/TagsCategory/setStatus', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('379', 'admin', '1', 'Admin/ProductTagsCategory/setStatus', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('380', 'admin', '1', 'Admin/Tags/moveTags', '移动到不同分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('381', 'admin', '1', 'Admin/Tags/operate', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('382', 'admin', '1', 'Admin/Tags/pasteTags', '粘贴到不同分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('383', 'admin', '1', 'Admin/ProductTags/operate/type/move/from', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('384', 'admin', '1', 'Admin/ProductTags/moveTags', '移动到不同分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('385', 'admin', '1', 'Admin/ProductTags/pasteTags', '粘贴到不同分类', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('386', 'admin', '1', 'Admin/StaticCreate/7230CreateMobile', '手机版通用页面生成', '1', '');

-- ----------------------------
-- Table structure for onethink_batch
-- ----------------------------
DROP TABLE IF EXISTS `onethink_batch`;
CREATE TABLE `onethink_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父页面ID',
  `interface` tinyint(1) NOT NULL DEFAULT '0' COMMENT '手机或触屏',
  `category_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `title` varchar(120) NOT NULL COMMENT '标题',
  `seo_title` varchar(255) NOT NULL COMMENT 'seo标题',
  `keywords` varchar(255) NOT NULL COMMENT '关键字',
  `description` varchar(255) NOT NULL COMMENT '专题描述',
  `layout` varchar(255) NOT NULL COMMENT '模版地址',
  `widget` text NOT NULL COMMENT '挂件',
  `content` mediumtext NOT NULL COMMENT '内容',
  `url_token` varchar(32) NOT NULL COMMENT '链接地址',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图标',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题计数',
  `label` varchar(255) NOT NULL COMMENT '自定义',
  `sort` int(10) unsigned NOT NULL COMMENT '顺序',
  `abet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赞',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '开启',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`id`),
  KEY `url_token` (`url_token`),
  KEY `title` (`title`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_batch
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_card
-- ----------------------------
DROP TABLE IF EXISTS `onethink_card`;
CREATE TABLE `onethink_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `center_cid` int(10) unsigned NOT NULL COMMENT '礼包中心的卡号表数据ID',
  `center_did` int(10) unsigned NOT NULL COMMENT '礼包中心此卡号所属文章数据ID',
  `did` int(10) unsigned NOT NULL COMMENT '文章ID',
  `number` varchar(255) NOT NULL COMMENT '号码',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `get_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '获取时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
  `draw_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '领取状态（0-待领取，1-已领取）',
  `ip` varchar(30) NOT NULL COMMENT '领取卡号的IP',
  `user_id` int(10) unsigned NOT NULL COMMENT '领取卡号的用户ID',
  `draw_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `draw_status` (`draw_status`),
  KEY `ip` (`ip`),
  KEY `user_id` (`user_id`),
  KEY `did` (`did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='卡号表';

-- ----------------------------
-- Records of onethink_card
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_category`;
CREATE TABLE `onethink_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `rootid` int(10) NOT NULL,
  `depth` int(10) NOT NULL,
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `path_index` varchar(255) NOT NULL DEFAULT '' COMMENT '首页生成路径规则',
  `path_lists` varchar(255) NOT NULL DEFAULT '' COMMENT '列表生成路径规则',
  `path_lists_index` varchar(255) NOT NULL DEFAULT '' COMMENT '列表首页名称（如果填写会同时生成填写名称的第一页）',
  `path_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '详情生成路径规则',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `template_index` varchar(100) NOT NULL COMMENT '频道页模板',
  `template_lists` varchar(100) NOT NULL COMMENT '列表页模板',
  `template_detail` varchar(100) NOT NULL COMMENT '详情页模板',
  `template_edit` varchar(100) NOT NULL COMMENT '编辑页模板',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '列表绑定模型',
  `model_sub` varchar(100) NOT NULL DEFAULT '' COMMENT '子文档绑定模型',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '允许发布的内容类型',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `allow_publish` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许发布内容',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许回复',
  `check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布的文章是否需要审核',
  `reply_model` varchar(100) NOT NULL DEFAULT '',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类图标',
  `old_id` int(11) DEFAULT NULL COMMENT '原有分类ID',
  `describe` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分类表';

-- ----------------------------
-- Records of onethink_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_category_map
-- ----------------------------
DROP TABLE IF EXISTS `onethink_category_map`;
CREATE TABLE `onethink_category_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `did` int(10) unsigned NOT NULL COMMENT '数据ID',
  `rootid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL COMMENT '分类ID',
  `type` varchar(50) NOT NULL DEFAULT ' ' COMMENT '类型，与tags_map表的类型一致',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `did` (`did`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_category_map
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_channel
-- ----------------------------
DROP TABLE IF EXISTS `onethink_channel`;
CREATE TABLE `onethink_channel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '频道ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级频道ID',
  `title` char(30) NOT NULL COMMENT '频道标题',
  `url` char(100) NOT NULL COMMENT '频道连接',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '导航排序',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `target` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '新窗口打开',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_channel
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_comment
-- ----------------------------
DROP TABLE IF EXISTS `onethink_comment`;
CREATE TABLE `onethink_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '模型名，类型名',
  `uid` int(10) NOT NULL,
  `uname` varchar(255) NOT NULL COMMENT '用户名，主要用于游客',
  `document_id` int(10) NOT NULL,
  `message` text NOT NULL COMMENT '评论内容',
  `add_time` int(10) NOT NULL,
  `at_uid` int(10) unsigned NOT NULL COMMENT '@用户',
  `votes` int(10) NOT NULL DEFAULT '0' COMMENT '投票',
  `enabled` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `document_id` (`document_id`),
  KEY `add_time` (`add_time`),
  KEY `votes` (`votes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_comment
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_company
-- ----------------------------
DROP TABLE IF EXISTS `onethink_company`;
CREATE TABLE `onethink_company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `name` varchar(500) NOT NULL COMMENT '公司名',
  `name_e` varchar(500) NOT NULL COMMENT '英文名',
  `path` varchar(500) NOT NULL COMMENT '生成路径',
  `pinyin` varchar(20) NOT NULL COMMENT '拼音首字母',
  `keywords` varchar(500) NOT NULL COMMENT '关键词',
  `title` varchar(500) NOT NULL COMMENT 'titile',
  `description` text NOT NULL COMMENT '厂商详细说明 描述',
  `homepage` varchar(500) NOT NULL COMMENT '主页',
  `img` int(10) unsigned NOT NULL COMMENT '图片ID',
  `position_img` int(10) unsigned NOT NULL COMMENT '推荐图片ID',
  `scontent` text NOT NULL COMMENT '厂商简单说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `intro` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='厂商表';

-- ----------------------------
-- Records of onethink_company
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_config
-- ----------------------------
DROP TABLE IF EXISTS `onethink_config`;
CREATE TABLE `onethink_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置分组',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '配置项',
  `remark` varchar(100) NOT NULL COMMENT '配置说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `value` text NOT NULL COMMENT '配置值',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `type` (`type`),
  KEY `group` (`group`)
) ENGINE=MyISAM AUTO_INCREMENT=123 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_config
-- ----------------------------
INSERT INTO `onethink_config` VALUES ('1', 'WEB_SITE_TITLE', '1', '网站标题', '1', '', '网站标题前台显示标题', '1378898976', '1379235274', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('2', 'WEB_SITE_DESCRIPTION', '2', '网站描述', '1', '', '网站搜索引擎描述', '1378898976', '1379235841', '1', '', '1');
INSERT INTO `onethink_config` VALUES ('3', 'WEB_SITE_KEYWORD', '2', '网站关键字', '1', '', '网站搜索引擎关键字', '1378898976', '1381390100', '1', '', '8');
INSERT INTO `onethink_config` VALUES ('4', 'WEB_SITE_CLOSE', '4', '关闭站点', '1', '0:关闭,1:开启', '站点关闭后其他用户不能访问，管理员可以正常访问', '1378898976', '1379235296', '1', '1', '1');
INSERT INTO `onethink_config` VALUES ('9', 'CONFIG_TYPE_LIST', '3', '配置类型列表', '4', '', '主要用于数据解析和页面表单的生成', '1378898976', '1379235348', '1', '0:数字\r\n1:字符\r\n2:文本\r\n3:数组\r\n4:枚举', '2');
INSERT INTO `onethink_config` VALUES ('10', 'WEB_SITE_ICP', '1', '网站备案号', '1', '', '设置在网站底部显示的备案号，如“沪ICP备12007941号-2', '1378900335', '1379235859', '1', '', '9');
INSERT INTO `onethink_config` VALUES ('11', 'DOCUMENT_POSITION', '3', '文档推荐位', '2', '', '文档推荐位，推荐到多个位置KEY值相加即可', '1379053380', '1421398263', '1', '1:热点文章\r\n2:固顶文章\r\n4:推荐文章\r\n8:专区首页大图推荐\r\n', '3');
INSERT INTO `onethink_config` VALUES ('12', 'DOCUMENT_DISPLAY', '3', '文档可见性', '2', '', '文章可见性仅影响前台显示，后台不收影响', '1379056370', '1379235322', '1', '0:所有人可见\r\n1:仅注册会员可见\r\n2:仅管理员可见', '4');
INSERT INTO `onethink_config` VALUES ('13', 'COLOR_STYLE', '4', '后台色系', '1', 'default_color:默认\r\nblue_color:紫罗兰', '后台颜色风格', '1379122533', '1379235904', '1', 'default_color', '10');
INSERT INTO `onethink_config` VALUES ('20', 'CONFIG_GROUP_LIST', '3', '配置分组', '4', '', '配置分组', '1379228036', '1419210803', '1', '1:基本\r\n5:系统\r\n4:用户\r\n2:文章\r\n3:下载\r\n6:礼包\r\n7:字段属性\r\n8:专题\r\n9:手机版\r\n10:搜索引擎\r\n11:其他', '4');
INSERT INTO `onethink_config` VALUES ('21', 'HOOKS_TYPE', '3', '钩子的类型', '4', '', '类型 1-用于扩展显示内容，2-用于扩展业务处理', '1379313397', '1379313407', '1', '1:视图\r\n2:控制器', '6');
INSERT INTO `onethink_config` VALUES ('22', 'AUTH_CONFIG', '3', 'Auth配置', '4', '', '自定义Auth.class.php类配置', '1379409310', '1379409564', '1', 'AUTH_ON:1\r\nAUTH_TYPE:2', '8');
INSERT INTO `onethink_config` VALUES ('23', 'OPEN_DRAFTBOX', '4', '是否开启草稿功能', '2', '0:关闭草稿功能\r\n1:开启草稿功能\r\n', '新增文章时的草稿功能配置', '1379484332', '1379484591', '1', '1', '1');
INSERT INTO `onethink_config` VALUES ('24', 'DRAFT_AOTOSAVE_INTERVAL', '0', '自动保存草稿时间', '2', '', '自动保存草稿的时间间隔，单位：秒', '1379484574', '1386143323', '1', '60', '2');
INSERT INTO `onethink_config` VALUES ('25', 'LIST_ROWS', '0', '后台每页记录数', '2', '', '后台数据每页显示记录数', '1379503896', '1380427745', '1', '30', '10');
INSERT INTO `onethink_config` VALUES ('26', 'USER_ALLOW_REGISTER', '4', '是否允许用户注册', '3', '0:关闭注册\r\n1:允许注册', '是否开放用户注册', '1379504487', '1379504580', '1', '1', '3');
INSERT INTO `onethink_config` VALUES ('27', 'CODEMIRROR_THEME', '4', '预览插件的CodeMirror主题', '4', '3024-day:3024 day\r\n3024-night:3024 night\r\nambiance:ambiance\r\nbase16-dark:base16 dark\r\nbase16-light:base16 light\r\nblackboard:blackboard\r\ncobalt:cobalt\r\neclipse:eclipse\r\nelegant:elegant\r\nerlang-dark:erlang-dark\r\nlesser-dark:lesser-dark\r\nmidnight:midnight', '详情见CodeMirror官网', '1379814385', '1384740813', '1', 'ambiance', '3');
INSERT INTO `onethink_config` VALUES ('28', 'DATA_BACKUP_PATH', '1', '数据库备份根路径', '4', '', '路径必须以 / 结尾', '1381482411', '1381482411', '1', './Data/', '5');
INSERT INTO `onethink_config` VALUES ('29', 'DATA_BACKUP_PART_SIZE', '0', '数据库备份卷大小', '4', '', '该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M', '1381482488', '1381729564', '1', '20971520', '7');
INSERT INTO `onethink_config` VALUES ('30', 'DATA_BACKUP_COMPRESS', '4', '数据库备份文件是否启用压缩', '4', '0:不压缩\r\n1:启用压缩', '压缩备份文件需要PHP环境支持gzopen,gzwrite函数', '1381713345', '1381729544', '1', '1', '9');
INSERT INTO `onethink_config` VALUES ('31', 'DATA_BACKUP_COMPRESS_LEVEL', '4', '数据库备份文件压缩级别', '4', '1:普通\r\n4:一般\r\n9:最高', '数据库备份文件的压缩级别，该配置在开启压缩时生效', '1381713408', '1381713408', '1', '9', '10');
INSERT INTO `onethink_config` VALUES ('32', 'DEVELOP_MODE', '4', '开启开发者模式', '4', '0:关闭\r\n1:开启', '是否开启开发者模式', '1383105995', '1383291877', '1', '1', '11');
INSERT INTO `onethink_config` VALUES ('33', 'ALLOW_VISIT', '3', '不受限控制器方法', '0', '', '', '1386644047', '1386644741', '1', '0:article/draftbox\r\n1:article/mydocument\r\n2:Category/tree\r\n3:Index/verify\r\n4:file/upload\r\n5:file/download\r\n6:user/updatePassword\r\n7:user/updateNickname\r\n8:user/submitPassword\r\n9:user/submitNickname\r\n10:file/uploadpicture', '0');
INSERT INTO `onethink_config` VALUES ('34', 'DENY_VISIT', '3', '超管专限控制器方法', '0', '', '仅超级管理员可访问的控制器方法', '1386644141', '1386644659', '1', '0:Addons/addhook\r\n1:Addons/edithook\r\n2:Addons/delhook\r\n3:Addons/updateHook\r\n4:Admin/getMenus\r\n5:Admin/recordList\r\n6:AuthManager/updateRules\r\n7:AuthManager/tree', '0');
INSERT INTO `onethink_config` VALUES ('35', 'REPLY_LIST_ROWS', '0', '回复列表每页条数', '2', '', '', '1386645376', '1387178083', '1', '10', '0');
INSERT INTO `onethink_config` VALUES ('36', 'ADMIN_ALLOW_IP', '2', '后台允许访问IP', '4', '', '多个用逗号分隔，如果不配置表示不限制IP访问', '1387165454', '1387165553', '1', '', '12');
INSERT INTO `onethink_config` VALUES ('37', 'SHOW_PAGE_TRACE', '4', '是否显示页面Trace', '4', '0:关闭\r\n1:开启', '是否显示页面Trace信息', '1387165685', '1387165685', '1', '0', '1');
INSERT INTO `onethink_config` VALUES ('38', 'ARTICLE_ADVANCE_AUTHOR', '3', '预定义文章作者', '2', '', '', '1411122440', '1415613702', '1', '0:作者1\r\n1:作者2\r\n', '0');
INSERT INTO `onethink_config` VALUES ('39', 'ARTICLE_ADVANCE_SOURCE', '3', '预定义文章来源', '2', '', '', '1411438967', '1411438986', '1', '0:原创\r\n1:网络\r\n2:手游排行榜', '0');
INSERT INTO `onethink_config` VALUES ('40', 'ARTICLE_ADVANCE_URL', '3', '预定义文章网址', '2', '', '', '1411439406', '1411439406', '1', '0:www.web.com', '0');
INSERT INTO `onethink_config` VALUES ('41', 'TEMPLATE_TYPE', '3', '模板管理类型', '11', '', '', '1411703608', '1423205082', '1', 'InsertArticle:插入文章\r\nInsertDown:插入下载\r\nInternalLink:内链', '0');
INSERT INTO `onethink_config` VALUES ('42', 'TEMPLATE_INTERNAL_LINK', '1', '内链模板名称', '11', '', '', '1411781419', '1423205090', '1', 'InternalLink', '0');
INSERT INTO `onethink_config` VALUES ('43', 'FIELD_PACKAGE_POSITION', '3', '礼包推荐位', '6', '', '', '1413011749', '1417765055', '1', '1:编辑精选\r\n2:推荐大图\r\n4:推荐小图\r\n', '0');
INSERT INTO `onethink_config` VALUES ('44', 'PACKAGE_CARD_TYPE', '3', '卡号类型', '6', '', '', '1413251824', '1414206215', '1', '0:激活码\r\n1:新手卡\r\n2:大礼包', '0');
INSERT INTO `onethink_config` VALUES ('45', 'PACKAGE_API_URL', '1', '礼包中心API数据获取地址', '6', '', '', '1413253769', '1420595646', '1', 'http://package.20hn.cn/api.php', '0');
INSERT INTO `onethink_config` VALUES ('46', 'PACKAGE_CARD_CONDITIONS', '3', '运行环境', '6', '', '', '1413337027', '1414575055', '1', '1:安卓\r\n2:iPhone\r\n4:iPad\r\n8:pc', '0');
INSERT INTO `onethink_config` VALUES ('47', 'PACKAGE_API_KEY', '3', '礼包中心API接口访问KEY', '6', '', '', '1413346229', '1415412549', '1', 'domain:7230\r\nkey:7230jjiuyhg', '0');
INSERT INTO `onethink_config` VALUES ('48', 'FIELD_PACKAGE_GAME_TYPE', '3', '礼包文章模型游戏类型', '6', '', '', '1414055955', '1421033354', '1', '1:回合\r\n2:横版\r\n4:动漫\r\n8:仙侠\r\n16:即时战略', '0');
INSERT INTO `onethink_config` VALUES ('49', 'PACKAGE_DEFAULT_TITLE', '1', '礼包默认首页TITLE', '6', '', '', '1414138163', '1418095011', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('50', 'PACKAGE_DEFAULT_KEY', '1', '礼包默认首页KEY', '6', '', '', '1414138202', '1418095046', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('51', 'PACKAGE_DEFAULT_DESCRIPTION', '1', '礼包默认首页DESCRIPTION', '6', '', '', '1414138259', '1418095038', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('52', 'PACKAGE_PARTICLE_SERVER_TYPE', '3', '礼包文章模型当前状态', '0', '', '', '1414145668', '1421109853', '1', '1:已经开测\r\n2:尚未开测', '0');
INSERT INTO `onethink_config` VALUES ('87', 'PACKAGE_DIR', '1', '礼包静态生成文件夹', '6', '', '如果填写此选项，所有列表页，内容页和首页都会生成在此目录下；基于静态文件生成根目录；请用/结尾', '1417417086', '1417417516', '1', '', '0');
/*INSERT INTO `onethink_config` VALUES ('54', 'STATIC_ROOT', '1', '静态文件生成目录', '5', '', '如果是根目录请./开头，其他目录请自行设置，不需要/结尾', '1414996981', '1417417570', '1', './Static', '0');*/
INSERT INTO `onethink_config` VALUES ('55', 'DYNAMIC_SERVER_URL', '1', '动态服务地址', '5', '', '', '1415000312', '1415412512', '1', 'http://dynamic.WEB.com', '0');
INSERT INTO `onethink_config` VALUES ('57', 'FIELD_DOWN_LANGUAGE', '3', '下载模块-主模型-语言', '3', '', '', '1415084589', '1417765117', '1', '1:简体\r\n2:繁体\r\n3:英文\r\n4:多国语言[中文]\r\n5:日文\r\n6:其它 ', '0');
INSERT INTO `onethink_config` VALUES ('58', 'PACKAGE_SLD', '1', '礼包的二级域名', '6', '', '没有设置二级域名请不要填写；不需要/结尾', '1415188533', '1417417615', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('59', 'PACKAGE_INDEX_PATH', '1', '礼包首页生成路径', '6', '', '礼包模块的首页生成路径；如果填写了模块静态文件夹，只需要填写文件名即可，不需要/开头', '1415412487', '1417417671', '1', 'index', '0');
INSERT INTO `onethink_config` VALUES ('60', 'DOCUMENT_INDEX_PATH', '1', '文章模块首页生成路径', '2', '', '', '1415412928', '1415412928', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('61', 'FIELD_DOWN_SYSTEM', '3', '下载模块-主模型-平台', '3', '', '', '1415582789', '1417765126', '1', '1:Android\r\n2:IOS\r\n3:WP\r\n4:PC\r\n5:TV', '0');
INSERT INTO `onethink_config` VALUES ('62', 'FIELD_DOWN_RANK', '3', '下载模型-主模型-等级', '3', '', '', '1415583328', '1417765134', '1', '1:1星\r\n2:2星\r\n3:3星\r\n4:4星\r\n5:5星', '0');
INSERT INTO `onethink_config` VALUES ('63', 'FIELD_DOWN_DATA_TYPE', '3', '下载模型-主模型-性质', '3', '', '', '1415583591', '1417765151', '1', '1:国产\r\n2:国外\r\n3:汉化\r\n4:破解', '0');
INSERT INTO `onethink_config` VALUES ('64', 'FIELD_DOWN_LICENCE', '3', '下载模型-主模型-授权', '3', '', '', '1415583811', '1417765142', '1', '1:免费下载\r\n2:有内购项\r\n3:测试\r\n4:付费下载\r\n5:限时免费', '0');
INSERT INTO `onethink_config` VALUES ('65', 'FIELD_DOCUMENT_FONT_COLOR', '3', '文章模型-基础模型-标题颜色', '2', '', '', '1415587373', '1417765209', '1', '0:正常\r\n1:红色\r\n2:黄色', '0');
INSERT INTO `onethink_config` VALUES ('66', 'FIELD_DOCUMENT_FONT', '3', '文章模型-基础模型-标题字体', '2', '', '', '1415587631', '1417765217', '1', '0:正常\r\n1:粗体\r\n2:斜体\r\n3:粗体+斜体', '0');
INSERT INTO `onethink_config` VALUES ('67', 'FIELD_PACKAGE_CARD_TYPE', '3', '礼包模型-主模型-卡号类型', '6', '', '', '1415588288', '1423618650', '1', '1:激活码\r\n2:新手卡\r\n3:大礼包', '0');
INSERT INTO `onethink_config` VALUES ('68', 'FILD_PACKAGE_CARD_CONDITIONS', '3', '礼包模型-主模型-运行环境', '0', '', '', '1415590504', '1415592157', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('69', 'FIELD_PACKAGE_STATUS', '0', '礼包模块-礼包基础模型-状态', '0', '', '', '1415607926', '1415607926', '1', '1:可领取\r\n-1:不可领取', '0');
INSERT INTO `onethink_config` VALUES ('70', 'FIELD_PACKAGE_CONDITIONS', '3', '礼包模型-主模型-环境', '6', '', '', '1415609574', '1417765175', '1', '1:安卓\r\n2:iPhone\r\n4:iPad\r\n8:pc', '0');
INSERT INTO `onethink_config` VALUES ('71', 'FIELD_PACKAGE_PLATFORM', '3', '礼包模型-主模型-平台', '6', '', '', '1415610404', '1417765073', '1', '0:测试平台', '0');
INSERT INTO `onethink_config` VALUES ('72', 'FIELD_DOWN_NETWORK', '3', '下载模块-主模型-联网', '3', '', '', '1415678741', '1417765166', '1', '1:单机\r\n2:网游', '0');
INSERT INTO `onethink_config` VALUES ('73', 'FIELD_DOCUMENT_VIDEO', '3', '文章模型-基础模型-有没有视频', '2', '', '', '1415877960', '1417765026', '1', '0:无视频\r\n1:有视频', '0');
INSERT INTO `onethink_config` VALUES ('74', 'FILD_DOWN_PICTURE_SCORE', '3', '礼包模型-主模型-画面分', '0', '', '', '1416295132', '1416295132', '1', '1:一星\r\n2:二星\r\n3:三星\r\n4:四星\r\n5:五星', '0');
INSERT INTO `onethink_config` VALUES ('75', 'FILD_DOWN_MUSIC_SCORE', '3', '下载模型-主模型-音乐分', '0', '', '', '1416295197', '1416295339', '1', '1:一星\r\n2:二星\r\n3:三星\r\n4:四星\r\n5:五星', '0');
INSERT INTO `onethink_config` VALUES ('76', 'FILD_DOWN_FEATURE_SCORE', '3', '下载模型-主模型-特色分', '0', '', '', '1416295247', '1416295349', '1', '1:一星\r\n2:二星\r\n3:三星\r\n4:四星\r\n5:五星', '0');
INSERT INTO `onethink_config` VALUES ('77', 'FILD_DOWN_RUN_SCORE', '3', '下载模型-主模型-运行分', '0', '', '', '1416295297', '1416295297', '1', '1:一星\r\n2:二星\r\n3:三星\r\n4:四星\r\n5:五星', '0');
INSERT INTO `onethink_config` VALUES ('89', 'STATIC_URL', '1', '静态站点URL', '5', '', '', '1417764494', '1417764529', '1', 'http://www.WEB.com', '0');
INSERT INTO `onethink_config` VALUES ('79', 'FIELD_DOWN_POSITION', '3', '下载模型-基础模型-推荐位', '3', '', '', '1416466786', '1417765012', '1', '1:频道首页精品推荐', '0');
INSERT INTO `onethink_config` VALUES ('80', 'THEME', '1', '站点主题名', '5', '', '', '1416484390', '1419491692', '1', 'WEB', '0');
INSERT INTO `onethink_config` VALUES ('81', 'HOME_INDEX_PATH', '1', '全站首页地址', '5', '', '', '1416536726', '1416628890', '1', 'index', '0');
INSERT INTO `onethink_config` VALUES ('82', 'SEO_PRE_SUF', '4', '详情页SEO位置', '5', '0:前缀,1:后缀', '', '1416993418', '1416993495', '1', '1', '0');
INSERT INTO `onethink_config` VALUES ('83', 'SEO_STRING', '1', 'SEO前后缀字符', '5', '', '', '1416993532', '1416993532', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('84', 'DOWN_DEFAULT_TITLE', '2', '下载默认TITLE', '3', '', '', '1416994712', '1418095131', '1', '游戏下载，网页游戏，安卓游戏，手机游戏', '0');
INSERT INTO `onethink_config` VALUES ('85', 'DOWN_DEFAULT_KEY', '2', '下载默认KEY', '3', '', '', '1417056430', '1418095137', '1', '下载，安卓，IOS，单机，网游，手机游戏', '0');
INSERT INTO `onethink_config` VALUES ('86', 'DOWN_DEFAULT_DESCRIPTION', '2', '下载中心DESCRIPTION', '3', '', '', '1417056549', '1418095144', '1', '下载频道可以下载各种安卓，IOS，单机，网络，等手机游戏', '0');
INSERT INTO `onethink_config` VALUES ('88', 'FIELD_HOME_POSITION', '3', '全站推荐位', '7', '', '', '1417596870', '1417765301', '1', '1:首页顶部横图推荐\r\n2:首页顶部竖图推荐', '0');
INSERT INTO `onethink_config` VALUES ('95', 'MOBILE_THEME', '1', '手机版主题', '9', '', '', '1420421807', '1420421807', '1', 'WEBmobile', '1');
/*INSERT INTO `onethink_config` VALUES ('96', 'MOBILE_STATIC_ROOT', '1', '手机版静态生成文件夹', '9', '', '', '1420421902', '1420421902', '1', 'Mobile', '2');*/
INSERT INTO `onethink_config` VALUES ('91', 'FEATURE_ZQ_DIR', '1', '专区生成文件夹', '8', '', '', '1419210916', '1419210916', '1', 'ku', '0');
INSERT INTO `onethink_config` VALUES ('92', 'FEATURE_ZT_DIR', '1', '专题生成文件夹', '8', '', '', '1419210940', '1419210940', '1', 'z', '0');
INSERT INTO `onethink_config` VALUES ('93', 'FEATURE_K_DIR', '1', 'K页面生成文件夹', '8', '', '', '1419210961', '1419210961', '1', 'k', '0');
INSERT INTO `onethink_config` VALUES ('94', 'SITE_NAME', '1', '站点名字', '1', '', '', '1419489870', '1419489870', '1', 'WEB', '0');
INSERT INTO `onethink_config` VALUES ('97', 'MOBILE_DOCUMENT', '3', '文章模块相关', '9', '', '', '1420439835', '1420439849', '1', 'detail:a/{id}\r\n', '4');
INSERT INTO `onethink_config` VALUES ('98', 'MOBILE_STATIC_URL', '1', '手机版URL', '9', '', '', '1420445735', '1420445768', '1', 'http://m.WEB.com', '3');
INSERT INTO `onethink_config` VALUES ('99', 'MOBILE_DOWN', '3', '下载模块相关', '9', '', '', '1420594379', '1420594379', '1', 'detail:d/{id}', '5');
INSERT INTO `onethink_config` VALUES ('100', 'MOBILE_PACKAGE', '3', '礼包模块相关', '9', '', '', '1420594404', '1421917260', '1', 'detail:g/{id}', '6');
INSERT INTO `onethink_config` VALUES ('101', 'FEATURE_PAGE', '0', '分页URL', '8', '', '分页', '1421917260', '1421917260', '1', 'page_{page}.html', '0');
INSERT INTO `onethink_config` VALUES ('104', 'MOBILE_BAIDU_SITEMAP_KEY', '1', '手机版百度站点地图KEY', '10', '', '', '1422866054', '1422930333', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('102', 'STATIC_URL_REWRITE', '3', 'PC版静态地址反向REWRITE规则', '5', '', '', '1422333047', '1422333358', '1', '/\\/post\\/(\\d+?).html/:/g$1\r\n', '0');
INSERT INTO `onethink_config` VALUES ('103', 'MOBILE_STATIC_URL_REWRITE', '3', '手机版静态地址反向REWRITE规则', '9', '', '', '1422333391', '1422333391', '1', '/\\/g\\/(\\d+?).html/:/g$1\r\n/\\/a\\/(\\d+?).html/:/a$1\r\n/\\/d\\/(\\d+?).html/:/d$1', '0');
INSERT INTO `onethink_config` VALUES ('105', 'BAIDU_SITEMAP_KEY', '1', 'PC版百度站点地图KEY', '10', '', '', '1422866132', '1422930349', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('106', 'BAIDU_SITEMAP_POST_SWITCH', '4', '百度站点地图实时推送开关', '10', '0:关闭,1:开启', '', '1422930370', '1422930370', '1', '0', '0');
INSERT INTO `onethink_config` VALUES ('107', 'BAIDU_RDF_POST_SWITCH', '4', '百度结构化数据下载模块实时推送开关', '10', '0:关闭,1:开启', '', '1422930394', '1422930757', '1', '0', '0');
INSERT INTO `onethink_config` VALUES ('108', 'LINK_GROUP', '3', '友情链接分组', '11', '', '', '1423203072', '1423203072', '1', '1:PC版首页\r\n2:PC版内页\r\n3:手机版', '0');
INSERT INTO `onethink_config` VALUES ('109', 'MOBILE_WEB_SITE_TITLE', '1', '手机版TITLE', '9', '', '', '1423623661', '1423623661', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('110', 'MOBILE_WEB_SITE_KEYWORD', '2', '手机版KEYWORD', '9', '', '', '1423623696', '1423623754', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('111', 'MOBILE_WEB_SITE_DESCRIPTION', '2', '手机版DESCRIPTION', '9', '', '', '1423623740', '1423623740', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('112', 'WEB_SERVER', '3', '下载服务器IP地址配置', '0', '', '', '1430379043', '1430393872', '1', 'dx.7230.com:222.189.238.229|220.202.121.199\r\ndx2.7230.com:222.189.238.229\r\nbigdx.7230.com:218.75.155.248\r\ng.7230.com:120.209.136.165', '0');
INSERT INTO `onethink_config` VALUES ('113', 'MOBLIE_BAIDU_NEW_TOKEN', '0', '百度推送TOKEN', '10', '', '', '1432001182', '1432001302', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('114', 'FIELD_DOWN_STATE', '3', '运营状态', '3', '', '', '1433226948', '1433226948', '1', '1:公测\r\n2:封测\r\n3:首测', '0');
INSERT INTO `onethink_config` VALUES ('115', 'FIELD_DOWN_CHANNEL', '3', '梁道', '3', '', '', '1433299596', '1433299737', '1', '0:无\r\n1:官方版\r\n2:九游版\r\n3:360版\r\n4:百度版\r\n5:越狱版\r\n6:腾讯版\r\n7:电脑版', '0');
INSERT INTO `onethink_config` VALUES ('116', 'DOWN_ICON_CORNER', '3', '角标', '3', '', '', '1434075815', '1434076083', '1', '0:无\r\n1:奖品\r\n2:公告\r\n3:现金\r\n4:礼包', '0');
INSERT INTO `onethink_config` VALUES ('117', 'SUB_CATEGORY', '0', '副分类', '5', '', '0代表禁用，1代表启用', '1436866060', '1436866060', '1', '0', '0');
INSERT INTO `onethink_config` VALUES ('118', 'THUMB_START', '4', '水印状态', '5', '0:关闭,1:开启', '标示是否启用图片水印', '1444353207', '1444353532', '1', '1', '7');
INSERT INTO `onethink_config` VALUES ('119', 'THUMB_POSI', '4', '水印位置', '5', '1:左上,2:左中,3:左右,4:中左,5:中心,6:中右,7:下左,8:下中,9:下右', '选择图片水印的位置', '1444353604', '1444353604', '1', '9', '8');
INSERT INTO `onethink_config` VALUES ('120', 'THUMB_IMAGE', '4', '水印图片', '5', '', '水印图片路径', '1444353922', '1444353922', '1', '0', '9');
INSERT INTO `onethink_config` VALUES ('121', 'THUMB_ALPHA', '0', '水印透明度', '5', '', '1-100范围,值越低透明度越高', '1444354109', '1444354109', '1', '60', '10');
INSERT INTO `onethink_config` VALUES ('122', 'CATEGORY_RECOM', '3', '栏目推荐位', '11', '', '', '1444381966', '1444381966', '1', '1:推荐', '0');
-- 标签推荐位 liuliu 2015-12-17
INSERT INTO `onethink_config` VALUES ('123', 'TAGS_POSITION', '7', '标签推荐位', '5', '', '最多支持8个选项', '1419210961', '1419210961', '1', '', '0');
INSERT INTO `onethink_config` VALUES ('124', 'PRODUCT_TAGS_POSITION', '7', '产品标签推荐位', '5', '', '最多支持8个选项', '1419210961', '1419210961', '1', '', '0');
-- 不需要验证码登录IP liuliu 2015-12-21
INSERT INTO `onethink_config` VALUES ('125', 'NO_CAPTCHA_IP
', '1', '不需要验证码登录的IP', '5', '', '最多支持8个选项', '1419210961', '1419210961', '1', '222.244.147.8', '0');

-- ----------------------------
-- Table structure for onethink_digg
-- ----------------------------
DROP TABLE IF EXISTS `onethink_digg`;
CREATE TABLE `onethink_digg` (
  `document_id` int(10) unsigned NOT NULL,
  `good` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赞数',
  `bad` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批数',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `uids` longtext NOT NULL COMMENT '投过票的用户id 字符合集 id1,id2,',
  PRIMARY KEY (`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_digg
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_document
-- ----------------------------
DROP TABLE IF EXISTS `onethink_document`;
CREATE TABLE `onethink_document` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` char(40) NOT NULL DEFAULT '' COMMENT '标识',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '标题',
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类',
  `description` varchar(500) NOT NULL COMMENT '描述',
  `root` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属ID',
  `model_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '内容模型ID',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '内容类型',
  `position` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '推荐位',
  `link` varchar(500) NOT NULL COMMENT '外链',
  `cover_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '可见性',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '截至时间',
  `attach` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `view` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `comment` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `extend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '扩展统计字段',
  `level` int(10) NOT NULL DEFAULT '0' COMMENT '优先级',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '数据状态',
  `title_pinyin` varchar(255) NOT NULL COMMENT '标题首字母',
  `path_detail` varchar(255) NOT NULL COMMENT '静态文件路径',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键词',
  `seo_description` text NOT NULL COMMENT 'SEO描述',
  `video` char(10) NOT NULL COMMENT '是否包含视频',
  `category_rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目根ID',
  `pagination_type` char(10) NOT NULL DEFAULT '0' COMMENT '分页类型',
  `files` varchar(255) NOT NULL COMMENT '附件',
  `ding` int(10) unsigned NOT NULL COMMENT '顶',
  `cai` int(10) unsigned NOT NULL COMMENT '踩',
  `old_id` int(10) unsigned NOT NULL COMMENT '老数据ID',
  `home_position` varchar(100) NOT NULL DEFAULT '0' COMMENT '全站推荐位',
  `vertical_pic` int(10) unsigned NOT NULL COMMENT '首页推荐竖图',
  `smallimg` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图鉴',
  `edit_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑id',
  PRIMARY KEY (`id`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `idx_status_type_pid` (`status`,`uid`,`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文档模型基础表';

-- ----------------------------
-- Records of onethink_document
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_document_article
-- ----------------------------
DROP TABLE IF EXISTS `onethink_document_article`;
CREATE TABLE `onethink_document_article` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档ID',
  `parse` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '内容解析类型',
  `content` text NOT NULL COMMENT '文章内容',
  `template` varchar(100) NOT NULL DEFAULT '' COMMENT '详情页显示模板',
  `bookmark` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  `sub_title` varchar(255) NOT NULL COMMENT '副标题',
  `font` char(10) NOT NULL COMMENT '标题字体',
  `font_color` char(50) NOT NULL COMMENT '标题颜色',
  `author` varchar(255) NOT NULL COMMENT '作者',
  `source` varchar(255) NOT NULL COMMENT '出处',
  `source_url` varchar(255) NOT NULL COMMENT '出处网址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文档模型文章表';

-- ----------------------------
-- Records of onethink_document_article
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_down
-- ----------------------------
DROP TABLE IF EXISTS `onethink_down`;
CREATE TABLE `onethink_down` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` char(40) NOT NULL DEFAULT '' COMMENT '标识',
  `title` char(80) NOT NULL COMMENT '卡号名称',
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类',
  `description` text NOT NULL COMMENT '简介',
  `root` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属ID',
  `model_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '内容模型ID',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '内容类型',
  `position` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '推荐位',
  `link` varchar(500) NOT NULL COMMENT '外链',
  `cover_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面横图',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '可见性',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '截至时间',
  `attach` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `view` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击总数',
  `comment` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `extend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '扩展统计字段',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优先级',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '数据状态',
  `title_pinyin` varchar(255) NOT NULL COMMENT '标题首字母',
  `path_detail` varchar(255) NOT NULL COMMENT '静态文件路径',
  `abet` int(10) unsigned NOT NULL COMMENT '好',
  `argue` int(10) unsigned NOT NULL COMMENT '差',
  `smallimg` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Logo图',
  `previewimg` varchar(255) NOT NULL COMMENT '预览多图',
  `seo_title` varchar(500) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(500) NOT NULL COMMENT 'SEO关键词',
  `seo_description` text NOT NULL COMMENT 'SEO描述',
  `hits_month` int(10) unsigned NOT NULL COMMENT '月点击数',
  `hits_week` int(10) unsigned NOT NULL COMMENT '周点击数',
  `hits_today` int(10) unsigned NOT NULL COMMENT '天点击数',
  `date_month` int(10) unsigned NOT NULL COMMENT '月点击数开始时间',
  `date_week` int(10) unsigned NOT NULL COMMENT '周点击数开始时间',
  `date_today` int(10) unsigned NOT NULL COMMENT '天点击数开始时间',
  `audit` char(10) NOT NULL COMMENT '审核',
  `old_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '老数据ID',
  `category_rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目根ID',
  `home_position` varchar(100) NOT NULL COMMENT '全站推荐位',
  `vertical_pic` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面竖图',
  `edit_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑者id',
  PRIMARY KEY (`id`),
  KEY `idx_status_type_pid` (`status`,`uid`,`pid`),
  KEY `ix_onethink_down_id_view` (`id`,`category_rootid`,`status`,`update_time`,`view`),
  KEY `update_time` (`update_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='下载模型基础表';

-- ----------------------------
-- Records of onethink_down
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_down_address
-- ----------------------------
DROP TABLE IF EXISTS `onethink_down_address`;
CREATE TABLE `onethink_down_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `did` int(10) unsigned NOT NULL COMMENT '下载ID',
  `name` varchar(300) NOT NULL COMMENT '下载名称',
  `url` varchar(700) NOT NULL COMMENT '下载地址',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '预定义站点ID',
  `special` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '链接类型id',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `old_id` int(10) NOT NULL DEFAULT '0' COMMENT '老数据ID',
  `bytes` int(13) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `did` (`did`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='下载地址表';

-- ----------------------------
-- Records of onethink_down_address
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_down_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_down_category`;
CREATE TABLE `onethink_down_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `rootid` int(10) NOT NULL COMMENT '根分类ID',
  `depth` int(10) NOT NULL COMMENT '层级',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `path_index` varchar(255) NOT NULL DEFAULT '' COMMENT '分类首页生成路径规则',
  `path_lists` varchar(255) NOT NULL DEFAULT '' COMMENT '列表生成路径规则',
  `path_lists_index` varchar(255) NOT NULL DEFAULT '' COMMENT '列表首页名称（如果填写会同时生成填写名称的第一页）',
  `path_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '内容生成路径规则',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `template_index` varchar(100) NOT NULL COMMENT '频道页模板',
  `template_lists` varchar(100) NOT NULL COMMENT '列表页模板',
  `template_detail` varchar(100) NOT NULL COMMENT '详情页模板',
  `template_edit` varchar(100) NOT NULL COMMENT '编辑页模板',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '列表绑定模型',
  `model_sub` varchar(100) NOT NULL DEFAULT '' COMMENT '子文档绑定模型',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '允许发布的内容类型',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `allow_publish` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许发布内容',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许回复',
  `check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布的文章是否需要审核',
  `reply_model` varchar(100) NOT NULL DEFAULT '',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类图标',
  `describe` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `rootid` (`rootid`),
  KEY `depth` (`depth`),
  KEY `rootid-depth` (`rootid`,`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='下载分类表';

-- ----------------------------
-- Records of onethink_down_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_down_dmain
-- ----------------------------
DROP TABLE IF EXISTS `onethink_down_dmain`;
CREATE TABLE `onethink_down_dmain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `content` text NOT NULL COMMENT '介绍',
  `version` varchar(100) NOT NULL COMMENT '版本',
  `font` char(50) NOT NULL DEFAULT '0' COMMENT '标题字体',
  `font_color` char(50) NOT NULL DEFAULT '0' COMMENT '标题颜色',
  `size` int(10) unsigned NOT NULL COMMENT '文件大小',
  `sub_title` varchar(255) NOT NULL COMMENT '副标题',
  `conductor` text NOT NULL COMMENT '导读',
  `system` char(50) NOT NULL DEFAULT '1' COMMENT '平台',
  `rank` char(50) NOT NULL DEFAULT '1' COMMENT '等级',
  `data_type` char(50) NOT NULL DEFAULT '1' COMMENT '性质',
  `licence` char(10) NOT NULL DEFAULT '1' COMMENT '授权',
  `language` char(10) NOT NULL DEFAULT '1' COMMENT '语言',
  `author_url` varchar(255) NOT NULL COMMENT '官网',
  `keytext` text NOT NULL COMMENT '特别',
  `system_version` varchar(255) NOT NULL COMMENT '平台版本',
  `network` char(10) NOT NULL DEFAULT '1' COMMENT '联网',
  `company_id` int(10) unsigned NOT NULL COMMENT '厂商',
  `picture_score` char(10) NOT NULL DEFAULT '1' COMMENT '画面分',
  `music_score` char(10) NOT NULL DEFAULT '1' COMMENT '音乐分',
  `feature_score` char(10) NOT NULL DEFAULT '1' COMMENT '特色分',
  `run_score` char(10) NOT NULL DEFAULT '1' COMMENT '运行分',
  `channel` char(10) NOT NULL COMMENT '渠道',
  `package_name` varchar(255) NOT NULL COMMENT '安卓包名',
  `state` int(5) NOT NULL DEFAULT '1' COMMENT '运营状态',
  `iconcorner` int(5) NOT NULL DEFAULT '0' COMMENT '角标',
  `relative_down_url` varchar(255) NOT NULL COMMENT '相关下载地址',
  `optimalemu` int(2) DEFAULT NULL COMMENT 'APP推荐引擎',
  `suitemu` int(2) DEFAULT NULL COMMENT 'APP支持引擎',
  PRIMARY KEY (`id`),
  KEY `ix_onethin_system` (`system`),
  KEY `ix_onethin_network` (`network`),
  KEY `ix_oneth_id_sys` (`id`,`system`,`network`)
) ENGINE=MyISAM AUTO_INCREMENT=188501 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of onethink_down_dmain
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_down_paihang
-- ----------------------------
DROP TABLE IF EXISTS `onethink_down_paihang`;
CREATE TABLE `onethink_down_paihang` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `soft_id` text NOT NULL COMMENT '软件ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=188280 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of onethink_down_paihang
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_feature
-- ----------------------------
DROP TABLE IF EXISTS `onethink_feature`;
CREATE TABLE `onethink_feature` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父页面ID',
  `interface` tinyint(1) NOT NULL DEFAULT '0' COMMENT '手机或触屏',
  `category_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `title` varchar(120) NOT NULL COMMENT '专题标题',
  `seo_title` varchar(255) NOT NULL COMMENT 'seo标题',
  `keywords` varchar(255) NOT NULL COMMENT '关键字',
  `description` varchar(255) NOT NULL COMMENT '专题描述',
  `layout` varchar(255) NOT NULL COMMENT '模版地址',
  `widget` text NOT NULL COMMENT '挂件',
  `content` mediumtext NOT NULL COMMENT '专题描述',
  `url_token` varchar(32) NOT NULL COMMENT '链接地址',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '专题图标',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题计数',
  `label` varchar(255) NOT NULL COMMENT '自定义',
  `sort` int(10) unsigned NOT NULL COMMENT '顺序',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '开启',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`id`),
  KEY `url_token` (`url_token`),
  KEY `title` (`title`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_feature
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_feature_batch
-- ----------------------------
DROP TABLE IF EXISTS `onethink_feature_batch`;
CREATE TABLE `onethink_feature_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品标签',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of onethink_feature_batch
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_feature_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_feature_category`;
CREATE TABLE `onethink_feature_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `is_parent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否枝干',
  `path` varchar(60) NOT NULL COMMENT '生成路径',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `extend` text COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图标',
  `abet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赞',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_feature_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_file
-- ----------------------------
DROP TABLE IF EXISTS `onethink_file`;
CREATE TABLE `onethink_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '原始文件名',
  `savename` char(20) NOT NULL DEFAULT '' COMMENT '保存名称',
  `savepath` char(30) NOT NULL DEFAULT '' COMMENT '文件保存路径',
  `ext` char(5) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `mime` char(40) NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `location` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '文件保存位置',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '远程地址',
  `create_time` int(10) unsigned NOT NULL COMMENT '上传时间',
  `old` int(10) NOT NULL DEFAULT '0' COMMENT '是否老数据',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_md5` (`md5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文件表';

-- ----------------------------
-- Records of onethink_file
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_hooks
-- ----------------------------
DROP TABLE IF EXISTS `onethink_hooks`;
CREATE TABLE `onethink_hooks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `description` text NOT NULL COMMENT '描述',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `addons` varchar(255) NOT NULL DEFAULT '' COMMENT '钩子挂载的插件 ''，''分割',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_hooks
-- ----------------------------
INSERT INTO `onethink_hooks` VALUES ('1', 'pageHeader', '页面header钩子，一般用于加载插件CSS文件和代码', '1', '0', '', '1');
INSERT INTO `onethink_hooks` VALUES ('2', 'pageFooter', '页面footer钩子，一般用于加载插件JS文件和JS代码', '1', '0', 'ReturnTop', '1');
INSERT INTO `onethink_hooks` VALUES ('3', 'documentEditForm', '添加编辑表单的 扩展内容钩子', '1', '0', 'Attachment', '1');
INSERT INTO `onethink_hooks` VALUES ('4', 'documentDetailAfter', '文档末尾显示', '1', '0', 'Attachment,SocialComment,Digg', '1');
INSERT INTO `onethink_hooks` VALUES ('5', 'documentDetailBefore', '页面内容前显示用钩子', '1', '0', '', '1');
INSERT INTO `onethink_hooks` VALUES ('6', 'documentSaveComplete', '保存文档数据后的扩展钩子', '2', '0', 'Attachment', '1');
INSERT INTO `onethink_hooks` VALUES ('7', 'documentEditFormContent', '添加编辑表单的内容显示钩子', '1', '0', 'Editor', '1');
INSERT INTO `onethink_hooks` VALUES ('8', 'adminArticleEdit', '后台内容编辑页编辑器', '1', '1378982734', 'EditorForAdmin', '1');
INSERT INTO `onethink_hooks` VALUES ('13', 'AdminIndex', '首页小格子个性化显示', '1', '1382596073', 'SystemInfo,SiteStat,QiuBai', '1');
INSERT INTO `onethink_hooks` VALUES ('14', 'topicComment', '评论提交方式扩展钩子。', '1', '1380163518', 'Editor', '1');
INSERT INTO `onethink_hooks` VALUES ('16', 'app_begin', '应用开始', '2', '1384481614', '', '1');

-- ----------------------------
-- Table structure for onethink_internal_link
-- ----------------------------
DROP TABLE IF EXISTS `onethink_internal_link`;
CREATE TABLE `onethink_internal_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `template_id` int(10) unsigned NOT NULL COMMENT '模板ID',
  `description` text NOT NULL COMMENT '描述',
  `icon` int(10) unsigned NOT NULL COMMENT '图片ID，用于预览',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
  `old_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '老版本ID',
  `level` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内链表';

-- ----------------------------
-- Records of onethink_internal_link
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_link
-- ----------------------------
DROP TABLE IF EXISTS `onethink_link`;
CREATE TABLE `onethink_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL COMMENT '标题',
  `url_token` varchar(32) NOT NULL COMMENT '链接地址',
  `icon` varchar(255) NOT NULL COMMENT '链接图标',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计数',
  `sort` int(10) unsigned NOT NULL COMMENT '顺序',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `group` char(50) NOT NULL DEFAULT '1' COMMENT '分组',
  `status` char(10) NOT NULL DEFAULT '1' COMMENT '数据状态',
  PRIMARY KEY (`id`),
  KEY `url_token` (`url_token`),
  KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_link
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_link_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_link_category`;
CREATE TABLE `onethink_link_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `template_index` varchar(100) NOT NULL COMMENT '频道页模板',
  `template_lists` varchar(100) NOT NULL COMMENT '列表页模板',
  `template_detail` varchar(100) NOT NULL COMMENT '详情页模板',
  `template_edit` varchar(100) NOT NULL COMMENT '编辑页模板',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '列表绑定模型',
  `model_sub` varchar(100) NOT NULL DEFAULT '' COMMENT '子文档绑定模型',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '允许发布的内容类型',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `allow_publish` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许发布内容',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许回复',
  `check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布的文章是否需要审核',
  `reply_model` varchar(100) NOT NULL DEFAULT '',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类图标',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  FULLTEXT KEY `uk_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_link_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_member
-- ----------------------------
DROP TABLE IF EXISTS `onethink_member`;
CREATE TABLE `onethink_member` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `nickname` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `username` varchar(60) NOT NULL COMMENT '姓名',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date NOT NULL DEFAULT '0000-00-00' COMMENT '生日',
  `qq` char(10) NOT NULL DEFAULT '' COMMENT 'qq号',
  `score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户积分',
  `login` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '会员状态',
  PRIMARY KEY (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员表';


-- ----------------------------
-- Table structure for onethink_menu
-- ----------------------------
DROP TABLE IF EXISTS `onethink_menu`;
CREATE TABLE `onethink_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=278 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_menu
-- ----------------------------
INSERT INTO `onethink_menu` VALUES ('1', '首页', '0', '1', 'Index/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('2', '文章', '0', '2', 'Document/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('3', '文档列表', '2', '0', 'Document/index', '1', '', '内容', '0', '1');
INSERT INTO `onethink_menu` VALUES ('4', '新增', '3', '0', 'Document/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('5', '编辑', '3', '0', 'Document/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('6', '改变状态', '3', '0', 'Document/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('7', '保存', '3', '0', 'Document/update', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('8', '保存草稿', '3', '0', 'Document/autoSave', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('9', '移动', '3', '0', 'Document/move', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('10', '复制', '3', '0', 'Document/copy', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('11', '粘贴', '3', '0', 'Document/paste', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('12', '导入', '3', '0', 'Document/batchOperate', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('13', '回收站', '2', '0', 'Document/recycle', '1', '', '内容', '0', '1');
INSERT INTO `onethink_menu` VALUES ('14', '还原', '13', '0', 'Document/permit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('15', '清空', '13', '0', 'Document/clear', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('16', '用户', '0', '98', 'User/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('17', '用户信息', '16', '0', 'User/index', '0', '', '用户管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('18', '新增用户', '17', '0', 'User/add', '0', '添加新用户', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('19', '用户行为', '16', '0', 'User/action', '0', '', '行为管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('20', '新增用户行为', '19', '0', 'User/addaction', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('21', '编辑用户行为', '19', '0', 'User/editaction', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('22', '保存用户行为', '19', '0', 'User/saveAction', '0', '\"用户->用户行为\"保存编辑和新增的用户行为', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('23', '变更行为状态', '19', '0', 'User/setStatus', '0', '\"用户->用户行为\"中的启用,禁用和删除权限', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('24', '禁用会员', '19', '0', 'User/changeStatus?method=forbidUser', '0', '\"用户->用户信息\"中的禁用', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('25', '启用会员', '19', '0', 'User/changeStatus?method=resumeUser', '0', '\"用户->用户信息\"中的启用', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('26', '删除会员', '19', '0', 'User/changeStatus?method=deleteUser', '0', '\"用户->用户信息\"中的删除', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('27', '权限管理', '16', '0', 'AuthManager/index', '0', '', '用户管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('28', '删除', '27', '0', 'AuthManager/changeStatus?method=deleteGroup', '0', '删除用户组', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('29', '禁用', '27', '0', 'AuthManager/changeStatus?method=forbidGroup', '0', '禁用用户组', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('30', '恢复', '27', '0', 'AuthManager/changeStatus?method=resumeGroup', '0', '恢复已禁用的用户组', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('31', '新增', '27', '0', 'AuthManager/createGroup', '0', '创建新的用户组', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('32', '编辑', '27', '0', 'AuthManager/editGroup', '0', '编辑用户组名称和描述', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('33', '保存用户组', '27', '0', 'AuthManager/writeGroup', '0', '新增和编辑用户组的\"保存\"按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('34', '授权', '27', '0', 'AuthManager/group', '0', '\"后台 \\ 用户 \\ 用户信息\"列表页的\"授权\"操作按钮,用于设置用户所属用户组', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('35', '访问授权', '27', '0', 'AuthManager/access', '0', '\"后台 \\ 用户 \\ 权限管理\"列表页的\"访问授权\"操作按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('36', '成员授权', '27', '0', 'AuthManager/user', '0', '\"后台 \\ 用户 \\ 权限管理\"列表页的\"成员授权\"操作按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('37', '解除授权', '27', '0', 'AuthManager/removeFromGroup', '0', '\"成员授权\"列表页内的解除授权操作按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('38', '保存成员授权', '27', '0', 'AuthManager/addToGroup', '0', '\"用户信息\"列表页\"授权\"时的\"保存\"按钮和\"成员授权\"里右上角的\"添加\"按钮)', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('39', '分类授权', '27', '0', 'AuthManager/category', '0', '\"后台 \\ 用户 \\ 权限管理\"列表页的\"分类授权\"操作按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('40', '保存分类授权', '27', '0', 'AuthManager/addToCategory', '0', '\"分类授权\"页面的\"保存\"按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('41', '模型授权', '27', '0', 'AuthManager/modelauth', '0', '\"后台 \\ 用户 \\ 权限管理\"列表页的\"模型授权\"操作按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('42', '保存模型授权', '27', '0', 'AuthManager/addToModel', '0', '\"分类授权\"页面的\"保存\"按钮', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('43', '扩展', '0', '999', 'Addons/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('44', '插件管理', '43', '1', 'Addons/index', '0', '', '扩展', '0', '1');
INSERT INTO `onethink_menu` VALUES ('45', '创建', '44', '0', 'Addons/create', '0', '服务器上创建插件结构向导', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('46', '检测创建', '44', '0', 'Addons/checkForm', '0', '检测插件是否可以创建', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('47', '预览', '44', '0', 'Addons/preview', '0', '预览插件定义类文件', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('48', '快速生成插件', '44', '0', 'Addons/build', '0', '开始生成插件结构', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('49', '设置', '44', '0', 'Addons/config', '0', '设置插件配置', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('50', '禁用', '44', '0', 'Addons/disable', '0', '禁用插件', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('51', '启用', '44', '0', 'Addons/enable', '0', '启用插件', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('52', '安装', '44', '0', 'Addons/install', '0', '安装插件', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('53', '卸载', '44', '0', 'Addons/uninstall', '0', '卸载插件', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('54', '更新配置', '44', '0', 'Addons/saveconfig', '0', '更新插件配置处理', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('55', '插件后台列表', '44', '0', 'Addons/adminList', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('56', 'URL方式访问插件', '44', '0', 'Addons/execute', '0', '控制是否有权限通过url访问插件控制器方法', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('57', '钩子管理', '43', '2', 'Addons/hooks', '0', '', '扩展', '0', '1');
INSERT INTO `onethink_menu` VALUES ('58', '模型管理', '68', '3', 'Model/index', '0', '', '系统设置', '0', '1');
INSERT INTO `onethink_menu` VALUES ('59', '新增', '58', '0', 'model/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('60', '编辑', '58', '0', 'model/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('61', '改变状态', '58', '0', 'model/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('62', '保存数据', '58', '0', 'model/update', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('63', '属性管理', '68', '0', 'Attribute/index', '1', '网站属性配置。', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('64', '新增', '63', '0', 'Attribute/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('65', '编辑', '63', '0', 'Attribute/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('66', '改变状态', '63', '0', 'Attribute/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('67', '保存数据', '63', '0', 'Attribute/update', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('68', '系统', '0', '99', 'Config/group', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('69', '网站设置', '68', '1', 'Config/group', '0', '', '系统设置', '0', '1');
INSERT INTO `onethink_menu` VALUES ('70', '配置管理', '68', '4', 'Config/index', '0', '', '系统设置', '0', '1');
INSERT INTO `onethink_menu` VALUES ('71', '编辑', '70', '0', 'Config/edit', '0', '新增编辑和保存配置', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('72', '删除', '70', '0', 'Config/del', '0', '删除配置', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('73', '新增', '70', '0', 'Config/add', '0', '新增配置', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('74', '保存', '70', '0', 'Config/save', '0', '保存配置', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('75', '菜单管理', '68', '5', 'Menu/index', '0', '', '系统设置', '0', '1');
INSERT INTO `onethink_menu` VALUES ('76', '导航管理', '68', '6', 'Channel/index', '0', '', '系统设置', '0', '1');
INSERT INTO `onethink_menu` VALUES ('77', '新增', '76', '0', 'Channel/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('78', '编辑', '76', '0', 'Channel/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('79', '删除', '76', '0', 'Channel/del', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('80', '文章分类', '191', '2', 'Category/index', '0', '', '分类管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('124', '编辑', '123', '0', 'DownCategory/edit', '0', '编辑和保存栏目分类', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('125', '新增', '123', '0', 'DownCategory/add', '0', '新增栏目分类', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('126', '删除', '123', '0', 'DownCategory/remove', '0', '删除栏目分类', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('127', '移动', '123', '0', 'DownCategory/operate/type/move', '0', '移动栏目分类', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('128', '合并', '123', '0', 'DownCategory/operate/type/merge', '0', '合并栏目分类', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('86', '备份数据库', '68', '0', 'Database/index?type=export', '0', '', '数据备份', '0', '1');
INSERT INTO `onethink_menu` VALUES ('87', '备份', '86', '0', 'Database/export', '0', '备份数据库', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('88', '优化表', '86', '0', 'Database/optimize', '0', '优化数据表', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('89', '修复表', '86', '0', 'Database/repair', '0', '修复数据表', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('90', '还原数据库', '68', '0', 'Database/index?type=import', '0', '', '数据备份', '0', '1');
INSERT INTO `onethink_menu` VALUES ('91', '恢复', '90', '0', 'Database/import', '0', '数据库恢复', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('92', '删除', '90', '0', 'Database/del', '0', '删除备份文件', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('150', '产品标签分类管理', '149', '0', 'ProductTagsCategory/index', '0', '', '分类管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('96', '新增', '75', '0', 'Menu/add', '0', '', '系统设置', '0', '1');
INSERT INTO `onethink_menu` VALUES ('98', '编辑', '75', '0', 'Menu/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('106', '行为日志', '16', '0', 'Action/actionlog', '0', '', '行为管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('108', '修改密码', '16', '0', 'User/updatePassword', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('109', '修改昵称', '16', '0', 'User/updateNickname', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('110', '查看行为日志', '106', '0', 'action/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('112', '新增数据', '58', '0', 'think/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('113', '编辑数据', '58', '0', 'think/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('114', '导入', '75', '0', 'Menu/import', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('115', '生成', '58', '0', 'Model/generate', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('116', '新增钩子', '57', '0', 'Addons/addHook', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('117', '编辑钩子', '57', '0', 'Addons/edithook', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('118', '文档排序', '3', '0', 'Document/sort', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('119', '排序', '70', '0', 'Config/sort', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('120', '排序', '75', '0', 'Menu/sort', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('121', '排序', '76', '0', 'Channel/sort', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('122', '数据列表', '58', '0', 'think/lists', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('123', '下载分类', '191', '3', 'DownCategory/index', '0', '', '分类管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('129', '下载', '0', '2', 'Down/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('130', '标签', '0', '5', 'TagsCategory/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('131', '标签分类管理', '130', '0', 'TagsCategory/index', '0', '', '分类管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('136', '专题', '0', '4', 'Feature/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('167', '生成', '0', '97', 'StaticCreate/create', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('135', '标签分类列表', '130', '0', 'Tags/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('137', '模板管理', '190', '0', 'Template/index', '0', '', '模板管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('146', '新增', '144', '0', 'InternalLink/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('138', '新增', '137', '0', 'Template/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('139', '编辑', '137', '0', 'Template/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('140', '新增', '131', '0', 'TagsCategory/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('141', '编辑', '131', '0', 'TagsCategory/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('147', '编辑', '144', '0', 'InternalLink/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('148', '删除', '144', '0', 'InternalLink/remove', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('144', '内链管理', '190', '0', 'InternalLink/index', '0', '', '内链管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('149', '产品标签', '0', '5', 'ProductTagsCategory/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('151', '新增', '150', '0', 'ProductTagsCategory/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('152', '编辑', '150', '0', 'ProductTagsCategory/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('153', '产品标签分类列表', '149', '0', 'ProductTags/index', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('154', '新增', '135', '0', 'Tags/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('155', '编辑', '135', '0', 'Tags/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('156', '新增', '153', '0', 'ProductTags/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('157', '编辑', '153', '0', 'ProductTags/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('159', '礼包分类', '191', '0', 'PackageCategory/index', '0', '', '分类管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('160', '编辑', '159', '0', 'PackageCategory/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('161', '新增', '159', '0', 'PackageCategory/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('162', '删除', '159', '0', 'PackageCategory/remove', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('163', '礼包', '0', '3', 'Package/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('164', '礼包列表', '163', '0', 'Package/index', '1', '', '内容', '0', '1');
INSERT INTO `onethink_menu` VALUES ('226', '还原', '201', '0', 'Package/permit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('166', '友情链接', '190', '9', 'Link/index', '0', '', '友情链接', '0', '1');
INSERT INTO `onethink_menu` VALUES ('168', '预设站点管理', '190', '0', 'PresetSite/index', '0', '', '预设站点管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('169', '评论', '0', '10', 'Comment/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('170', '厂商管理', '190', '0', 'Company/index', '0', '', '厂商管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('171', '专题列表', '136', '0', 'Feature/index', '0', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('172', '专区列表', '136', '0', 'batch/index', '0', '', '专区列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('173', '评论列表', '169', '0', 'Comment/index', '0', '', '评论列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('174', '专题编辑', '171', '0', 'Feature/edit', '1', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('175', '新增专题', '171', '0', 'Feature/add', '0', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('176', 'K页面列表', '136', '0', 'special/index', '0', '', 'K页面', '0', '1');
INSERT INTO `onethink_menu` VALUES ('177', '新增专区', '172', '0', 'batch/add', '1', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('178', '编辑专区', '172', '0', 'batch/edit', '0', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('179', '新增K页面', '176', '0', 'special/add', '0', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('180', '编辑K页面', '176', '0', 'special/edit', '0', '', '专题列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('181', 'PC版通用页面生成', '167', '1', 'StaticCreate/create', '0', '', '生成', '0', '1');
INSERT INTO `onethink_menu` VALUES ('182', 'PC版页面管理', '167', '3', 'StaticCreate/index', '0', '', '管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('183', 'PC版单页或特殊页生成', '167', '3', 'StaticCreate/widgetCreate', '0', '', '生成', '0', '1');
INSERT INTO `onethink_menu` VALUES ('184', '编辑', '182', '0', 'StaticCreate/edit', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('185', '新增', '182', '0', 'StaticCreate/add', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('186', '删除', '182', '0', 'StaticCreate/remove', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('187', '分类', '136', '0', 'FeatureCategory/index', '0', '', '分类列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('188', '编辑', '187', '0', 'FeatureCategory/edit', '0', '', '分类列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('189', '新增', '187', '0', 'FeatureCategory/add', '0', '', '分类列表', '0', '1');
INSERT INTO `onethink_menu` VALUES ('190', '其他', '0', '6', 'Company/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('191', '分类', '0', '6', 'DownCategory/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('192', '手机版页面管理', '167', '4', 'StaticCreate/index/type/1', '0', '', '管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('193', '手机版页面生成', '167', '5', 'StaticCreate/widgetCreate/type/1', '0', '', '生成', '0', '1');
INSERT INTO `onethink_menu` VALUES ('194', 'm1', '136', '0', 'Feature/mobile', '1', '', '专题', '0', '1');
INSERT INTO `onethink_menu` VALUES ('195', 'm2', '136', '0', 'Special/mobile', '1', '', '专题', '0', '1');
INSERT INTO `onethink_menu` VALUES ('196', 'm3', '136', '0', 'Batch/mobile', '1', '', '专题', '0', '1');
INSERT INTO `onethink_menu` VALUES ('197', '生成', '3', '0', 'Document/create', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('198', '查看', '3', '0', 'Document/redirectUrl', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('199', '下载列表', '129', '0', 'Down/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('200', '回收站', '129', '0', 'Down/recycle', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('201', '回收站', '163', '0', 'Document/recycle', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('202', '新增', '199', '0', 'Down/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('203', '编辑', '199', '0', 'Down/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('204', '改变状态', '199', '0', 'Down/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('205', '保存', '199', '0', 'Down/update', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('206', '保存草稿', '199', '0', 'Down/autoSave', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('207', '移动', '199', '0', 'Down/move', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('208', '复制', '199', '0', 'Down/copy', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('209', '粘贴', '199', '0', 'Down/paste', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('210', '导入', '199', '0', 'Down/batchOperate', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('211', '文档排序', '199', '0', 'Down/sort', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('212', '生成', '199', '0', 'Down/create', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('213', '查看', '199', '0', 'Down/redirectUrl', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('214', '新增', '164', '0', 'Package/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('215', '编辑', '164', '0', 'Package/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('216', '改变状态', '164', '0', 'Package/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('217', '保存', '164', '0', 'Package/update', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('218', '保存草稿', '164', '0', 'Package/autoSave', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('219', '移动', '164', '0', 'Package/move', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('220', '复制', '164', '0', 'Package/copy', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('221', '粘贴', '164', '0', 'Package/paste', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('222', '导入', '164', '0', 'Package/batchOperate', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('223', '文档排序', '164', '0', 'Package/sort', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('224', '生成', '164', '0', 'Package/create', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('225', '查看', '164', '0', 'Package/redirectUrl', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('227', '清空', '201', '0', 'Package/clear', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('228', '还原', '200', '0', 'Down/permit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('229', '清空', '200', '0', 'Down/clear', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('230', '新增', '170', '0', 'Company/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('231', '删除', '170', '0', 'Company/remove', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('232', '编辑', '170', '0', 'Company/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('233', '修改姓名', '16', '0', 'User/updateUsername', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('234', '提交姓名', '16', '0', 'User/submitUsername', '1', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('235', '专题预览', '171', '0', 'Feature/parse', '0', '', '专题', '0', '1');
INSERT INTO `onethink_menu` VALUES ('236', '专题生成', '171', '0', 'Feature/flush', '0', '', '专题', '0', '1');
INSERT INTO `onethink_menu` VALUES ('237', '专区预览', '172', '0', 'Batch/parse', '0', '', '专区', '0', '1');
INSERT INTO `onethink_menu` VALUES ('238', '专区生成', '172', '0', 'Batch/flush', '0', '', '专区', '0', '1');
INSERT INTO `onethink_menu` VALUES ('239', 'K页面预览', '176', '0', 'Special/parse', '0', '', 'K页面', '0', '1');
INSERT INTO `onethink_menu` VALUES ('240', 'K页面生成', '176', '0', 'Special/flush', '0', '', 'K页面', '0', '1');
INSERT INTO `onethink_menu` VALUES ('241', '评论审核', '173', '0', 'Comment/setStatus', '0', '', '评论', '0', '1');
INSERT INTO `onethink_menu` VALUES ('242', '评论删除', '173', '0', 'Comment/remove', '0', '', '评论', '0', '1');
INSERT INTO `onethink_menu` VALUES ('243', '生成首页', '181', '0', 'StaticCreate/home', '0', '', '生成PC版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('244', '生成M首页', '193', '0', 'StaticCreate/widgetSub', '0', '', '生成M版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('245', '生成特殊页', '183', '0', 'StaticCreate/widgetSub', '0', '', '生成PC版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('246', '生成列表页', '181', '0', 'StaticCreate/moduleLists', '0', '', '生成PC版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('247', '生成礼包首页', '181', '0', 'StaticCreate/moduleIndex', '0', '', '生成PC版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('248', '生成厂商内容', '181', '0', 'StaticCreate/companyDetail', '0', '', '生成PC版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('249', '最新数据站点地图', '181', '0', 'StaticCreate/siteMapNew', '0', '', '生成PC版', '0', '1');
INSERT INTO `onethink_menu` VALUES ('250', '导入日志', '16', '0', 'Action/import', '0', '', '行为管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('251', '下载地址检测', '190', '0', 'Down/address_check', '0', '', '下载管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('252', '手机版通用页面生成', '167', '2', 'StaticCreate/7230CreateMobile', '0', '', '生成', '0', '1');
INSERT INTO `onethink_menu` VALUES ('253', '生成', '170', '0', 'Company/create', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('254', '查看', '170', '0', 'Company/view', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('256', '删除', '150', '0', 'ProductTagsCategory/remove', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('257', '删除', '131', '0', 'TagsCategory/remove', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('258', '禁用', '131', '0', 'TagsCategory/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('259', '禁用', '150', '0', 'ProductTagsCategory/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('260', '标签关联数据列表', '130', '0', 'TagsMap/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('261', '新增', '260', '0', 'TagsMap/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('262', '编辑', '260', '0', 'TagsMap/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('263', '删除', '260', '0', 'TagsMap/delete', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('264', '产品标签关联数据列表', '149', '0', 'ProductTagsMap/index', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('265', '新增', '264', '0', 'ProductTagsMap/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('266', '编辑', '264', '0', 'ProductTagsMap/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('267', '删除', '264', '0', 'ProductTagsMap/delete', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('268', '移动到不同分类', '135', '0', 'Tags/moveTags', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('269', '删除', '135', '0', 'Tags/remove', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('270', '禁用', '135', '0', 'Tags/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('271', '移动', '135', '0', 'Tags/operate/type/move', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('272', '粘贴到不同分类', '135', '0', 'Tags/pasteTags', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('273', '删除', '153', '0', 'ProductTags/remove', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('274', '禁用', '153', '0', 'ProductTags/setStatus', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('275', '移动', '153', '0', 'ProductTags/operate/type/move/from', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('276', '移动到不同分类', '153', '0', 'ProductTags/moveTags', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('277', '粘贴到不同分类', '153', '0', 'ProductTags/pasteTags', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(278, '图库', 0, 3, 'Gallery/index', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(279, '图库列表', 278, 0, 'Gallery/index', 0, '', '内容', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(280, '回收站', 278, 0, 'Gallery/recycle', 0, '', '内容', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(281, '新增', 279, 0, 'Gallery/add', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(282, '编辑', 279, 0, 'Gallery/edit', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(283, '改变状态', 279, 0, 'Gallery/setStatus', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(284, '保存', 279, 0, 'Gallery/update', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(285, '保存草稿', 279, 0, 'Gallery/autoSave', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(286, '移动', 279, 0, 'Gallery/move', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(287, '复制', 279, 0, 'Gallery/copy', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(288, '粘贴', 279, 0, 'Gallery/paste', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(289, '导入', 279, 0, 'Gallery/batchOperate', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(290, '文档排序', 279, 0, 'Gallery/sort', 1, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(291, '生成', 279, 0, 'Gallery/create', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(292, '查看', 279, 0, 'Gallery/redirectUrl', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(293, '还原', 280, 0, 'Gallery/permit', 0, '', '', 0, 1);
INSERT INTO `onethink_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES(294, '清空', 280, 0, 'Gallery/clear', 0, '', '', 0, 1);
-- ----------------------------
-- Table structure for onethink_model
-- ----------------------------
DROP TABLE IF EXISTS `onethink_model`;
CREATE TABLE `onethink_model` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型ID',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '模型标识',
  `title` char(30) NOT NULL DEFAULT '' COMMENT '模型名称',
  `extend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '继承的模型',
  `relation` varchar(30) NOT NULL DEFAULT '' COMMENT '继承与被继承模型的关联字段',
  `need_pk` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '新建表时是否需要主键字段',
  `field_sort` text NOT NULL COMMENT '表单字段排序',
  `field_group` varchar(255) NOT NULL DEFAULT '1:基础' COMMENT '字段分组',
  `attribute_list` text NOT NULL COMMENT '属性列表（表的字段）',
  `template_list` varchar(100) NOT NULL DEFAULT '' COMMENT '列表模板',
  `template_add` varchar(100) NOT NULL DEFAULT '' COMMENT '新增模板',
  `template_edit` varchar(100) NOT NULL DEFAULT '' COMMENT '编辑模板',
  `list_grid` text NOT NULL COMMENT '列表定义',
  `list_row` smallint(2) unsigned NOT NULL DEFAULT '10' COMMENT '列表数据长度',
  `search_key` varchar(50) NOT NULL DEFAULT '' COMMENT '默认搜索字段',
  `search_list` varchar(255) NOT NULL DEFAULT '' COMMENT '高级搜索的字段',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `engine_type` varchar(25) NOT NULL DEFAULT 'MyISAM' COMMENT '数据库引擎',
  `tags_category` varchar(100) NOT NULL DEFAULT '' COMMENT '挂载的标签ID',
  `product_tags_category` varchar(100) NOT NULL DEFAULT '' COMMENT '挂载的产品标签ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='文档模型表';

-- ----------------------------
-- Records of onethink_model
-- ----------------------------
INSERT INTO `onethink_model` VALUES ('1', 'document', '基础文档模型', '0', '', '1', '{\"1\":[\"22\",\"651\",\"646\",\"642\",\"235\",\"234\",\"233\",\"231\",\"218\",\"192\",\"191\",\"190\",\"123\",\"2\",\"3\",\"5\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"16\",\"17\",\"19\",\"20\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Document/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Document/create?id=[id]|生成,Document/redirectUrl?id=[id]|查看|_blank', '0', '', '', '1383891233', '1448845867', '1', 'MyISAM', '8', '1,2');
INSERT INTO `onethink_model` VALUES ('2', 'article', '文档主模型', '1', '', '1', '{\"1\":[\"22\",\"3\",\"35\",\"34\",\"33\",\"5\",\"24\",\"39\",\"40\",\"41\",\"235\",\"234\",\"233\",\"20\",\"14\"],\"2\":[\"123\",\"11\",\"218\",\"16\",\"17\",\"231\"],\"3\":[\"9\",\"13\",\"19\",\"26\",\"2\",\"25\"],\"4\":[\"642\",\"646\",\"10\",\"12\",\"651\"],\"5\":[\"190\",\"191\",\"192\"]}', '1:基础,2:常用,3:扩展,4:推荐,5:SEO', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Document/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Document/create?id=[id]|生成,Document/redirectUrl?id=[id]|查看|_blank', '0', '', '', '1383891243', '1448845852', '1', 'MyISAM', '8', '1,2');
INSERT INTO `onethink_model` (`id`, `name`, `title`, `extend`, `relation`, `need_pk`, `field_sort`, `field_group`, `attribute_list`, `template_list`, `template_add`, `template_edit`, `list_grid`, `list_row`, `search_key`, `search_list`, `create_time`, `update_time`, `status`, `engine_type`, `tags_category`, `product_tags_category`) VALUES(3, 'gallery', '基础图库模型', 0, '', 1, '{"1":["689","690","692","696","697","698","699","700","703","704","706","707","712","713","714","715","716","719","721","722","724"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Gallery/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Gallery/create?id=[id]|生成,Gallery/redirectUrl?id=[id]|查看|_blank1', 10, '', '', 1436776101, 1437707060, 1, 'MyISAM', '12', '');
INSERT INTO `onethink_model` (`id`, `name`, `title`, `extend`, `relation`, `need_pk`, `field_sort`, `field_group`, `attribute_list`, `template_list`, `template_add`, `template_edit`, `list_grid`, `list_row`, `search_key`, `search_list`, `create_time`, `update_time`, `status`, `engine_type`, `tags_category`, `product_tags_category`) VALUES(4, 'album', '图库主模型', 3, '', 1, '{"1":["690","726","728","732","692","680","729","730","731"],"2":["713","698","703","704","719","721","722","707"],"3":["696","700","706","689","727"],"4":["712","697","699","724"],"5":["714","715","716"]}', '1:基础,2:常用,3:扩展,4:推荐,5:SEO', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Gallery/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Gallery/create?id=[id]|生成,Gallery/redirectUrl?id=[id]|查看|_blank', 10, '', '', 1436777448, 1437096589, 1, 'MyISAM', '', '');
INSERT INTO `onethink_model` VALUES ('12', 'down', '基础下载模型', '0', '', '1', '{\"1\":[\"157\",\"155\",\"230\",\"215\",\"213\",\"212\",\"204\",\"203\",\"210\",\"209\",\"208\",\"211\",\"643\",\"202\",\"188\",\"187\",\"186\",\"185\",\"154\",\"152\",\"151\",\"149\",\"148\",\"147\",\"146\",\"145\",\"144\",\"140\",\"138\",\"137\",\"159\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Down/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Down/create?id=[id]|生成,Down/redirectUrl?id=[id]|查看|_blank', '10', '', '', '1413771056', '1448845883', '1', 'MyISAM', '1,7,10', '1,2');
INSERT INTO `onethink_model` VALUES ('7', 'package', '基础礼包模型', '0', '', '1', '{\"1\":[\"89\",\"206\",\"205\",\"644\",\"179\",\"178\",\"177\",\"124\",\"641\",\"69\",\"70\",\"72\",\"76\",\"77\",\"78\",\"79\",\"80\",\"81\",\"83\",\"84\",\"86\",\"87\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Package/setstatus?status=-1&ids=[id]|删除\r\nid:生成:Package/create?id=[id]|生成,Package/redirectUrl?id=[id]|查看|_blank', '10', '', '', '1413010314', '1448845905', '1', 'MyISAM', '1', '1');
INSERT INTO `onethink_model` VALUES ('8', 'pmain', '礼包主模型', '7', '', '1', '{\"1\":[\"70\",\"93\",\"97\",\"96\",\"95\",\"94\",\"92\",\"91\"],\"2\":[\"78\",\"124\",\"81\",\"86\",\"69\",\"76\",\"72\",\"83\",\"80\",\"84\",\"87\"],\"4\":[\"641\",\"77\",\"205\",\"206\",\"79\",\"644\"],\"3\":[\"177\",\"178\",\"179\"]}', '1:基础;2:扩展;4:推荐;3:SEO;', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Package/setstatus?status=-1&ids=[id]|删除,Card/index?did=[id]|卡号管理\r\nid:生成:Package/create?id=[id]|生成,Package/redirectUrl?id=[id]|查看|_blank', '10', '', '', '1413014740', '1426645492', '1', 'MyISAM', '1', '1,5');
INSERT INTO `onethink_model` VALUES ('11', 'feature', '基础专题模型', '0', '', '1', '{\"1\":[\"193\",\"126\",\"133\",\"237\",\"127\",\"654\",\"176\",\"128\",\"129\",\"130\",\"132\",\"134\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:package/edit?cate_id=[category_id]&id=[id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:package/setstatus?status=-1&ids=[id]|删除,[EDIT]&cate_id=[category_id]|编辑,package/setstatus?status=-1&ids=[id]|删除', '10', '', '', '1413539103', '1425261084', '1', 'MyISAM', '1', '1');
INSERT INTO `onethink_model` VALUES ('13', 'dmain', '下载主模型', '12', '', '1', '{\"1\":[\"670\",\"669\",\"668\",\"667\",\"157\",\"155\",\"138\",\"165\",\"164\",\"214\",\"166\",\"184\",\"169\",\"168\",\"228\",\"226\",\"229\",\"227\",\"207\",\"167\",\"664\",\"201\",\"666\",\"161\",\"183\",\"140\",\"160\"],\"2\":[\"230\",\"145\",\"146\",\"152\",\"215\",\"186\",\"185\",\"163\",\"162\"],\"3\":[\"187\",\"147\",\"643\",\"188\"],\"4\":[\"202\",\"203\",\"204\"],\"5\":[\"170\",\"209\",\"213\",\"210\",\"211\",\"159\",\"212\",\"151\",\"208\",\"149\",\"154\",\"137\",\"148\",\"144\"]}', '1:基础;2:常用;3:图片;4:SEO;5:扩展;', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\npid:子数据:[LIST]&cate_id=[smallimg]|子数据\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Down/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Down/create?id=[id]|生成,Down/redirectUrl?id=[id]|查看|_blank', '10', '', '', '1413773381', '1448845832', '1', 'MyISAM', '1,7,10', '1,2');
INSERT INTO `onethink_model` VALUES ('14', 'particle', '礼包文章模型', '7', '', '1', '{\"1\":[\"89\",\"206\",\"205\",\"644\",\"641\",\"70\",\"171\",\"172\",\"650\",\"173\",\"174\"],\"2\":[\"77\",\"79\",\"78\",\"124\",\"86\",\"76\",\"69\",\"72\",\"81\",\"80\",\"84\",\"83\",\"87\"],\"3\":[\"177\",\"178\",\"179\"]}', '1:基础;2:扩展;3:SEO', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Package/setstatus?status=-1&ids=[id]|删除\r\nid:生成:Package/create?id=[id]|生成,Package/redirectUrl?id=[id]|查看|_blank', '10', '', '', '1413876973', '1448845894', '1', 'MyISAM', '1', '1');
INSERT INTO `onethink_model` VALUES ('15', 'comment', '评论模型', '0', '', '1', '{\"1\":[\"200\",\"194\",\"195\",\"196\",\"197\",\"198\",\"199\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:down/edit?cate_id=[category_id]&id=[id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,down/setstatus?status=-1&ids=[id]|删除', '10', '', '', '1415454462', '1418350413', '1', 'MyISAM', '', '');
INSERT INTO `onethink_model` VALUES ('16', 'batch', '专区模型', '0', '', '1', '{\"1\":[\"393\",\"655\",\"376\",\"326\",\"327\",\"332\",\"328\",\"329\",\"330\",\"647\",\"333\",\"334\",\"437\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:down/edit?cate_id=[category_id]&id=[id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,down/setstatus?status=-1&ids=[id]|删除', '10', '', '', '1416622191', '1427853844', '1', 'MyISAM', '1,6,11', '1,5');
INSERT INTO `onethink_model` VALUES ('17', 'special', 'K页面模型', '0', '', '1', '{\"1\":[\"593\",\"532\",\"526\",\"527\",\"653\",\"576\",\"528\",\"529\",\"637\",\"534\",\"530\",\"533\"]}', '1:基础', '', '', '', '', 'id:编号\r\ntitle:标题:down/edit?cate_id=[category_id]&id=[id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,down/setstatus?status=-1&ids=[id]|删除', '10', '', '', '1416622624', '1425261138', '1', 'MyISAM', '1', '1');
INSERT INTO `onethink_model` VALUES ('18', 'link', '基础友情链接模型', '0', '', '1', '', '1:基础', '', '', '', '', '', '10', '', '', '1416817084', '1416817084', '1', 'MyISAM', '', '');
INSERT INTO `onethink_model` VALUES ('19', 'paihang', '排行榜模型', '12', '', '1', '{\"1\":[\"138\",\"202\",\"203\",\"204\",\"658\",\"159\"],\"2\":[\"154\",\"152\",\"149\",\"140\",\"137\",\"187\",\"147\",\"144\",\"151\",\"148\",\"146\",\"145\",\"643\",\"210\",\"209\",\"208\",\"211\",\"230\",\"215\",\"213\",\"212\",\"188\",\"185\",\"186\"]}', '1:基础;2:其他;', '', '', '', '', 'id:编号\r\ntitle:标题:[EDIT]&cate_id=[category_id]\r\ntype:类型\r\nupdate_time:最后更新\r\nstatus:状态\r\nview:浏览\r\nid:操作:[EDIT]&cate_id=[category_id]|编辑,Down/setstatus?status=-1&ids=[id]|删除\r\nid:静态:Down/create?id=[id]|生成,Down/redirectUrl?id=[id]|查看|_blank', '10', '', '', '1426057289', '1426062331', '1', 'MyISAM', '1,6,7,8,9,10', '1,2');

-- ----------------------------
-- Table structure for onethink_package
-- ----------------------------
DROP TABLE IF EXISTS `onethink_package`;
CREATE TABLE `onethink_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` char(40) NOT NULL DEFAULT '' COMMENT '标识',
  `title` char(80) NOT NULL COMMENT '标题',
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类',
  `description` char(140) NOT NULL COMMENT '描述',
  `root` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属ID',
  `model_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '内容模型ID',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '内容类型',
  `position` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '推荐位',
  `link` varchar(500) NOT NULL COMMENT '外链',
  `cover_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Logo图',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '可见性',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '截至时间',
  `attach` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `view` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `comment` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `extend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '扩展统计字段',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优先级',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `title_pinyin` varchar(255) NOT NULL COMMENT '标题首字母',
  `path_detail` varchar(255) NOT NULL COMMENT '静态文件路径',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_key` varchar(255) NOT NULL COMMENT 'SEO关键词',
  `seo_description` text NOT NULL COMMENT 'SEO描述',
  `brecom_id` int(10) unsigned NOT NULL COMMENT '推荐大图',
  `srecom_id` int(10) unsigned NOT NULL COMMENT '推荐小图',
  `home_position` varchar(100) NOT NULL COMMENT '全站推荐位',
  `vertical_pic` int(10) unsigned NOT NULL COMMENT '封面竖图',
  `abet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赞',
  `category_rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目根ID',
  PRIMARY KEY (`id`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `idx_status_type_pid` (`status`,`uid`,`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼包模型基础表';

-- ----------------------------
-- Records of onethink_package
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_package_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_package_category`;
CREATE TABLE `onethink_package_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `rootid` int(10) NOT NULL COMMENT '根分类ID',
  `depth` int(10) NOT NULL COMMENT '层级',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `path_index` varchar(255) NOT NULL DEFAULT '' COMMENT '分类首页生成路径规则',
  `path_lists` varchar(255) NOT NULL DEFAULT '' COMMENT '列表生成路径规则',
  `path_lists_index` varchar(255) NOT NULL DEFAULT '' COMMENT '列表首页名称（如果填写会同时生成填写名称的第一页）',
  `path_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '内容生成路径规则',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `template_index` varchar(100) NOT NULL COMMENT '频道页模板',
  `template_lists` varchar(100) NOT NULL COMMENT '列表页模板',
  `template_detail` varchar(100) NOT NULL COMMENT '详情页模板',
  `template_edit` varchar(100) NOT NULL COMMENT '编辑页模板',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '列表绑定模型',
  `model_sub` varchar(100) NOT NULL DEFAULT '' COMMENT '子文档绑定模型',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '允许发布的内容类型',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `allow_publish` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许发布内容',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许回复',
  `check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布的文章是否需要审核',
  `reply_model` varchar(100) NOT NULL DEFAULT '',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类图标',
  `describe` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `rootid` (`rootid`),
  KEY `depth` (`depth`),
  KEY `rootid-depth` (`rootid`,`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼包分类表';

-- ----------------------------
-- Records of onethink_package_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_package_particle
-- ----------------------------
DROP TABLE IF EXISTS `onethink_package_particle`;
CREATE TABLE `onethink_package_particle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `start_time` int(10) NOT NULL COMMENT '开测时间',
  `server` varchar(255) NOT NULL COMMENT '测试类型',
  `game_type` smallint(5) unsigned NOT NULL COMMENT '游戏类型',
  `server_type` char(10) NOT NULL COMMENT '当前情况',
  `conditions` varchar(100) NOT NULL DEFAULT '1' COMMENT '运行环境',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3654 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of onethink_package_particle
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_package_pmain
-- ----------------------------
DROP TABLE IF EXISTS `onethink_package_pmain`;
CREATE TABLE `onethink_package_pmain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `activation` text NOT NULL COMMENT '礼包激活',
  `content` text NOT NULL COMMENT '礼包内容',
  `card_type` char(10) NOT NULL COMMENT '卡号类型',
  `end_time` int(10) NOT NULL COMMENT '结束时间',
  `start_time` int(10) NOT NULL COMMENT '开始时间',
  `conditions` varchar(100) NOT NULL COMMENT '运行环境',
  `platform` varchar(255) NOT NULL COMMENT '运营平台',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3681 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of onethink_package_pmain
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_picture
-- ----------------------------
DROP TABLE IF EXISTS `onethink_picture`;
CREATE TABLE `onethink_picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片链接',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `old` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_picture
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_preset_site
-- ----------------------------
DROP TABLE IF EXISTS `onethink_preset_site`;
CREATE TABLE `onethink_preset_site` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `site_name` varchar(50) NOT NULL COMMENT '站点名称',
  `site_url` varchar(100) NOT NULL COMMENT '站点地址',
  `description` varchar(300) NOT NULL COMMENT '描述',
  `download_name` varchar(1000) NOT NULL COMMENT '下载名称',
  `download_url` varchar(1000) NOT NULL COMMENT '下载地址',
  `autofill` varchar(700) NOT NULL COMMENT '自动填充',
  `is_lb` tinyint(4) NOT NULL DEFAULT '1' COMMENT '链接方式：1-显示全部分站，0-不显示，自动负载均衡）',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '设置默认：1-是，0-否）',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `level` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='预设站点表';

-- ----------------------------
-- Records of onethink_preset_site
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_product_tags
-- ----------------------------
DROP TABLE IF EXISTS `onethink_product_tags`;
CREATE TABLE `onethink_product_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `category` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级标签ID',
  `rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点ID',
  `depth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '层级',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签图标',
  `old_id` int(10) unsigned NOT NULL COMMENT '原表ID',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '推荐位置',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `pid` (`pid`),
  KEY `rootid` (`rootid`),
  KEY `depth` (`depth`),
  KEY `rootid-depth` (`rootid`,`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品标签表';

-- ----------------------------
-- Records of onethink_product_tags
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_product_tags_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_product_tags_category`;
CREATE TABLE `onethink_product_tags_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `template` varchar(100) NOT NULL COMMENT '模板',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品标签分类表';

-- ----------------------------
-- Records of onethink_product_tags_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_product_tags_map
-- ----------------------------
DROP TABLE IF EXISTS `onethink_product_tags_map`;
CREATE TABLE `onethink_product_tags_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'tags表ID',
  `did` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(5) unsigned NOT NULL DEFAULT '50' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `did` (`did`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品标签和数据映射表';

-- ----------------------------
-- Records of onethink_product_tags_map
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_special
-- ----------------------------
DROP TABLE IF EXISTS `onethink_special`;
CREATE TABLE `onethink_special` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父页面ID',
  `category_id` int(10) NOT NULL COMMENT '分类ID',
  `title` varchar(120) NOT NULL COMMENT '标题',
  `seo_title` varchar(255) NOT NULL COMMENT 'seo标题',
  `keywords` varchar(255) NOT NULL COMMENT '关键字',
  `description` text NOT NULL COMMENT '专题描述',
  `layout` varchar(255) NOT NULL COMMENT '模版地址',
  `widget` text NOT NULL COMMENT '挂件',
  `content` mediumtext NOT NULL COMMENT '内容',
  `url_token` varchar(32) NOT NULL COMMENT '链接地址',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '专题图标',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题计数',
  `label` varchar(255) NOT NULL COMMENT '自定义',
  `sort` int(10) unsigned NOT NULL COMMENT '顺序',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '开启',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `interface` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '界面',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`id`),
  KEY `url_token` (`url_token`),
  KEY `title` (`title`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_special
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_static_page
-- ----------------------------
DROP TABLE IF EXISTS `onethink_static_page`;
CREATE TABLE `onethink_static_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group` varchar(50) NOT NULL COMMENT '分组，用于显示',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类型，0-PC版,1-手机版',
  `name` varchar(50) NOT NULL COMMENT '页面名字',
  `multipage` varchar(50) NOT NULL DEFAULT '' COMMENT '多页生成的页码参数，不填为单页',
  `multipage_index` varchar(50) NOT NULL DEFAULT '' COMMENT '多页的第一页名',
  `module_name` varchar(50) NOT NULL DEFAULT '' COMMENT '生成访问模块名',
  `controller_name` varchar(50) NOT NULL COMMENT '生成访问控制层名',
  `method_name` varchar(50) NOT NULL COMMENT '生成访问方法名',
  `params` text NOT NULL COMMENT '生成访问参数',
  `path` varchar(500) NOT NULL COMMENT '生成路径',
  `keywords` varchar(500) NOT NULL COMMENT '关键词',
  `title` varchar(500) NOT NULL COMMENT 'titile',
  `description` text NOT NULL COMMENT '厂商详细说明 描述',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='静态页面管理表';

-- ----------------------------
-- Records of onethink_static_page
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_tags
-- ----------------------------
DROP TABLE IF EXISTS `onethink_tags`;
CREATE TABLE `onethink_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `category` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '标志',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级标签ID',
  `rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点ID',
  `depth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '层级',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签图标',
  `old_id` int(10) unsigned NOT NULL COMMENT '原表ID',
  `img` int(10) NOT NULL DEFAULT '0' COMMENT '手机图标',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '推荐位置',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `pid` (`pid`),
  KEY `rootid` (`rootid`),
  KEY `depth` (`depth`),
  KEY `rootid-depth` (`rootid`,`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='标签表';

-- ----------------------------
-- Records of onethink_tags
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_tags_category
-- ----------------------------
DROP TABLE IF EXISTS `onethink_tags_category`;
CREATE TABLE `onethink_tags_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `template` varchar(100) NOT NULL COMMENT '模板',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='标签分类表';

-- ----------------------------
-- Records of onethink_tags_category
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_tags_map
-- ----------------------------
DROP TABLE IF EXISTS `onethink_tags_map`;
CREATE TABLE `onethink_tags_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'tags表ID',
  `did` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(5) unsigned NOT NULL DEFAULT '50' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `did` (`did`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='标签和数据映射表';

-- ----------------------------
-- Records of onethink_tags_map
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_template
-- ----------------------------
DROP TABLE IF EXISTS `onethink_template`;
CREATE TABLE `onethink_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(30) NOT NULL COMMENT '类型',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `path` varchar(255) NOT NULL COMMENT '模板路径',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
  `mobile_path` varchar(500) NOT NULL COMMENT '手机版模板路径',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='模板表';

-- ----------------------------
-- Records of onethink_template
-- ----------------------------
INSERT INTO `onethink_template` VALUES ('1', 'InsertDown', 'downDetail', '详细下载模板', 'Home@7230/Insert/downDetail', '1411635018', '1423808183', '1', 'Home@7230mobile/Insert/downDetail');
INSERT INTO `onethink_template` VALUES ('3', 'InsertArticle', 'documentDefault', '默认文章模板', 'Home@7230/Insert/documentDefault', '1411712917', '1423808171', '1', 'Home@7230mobile/Insert/documentDefault');
INSERT INTO `onethink_template` VALUES ('4', 'InternalLink', 'internalLink', '默认内链模板', 'Home@7230/Insert/internalLinkDefault', '1411713054', '1423808209', '1', 'Home@7230mobile/Insert/internalLinkDefault');
INSERT INTO `onethink_template` VALUES ('7', 'InsertDown', 'downSimple', '简单下载模板', 'Home@7230/Insert/downSimple', '1417078262', '1423808196', '1', 'Home@7230mobile/Insert/downSimple');

-- ----------------------------
-- Table structure for onethink_ucenter_admin
-- ----------------------------
DROP TABLE IF EXISTS `onethink_ucenter_admin`;
CREATE TABLE `onethink_ucenter_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员用户ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '管理员状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Records of onethink_ucenter_admin
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_ucenter_app
-- ----------------------------
DROP TABLE IF EXISTS `onethink_ucenter_app`;
CREATE TABLE `onethink_ucenter_app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `title` varchar(30) NOT NULL COMMENT '应用名称',
  `url` varchar(100) NOT NULL COMMENT '应用URL',
  `ip` char(15) NOT NULL COMMENT '应用IP',
  `auth_key` varchar(100) NOT NULL COMMENT '加密KEY',
  `sys_login` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '同步登陆',
  `allow_ip` varchar(255) NOT NULL COMMENT '允许访问的IP',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '应用状态',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='应用表';

-- ----------------------------
-- Records of onethink_ucenter_app
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_ucenter_member
-- ----------------------------
DROP TABLE IF EXISTS `onethink_ucenter_member`;
CREATE TABLE `onethink_ucenter_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` char(16) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `email` char(32) NOT NULL COMMENT '用户邮箱',
  `mobile` char(15) NOT NULL COMMENT '用户手机',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '用户状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户表';


-- ----------------------------
-- Table structure for onethink_ucenter_setting
-- ----------------------------
DROP TABLE IF EXISTS `onethink_ucenter_setting`;
CREATE TABLE `onethink_ucenter_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '设置ID',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型（1-用户配置）',
  `value` text NOT NULL COMMENT '配置数据',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设置表';

-- ----------------------------
-- Records of onethink_ucenter_setting
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_url
-- ----------------------------
DROP TABLE IF EXISTS `onethink_url`;
CREATE TABLE `onethink_url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '链接唯一标识',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `short` char(100) NOT NULL DEFAULT '' COMMENT '短网址',
  `status` tinyint(2) NOT NULL DEFAULT '2' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='链接表';

-- ----------------------------
-- Records of onethink_url
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_userdata
-- ----------------------------
DROP TABLE IF EXISTS `onethink_userdata`;
CREATE TABLE `onethink_userdata` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `type` tinyint(3) unsigned NOT NULL COMMENT '类型标识',
  `target_id` int(10) unsigned NOT NULL COMMENT '目标id',
  UNIQUE KEY `uid` (`uid`,`type`,`target_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_userdata
-- ----------------------------

-- ----------------------------
-- Table structure for onethink_widgets_instance
-- ----------------------------
DROP TABLE IF EXISTS `onethink_widgets_instance`;
CREATE TABLE `onethink_widgets_instance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `core_file` varchar(255) NOT NULL,
  `core_slot` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `core_id` varchar(20) NOT NULL,
  `widgets_type` varchar(30) NOT NULL DEFAULT '',
  `app` varchar(30) NOT NULL,
  `theme` varchar(30) NOT NULL,
  `params` varchar(255) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of onethink_widgets_instance
-- ----------------------------
INSERT INTO `onethink_widgets_instance` VALUES ('1', './Application/Admin/View/Feature/widget/default.htm', '0', '1', 'special', '', '', '', '2014-10-29 15:24:00');
INSERT INTO `onethink_widgets_instance` VALUES ('2', './Application/Admin/View/Feature/widget/ad.htm', '0', 'pro', 'ad', '', '', '', '2014-10-29 15:21:59');

-- ----------------------------
-- 图库模块
-- ----------------------------
CREATE TABLE IF NOT EXISTS `onethink_gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` char(40) NOT NULL COMMENT '标识',
  `title` char(80) NOT NULL COMMENT '标题',
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类',
  `description` varchar(500) NOT NULL COMMENT '摘要',
  `root` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属ID',
  `model_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '内容模型ID',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '内容类型',
  `link` varchar(500) NOT NULL COMMENT '外链',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '可见性',
  `attach` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `view` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `comment` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `extend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '扩展统计字段',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优先级',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `title_pinyin` varchar(255) NOT NULL COMMENT '标题首字母',
  `path_detail` varchar(255) NOT NULL COMMENT '静态文件路径',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键词',
  `seo_description` text NOT NULL COMMENT 'SEO描述',
  `category_rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目根ID',
  `pagination_type` char(10) NOT NULL DEFAULT '0' COMMENT '分页类型',
  `ding` int(10) unsigned NOT NULL COMMENT '顶',
  `cai` int(10) unsigned NOT NULL COMMENT '踩',
  `old_id` int(10) unsigned NOT NULL COMMENT '老数据ID',
  `edit_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑id',
  `mobile_recom` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Mobile推荐位',
  `atlas` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通用',
  `atlas_a` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面A',
  `atlas_b` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面B',
  `atlas_c` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面C',
  `atlas_d` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面D',
  `atlas_e` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面E',
  `atlas_f` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面F',
  `atlas_g` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封面G',
  `rec_recom` varchar(100) NOT NULL COMMENT '[版块] 娱乐',
  `channel_recom` varchar(100) NOT NULL COMMENT '[版块] 图库',
  `list_recom` varchar(100) NOT NULL DEFAULT '0' COMMENT '列表',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `onethink_gallery_album` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `imgpack` text NOT NULL COMMENT '图库',
  `sub_title` varchar(255) NOT NULL COMMENT '副标题',
  `template` varchar(255) NOT NULL COMMENT '详情页显示模板',
  `font` char(10) NOT NULL COMMENT '标题字体',
  `author` varchar(255) NOT NULL COMMENT '作者',
  `source` varchar(255) NOT NULL COMMENT '来源',
  `source_url` varchar(255) NOT NULL COMMENT '出处网址',
  `font_color` char(50) NOT NULL COMMENT '标题颜色',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `onethink_gallery_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `poly_name` varchar(30) NOT NULL COMMENT '聚合名称',
  `recommend_view_name` varchar(20) NOT NULL,
  `title` varchar(50) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `rootid` int(10) NOT NULL,
  `depth` int(10) NOT NULL,
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `vertical_pic` int(10) unsigned NOT NULL COMMENT '分类大图',
  `path_index` varchar(255) NOT NULL DEFAULT '' COMMENT '首页生成路径规则',
  `path_lists` varchar(255) NOT NULL DEFAULT '' COMMENT '列表生成路径规则',
  `path_lists_index` varchar(255) NOT NULL DEFAULT '' COMMENT '列表首页名称（如果填写会同时生成填写名称的第一页）',
  `path_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '详情生成路径规则',
  `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
  `describe` varchar(200) NOT NULL,
  `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `template_index` varchar(100) NOT NULL COMMENT '频道页模板',
  `template_lists` varchar(100) NOT NULL COMMENT '列表页模板',
  `template_detail` varchar(100) NOT NULL COMMENT '详情页模板',
  `template_edit` varchar(100) NOT NULL COMMENT '编辑页模板',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '列表绑定模型',
  `model_sub` varchar(100) NOT NULL DEFAULT '' COMMENT '子文档绑定模型',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '允许发布的内容类型',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
  `allow_publish` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许发布内容',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
  `reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许回复',
  `check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布的文章是否需要审核',
  `reply_model` varchar(100) NOT NULL DEFAULT '',
  `extend` text NOT NULL COMMENT '扩展设置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `position` tinyint(4) NOT NULL DEFAULT '0',
  `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类图标',
  `old_id` int(11) DEFAULT NULL COMMENT '原有分类ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分类表';





