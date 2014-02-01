<?php

require __DIR__.'/../private/setup.php';

if ($_POST) {
	$email     = $_POST['email'];
	$pseudo    = $_POST['pseudo'];
	$password  = md5('péoàm'.md5($_POST['password']).'ùp)=àç');
	$rewritten = preg_replace('/[^a-z0-9]+/', '-', strtolower($pseudo));
	$new_dir   = __DIR__.'/accounts/'.$rewritten;

	if (!file_exists($new_dir)) {
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
			$main_domain   = implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2));
			$htaccess_file = __DIR__.'/.htaccess';
			$rewrite_rule  = 'RewriteCond %{HTTP_HOST} ^'.$rewritten.'.'.$main_domain.'$'."\n"
							 .'RewriteCond %{REQUEST_URI} !^/accounts/'.$rewritten.'/'."\n"
							 .'RewriteRule ^(.*)$ accounts/'.$rewritten.'/$1 [L,QSA]'."\n";

			if (!file_exists($htaccess_file)) {
				file_put_contents($htaccess_file, 'RewriteEngine On'."\n");
			}
			file_put_contents($htaccess_file, file_get_contents($htaccess_file)."\n".$rewrite_rule);


			// Mantis
			exec('cp -r '.dirname(__DIR__).'/private/bt/mantisbt/mantisbt-1.2.15 '.$new_dir, $output);

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