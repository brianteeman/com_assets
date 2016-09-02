DROP TABLE IF EXISTS `#__assets_assets`;
DROP TABLE IF EXISTS `#__assets_types`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_assets.%');