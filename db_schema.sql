
CREATE TABLE user (
  user_id INT(11) NOT NULL AUTO_INCREMENT,
  email VARCHAR(50) NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  password VARCHAR(100) NOT NULL,
  PRIMARY KEY (user_id),
  KEY (user_id, password)
) ENGINE InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;
