<?php

require __DIR__.'/setup.vars.php';

$pdo = new PDO($config['pdo_dns'], $config['pdo_username'], $config['pdo_password']);
