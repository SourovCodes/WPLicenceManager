<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/SourovCodes/WPLicenceManager.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('159.223.51.52')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/WPLicenceManager');

// Hooks

after('deploy:failed', 'deploy:unlock');
