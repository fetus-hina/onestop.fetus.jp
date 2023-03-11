<?php //phpcs:disable

declare(strict_types=1);

namespace Deployer;

require('recipe/yii2-app-basic.php');

set('application', 'onestop');
set('repository', 'git@github.com:fetus-hina/onestop.fetus.jp.git');
set('composer_options', implode(' ', [
    'install',
    '--no-interaction',
    '--no-progress',
    '--no-suggest',
    '--optimize-autoloader',
    '--prefer-dist',
    '--verbose',
]));
set('git_tty', true);
add('shared_files', [
    'config/cookie-secret.php',
]);
add('shared_dirs', [
    'database',
    'runtime',
]);
add('writable_dirs', [
    'database',
    'runtime',
    'web/assets',
]);
set('writable_mode', 'chmod');
set('writable_chmod_recursive', false);
set('softwarecollections', []);

set('bin/make', fn () => locateBinaryPath('make'));
set('bin/npm', fn () => locateBinaryPath('npm'));
set('bin/php', fn () => locateBinaryPath('php'));

host('2403:3a00:202:1127:49:212:205:127')
    ->user('onestop')
    ->stage('production')
    ->roles('app')
    ->set('deploy_path', '~/app');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:git_config',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'deploy:run_migrations',
    'deploy:build',
    'deploy:vendors_production',
    'deploy:symlink',
    'deploy:clear_opcache',
    'deploy:clear_proxy',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy the project');

task('deploy:git_config', function () {
    run('git config --global advice.detachedHead false');
});

after('deploy:update_code', 'deploy:production');

task('deploy:production', function () {
    within('{{release_path}}', function () {
        run('touch .production');
        run('rm -f web/index.test.php');
    });
});

task('deploy:vendors', function () {
    within('{{release_path}}', function () {
        run('{{bin/composer}} {{composer_options}}');
        run('{{bin/npm}} clean-install');
    });
});

task('deploy:vendors_production', function () {
    within('{{release_path}}', function () {
        run('{{bin/composer}} {{composer_options}} --no-dev');
        run('{{bin/npm}} prune --production');
    });
});

task('deploy:build', function () {
    within('{{release_path}}', function () {
        run('{{bin/make}}');
    });
});

task('deploy:clear_opcache', function () {
    run('curl -f --insecure --resolve onestop.fetus.jp:443:127.0.0.1 https://onestop.fetus.jp/site/clear-opcache');
});

task('deploy:clear_proxy', function () {
});

after('deploy:failed', 'deploy:unlock');
