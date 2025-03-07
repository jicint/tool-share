<?php
$content = <<<'EOD'
actor: ApiTester
modules:
    enabled:
        - Laravel:
            environment_file: .env.testing
            cleanup: true
            run_database_migrations: true
            database_seeder_class: DatabaseSeeder
        - REST:
            depends: Laravel
            part: Json
        - \Tests\Support\Helper\Api
        - Asserts
EOD;

file_put_contents('tests/Api.suite.yml', $content, LOCK_EX); 