CREATE TABLE `interest` (
`id`  bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
`account` varchar(16) NOT NULL,
`category`  varchar(12) NULL ,
`topic`  varchar(128) NULL ,
`flag`  int NULL ,
`page`  int NULL ,
`releated_account` varchar(128) NULL,
PRIMARY KEY (`id`)
)