CREATE TABLE featuredphoto_photos (
  id INT NOT NULL,
  block_id INT NOT NULL,
  image_id INT NOT NULL,
  active SMALLINT NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE featuredphoto_blocks (
  id INT NOT NULL,
  key_id INT DEFAULT '0' NOT NULL,
  title VARCHAR(255) NOT NULL,
  mode SMALLINT NOT NULL,
  active SMALLINT NOT NULL,
  current_photo INT NOT NULL,
  flickr_set BIGINT unsigned NOT NULL,
  tn_width INT NOT NULL,
  tn_height INT NOT NULL,
  template VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE featuredphoto_pins (
  block_id INT DEFAULT '0' NOT NULL,
  key_id INT DEFAULT '0' NOT NULL
);
