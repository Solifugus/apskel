<?php

/*
Rob: 972-249-4951
+-----------+-------------+------+-----+---------+----------------+
| Field     | Type        | Null | Key | Default | Extra          |
+-----------+-------------+------+-----+---------+----------------+
| id        | int(11)     | NO   | PRI | NULL    | auto_increment |
| user_name | varchar(15) | YES  |     | NULL    |                |
| password  | varchar(32) | YES  |     | NULL    |                |
| surname   | varchar(15) | YES  |     | NULL    |                |
| forename  | varchar(15) | YES  |     | NULL    |                |
| email     | varchar(60) | YES  |     | NULL    |                |
| super     | tinyint(1)  | YES  |     | 0       |                |
| active    | tinyint(1)  | YES  |     | 1       |                |
+-----------+-------------+------+-----+---------+----------------+

+-----------+-------------+------+-----+---------+----------------+
| Field     | Type        | Null | Key | Default | Extra          |
+-----------+-------------+------+-----+---------+----------------+
| id        | int(11)     | NO   | PRI | NULL    | auto_increment |
| user_id   | int(11)     | YES  |     | NULL    |                |
| attribute | varchar(15) | YES  |     | NULL    |                |
| value     | text        | YES  |     | NULL    |                |
+-----------+-------------+------+-----+---------+----------------+
*/

$storage = array (
	'users' => array ( 
		'id'        => array ( 'type' => 'INT(11)',     'key' => 'primary' ),
		'user_name' => array ( 'type' => 'VARCHAR(15)', 'default' => 'null', 'filter' => null ),
		'password'  => array ( 'type' => 'VARCHAR(32)', 'default' => 'null', 'filter' => null ),
		'surname'   => array ( 'type' => 'VARCHAR(15)', 'default' => 'null', 'filter' => null ),
		'forename'  => array ( 'type' => 'VARCHAR(15)', 'default' => 'null', 'filter' => null ),
		'email'     => array ( 'type' => 'VARCHAR(60)', 'default' => 'null', 'filter' => null ),
		'super'     => array ( 'type' => 'BOOLEAN',     'default' => 0,      'filter' => null ),
		'active'    => array ( 'type' => 'BOOLEAN',     'default' => 1,      'filter' => null ),
		),
	'user_attributes' => array (
		'id'        => array ( 'type' => 'INT(11)',     'key' => 'primary' ),
		'user_id'   => array ( 'type' => 'INT(11)'      'default' => 'NULL' ),
		'attribute' => array ( 'type' => 'VARCHAR(15)', 'default' => 'NULL', 'filter' => null ),
		'value'     => array ( 'type' => 'TEXT',        'default' => 'NULL', 'filter' => null ),
	), 
);

