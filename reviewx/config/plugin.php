<?php

namespace Rvx;

return ['name' => 'ReviewX', 'slug' => 'reviewx', 'prefix' => 'rvx', 'rest_api_namespace' => 'reviewx', 'version' => '0.0.1', 'initial_handlers' => ['activated' => \Rvx\Handlers\PluginActivatedHandler::class, 'deactivated' => \Rvx\Handlers\PluginDeactivatedHandler::class, 'uninstalled' => null], 'providers' => [\Rvx\WPDrill\Providers\ShortcodeServiceProvider::class, \Rvx\WPDrill\Providers\DBServiceProvider::class, \Rvx\WPDrill\Providers\RequestServiceProvider::class, \Rvx\WPDrill\Providers\MenuServiceProvider::class, \Rvx\WPDrill\Providers\ViewServiceProvider::class, \Rvx\WPDrill\Providers\ConfigServiceProvider::class, \Rvx\WPDrill\Providers\EnqueueServiceProvider::class, \Rvx\WPDrill\Providers\RoutingServiceProvider::class, \Rvx\WPDrill\Providers\MigrationServiceProvider::class, \Rvx\WPDrill\Providers\CommonServiceProvider::class, \Rvx\Providers\PluginServiceProvider::class], 'build' => ['output_dir' => '.dist', 'commands' => ['before' => [['./reset-client.sh'], ['./client-build.sh']], 'after' => [['composer', 'dump-autoload']]], 'cleanup' => ['composer.json', 'composer.lock', 'scoper.inc.php', '.editorconfig', '.gitignore', '.php-cs-fixer.cache', 'package.json', 'package-lock.json', 'tests', '.env.dev', '.git', 'node_modules', '.vscode', 'frontend/.nx.cache', 'frontend/.vscode', 'frontend/apps', 'frontend/libs', 'frontend/locals', 'frontend/node_modules', 'frontend/.editorconfig', 'frontend/.eslintignore', 'frontend/.eslintrc.json', 'frontend/.gitignore', 'frontend/.prettierignore', 'frontend/.prettierrc', 'frontend/client-build.sh', 'frontend/.prettierrc', 'frontend/gettext-extract.js', 'frontend/locals.js', 'frontend/localsToJson.js', 'frontend/nx.json', 'frontend/package.json', 'frontend/README.md', 'frontend/reset.sh', 'frontend/tsconfig.base.json', 'frontend/vitest.workspace.ts', 'frontend/yarn.lock', 'frontend/dist/frontend.dist.libs.share-ui', "frontend/locals", "frontend/package-lock.json", "vendor/php-di/php-di/src/Compiler/Template.php", "storage/cache/views", "php-scoper", "build.sh", "nohup.out", "phpunit.xml", "reset-client.sh", "run_nohup.sh", "tunnel.sh", "wpdrill", "yarn.lock", 'storage/cache/views/', 'client-build.sh']]];
