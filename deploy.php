<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'zero_downtime_deploy');

// Project repository
set('repository', 'git@github.com:huongnx-0984/autodeploy_example.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys
add('shared_files', [".env"]);
add('shared_dirs', ["storage"]);

// Writable dirs by web server
add('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/logs',
]);

// Hosts
host('10.0.4.18')
    ->user('deploy')
    ->stage('staging')
    ->set('deploy_path', '~/{{application}}');

// Tasks
task('setup-laravel', function () {
    run('cd {{release_path}} && cp .env.example {{deploy_path}}/shared/.env');
    run('cd {{release_path}} && php artisan key:generate');
});

task('reload:php-fpm', function () {
    run('sudo /etc/init.d/php7.2-fpm reload');
});

task('yarn:install', function () {
    run('cd {{release_path}} && yarn install');
});

task('yarn:run:production', function () {
    run('cd {{release_path}} && yarn run production');
});

desc('Init project');
task('init-project', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'setup-laravel',
    'deploy:symlink',
    'deploy:unlock',
]);

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:optimize',
    'deploy:symlink',
    'deploy:unlock',
    'artisan:migrate',
    'cleanup',
    'reload:php-fpm',
    'yarn:install',
    'yarn:run:production'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
desc('Deployed successfully!');
