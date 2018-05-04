CREATE TABLE `region` (
  `region_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT '父区域id',
  `region_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '区域名称',
  `region_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '区域类型，country-国、province-省、city-市、county-区、town-街道、village-村',
  PRIMARY KEY (`region_id`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `region_type` (`region_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='region区域表';

