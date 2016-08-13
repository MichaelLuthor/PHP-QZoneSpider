CREATE TABLE `profiles` (
`id`  bigint UNSIGNED UNIQUE AUTO_INCREMENT,
`uin`  varchar(16) NULL ,
`is_famous`  varchar(64) NULL ,
`famous_custom_homepage`  varchar(256) NULL ,
`nickname`  varchar(128) NULL ,
`emoji`  varchar(128) NULL ,
`spacename`  varchar(128) NULL ,
`desc`  varchar(512) NULL ,
`signature`  varchar(512) NULL ,
`avatar`  varchar(256) NULL ,
`sex_type`  int NULL ,
`sex`  int NULL ,
`animalsign_type`  int NULL ,
`constellation_type`  int NULL ,
`constellation`  int NULL ,
`age_type`  int NULL ,
`age`  int NULL ,
`islunar`  int NULL ,
`birthday_type`  int NULL ,
`birthyear`  int NULL ,
`birthday`  varchar(32) NULL ,
`bloodtype`  int NULL ,
`address_type`  int NULL ,
`country`  varchar(16) NULL ,
`province`  varchar(16) NULL ,
`city`  varchar(16) NULL ,
`home_type`  int NULL ,
`hco`  varchar(16) NULL ,
`hp`  varchar(16) NULL ,
`hc`  varchar(16) NULL ,
`marriage`  int NULL ,
`career`  varchar(64) NULL ,
`company`  varchar(128) NULL ,
`cco`  varchar(256) NULL ,
`cp`  varchar(256) NULL ,
`cc`  varchar(256) NULL ,
`cb`  varchar(256) NULL ,
`mailname`  varchar(128) NULL ,
`mailcellphone`  varchar(128) NULL ,
`mailaddr`  varchar(256) NULL ,
`qzworkexp`  varchar(256) NULL ,
`qzeduexp`  varchar(256) NULL ,
`ptimestamp`  varchar(256) NULL ,
PRIMARY KEY (`id`)
)