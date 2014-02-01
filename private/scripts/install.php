<?php

require __DIR__.'/../setup.vars.php';

$pdo_dns_without_db = str_replace('dbname='.$config['database'].';', '', $config['pdo_dns']);
$pdo = new PDO($pdo_dns_without_db, $config['pdo_username'], $config['pdo_password']);

$pdo->exec('DROP DATABASE IF EXISTS `'.$config['database'].'`');
$pdo->exec('CREATE DATABASE `'.$config['database'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
$pdo->exec('USE `'.$config['database'].'`');

$pdo->exec('CREATE TABLE IF NOT EXISTS `account` (
			`id_account` INT(10) NOT NULL AUTO_INCREMENT,
			`email` VARCHAR(255) NOT NULL DEFAULT "",
			`pseudo` VARCHAR(50) NOT NULL DEFAULT "",
			`password` CHAR(32) NOT NULL DEFAULT "",
			`rewritten` VARCHAR(50) NOT NULL DEFAULT "",
			PRIMARY KEY (`id_account`),
			UNIQUE (`rewritten`)
			) ENGINE=InnoDB ROW_FORMAT=DYNAMIC CHARSET=UTF8;');