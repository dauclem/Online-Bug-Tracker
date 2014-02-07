<?php

require __DIR__.'/../private/setup.php';

if ($_POST) {
	$email       = trim($_POST['email']);
	$pseudo      = trim($_POST['pseudo']);
	$password    = md5(trim($_POST['password']));
	$rewritten   = preg_replace('/[^a-z0-9]+/', '-', strtolower($pseudo));
	$new_dir     = __DIR__.'/accounts/'.$rewritten;
	$main_domain = implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2));

	if (file_exists($new_dir)) {
		// Check if account already exists
		$sth = $pdo->prepare('SELECT `rewritten`
								FROM `account`
 								WHERE (`email` = :email OR `pseudo` = :pseudo)
 									AND `password` = :password');
		$sth->execute(array(
						  'email'    => $email,
						  'pseudo'   => $pseudo,
						  'password' => $password,
					  ));
		$result    = $sth->fetch(PDO::FETCH_ASSOC);
		$rewritten = $result['rewritten'];
		if ($rewritten) {
			header('Status: 302 Found', true, 302);
			header('Location: http://'.$rewritten.'.'.$main_domain.'/');
		}
	} else {
		$sth = $pdo->prepare('INSERT INTO `account` (`email`, `pseudo`, `password`, `rewritten`)
								VALUES (:email, :pseudo, :password, :rewritten)');
		$sth->execute(array(
						  'email'     => $email,
						  'pseudo'    => $pseudo,
						  'password'  => $password,
						  'rewritten' => $rewritten,
					  ));
		$account_id = $pdo->lastInsertId();

		if ($account_id) {
			$htaccess_file = __DIR__.'/.htaccess';
			$rewrite_rule  = 'RewriteCond %{HTTP_HOST} ^'.$rewritten.'.'.$main_domain.'$'."\n"
							 .'RewriteCond %{REQUEST_URI} !^/accounts/'.$rewritten.'/'."\n"
							 .'RewriteRule ^(.*)$ accounts/'.$rewritten.'/$1 [L,QSA]'."\n";

			if (!file_exists($htaccess_file)) {
				file_put_contents($htaccess_file, 'RewriteEngine On'."\n\n"
												  .'RewriteCond %{HTTP_HOST} ^'.$main_domain.'$'."\n"
												  .'RewriteRule ^accounts/([^/]+)/(.*)$ http://$1.'.$main_domain.'/$2 [R=301,L,QSA]'."\n");
			}
			file_put_contents($htaccess_file, file_get_contents($htaccess_file)."\n".$rewrite_rule);


			// Mantis
			exec('cp -r '.dirname(__DIR__).'/private/bt/mantisbt/mantisbt-1.2.15 '.$new_dir, $output);
			file_put_contents($new_dir.'/admin/tmp', $pseudo."\n".$password."\n".$email);

			/*
			// Bugzilla
			exec('cp -r '.dirname(__DIR__).'/private/bt/bugzilla/bugzilla-4.4.2 '.$new_dir, $output);

			$config_file = $new_dir.'/localconfig';
			$replace     = array(
				'%DB_NAME%' => $rewritten.'_bugzilla',
				'%DB_USER%' => $config['pdo_username'],
				'%DB_PASS%' => $config['pdo_password'],
			);
			file_put_contents($config_file, str_replace(array_keys($replace), array_values($replace), file_get_contents($config_file)));
			*/


			// Mantis
			header('Status: 302 Found', true, 302);
			header('Location: http://'.$rewritten.'.'.$main_domain.'/admin/install.php');

			/*
			// Bugzilla
			header('Location: http://'.$rewritten.'.'.$main_domain.'/');
			*/

			exit;
		}
	}
}

require __DIR__.'/../private/templates/index.tpl.php';