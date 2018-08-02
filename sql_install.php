<?php

$sql = array();
$sql[_DB_PREFIX_.'cquestion'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cquestion` (
            `id_cquestion` int (11) NOT NULL AUTO_INCREMENT,
            `question` varchar(250) NOT NULL,
            `answer` text NOT NULL,
            `is_published` tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id_cquestion`)
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
$sql[_DB_PREFIX_.'category_cquestion'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'category_cquestion` (
            `id_category` int (11) NOT NULL,
            `id_cquestion` int (11) NOT NULL,
            PRIMARY KEY (`id_category`, `id_cquestion`)
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';