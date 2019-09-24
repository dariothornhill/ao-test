# Setup

## PHP

### php 7.2

### php-mysql (pdo)

## MYSQL

- schema: 'myDB'
- username: 'user1'
- password: 'pass1'

Two tables

```sql
CREATE TABLE `table1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(45) DEFAULT NULL,
  `lastname` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

 CREATE TABLE `table2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
```

# Run

```bash
php index.php
```
