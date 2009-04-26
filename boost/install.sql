-- $Id: install.sql,v 1.3 2005/01/13 03:46:08 blindman1344 Exp $

CREATE TABLE mod_featuredphoto_photos (
  id int(10) unsigned NOT NULL default '0',
  rec_date int(11) unsigned NOT NULL default '0',
  hidden int(1) NOT NULL default '1',
  name varchar(100) NOT NULL default '',
  caption text,
  credit text,
  filename varchar(255) NOT NULL default '',
  width int(4) NOT NULL default '0',
  height int(4) NOT NULL default '0',
  size int(9) unsigned NOT NULL default '0',
  type varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
);

CREATE TABLE mod_featuredphoto_settings (
  mode int(1) NOT NULL default '0',
  showblock int(1) NOT NULL default '1',
  blocktitle varchar(100) NOT NULL default '',
  current int(10) unsigned NOT NULL default '0',
  resize_width int(4) unsigned NOT NULL default '600',
  resize_height int(4) unsigned NOT NULL default '600'
);

INSERT INTO mod_featuredphoto_settings VALUES ('0', '1', 'Featured Photo', '0', '600', '600');
