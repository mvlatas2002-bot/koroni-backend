<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    protected $signature = 'notifications:vapid-keys';

    protected $description = 'Generate VAPID keys for browser push notifications.';

    public function handle(): int
    {
        $this->ensureWindowsOpenSslConfig();

        try {
            $keys = VAPID::createVapidKeys();
        } catch (\Throwable $exception) {
            $this->error('Could not generate VAPID keys from this PHP/OpenSSL runtime.');
            $this->warn('On Windows, run this once before the command if needed:');
            $this->line('$env:OPENSSL_CONF="C:\Program Files\Git\mingw64\etc\ssl\openssl.cnf"');

            return self::FAILURE;
        }

        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:admin@koronisa.local');
        $this->line('NOTIFICATION_PUSH_ENABLED=true');

        return self::SUCCESS;
    }

    private function ensureWindowsOpenSslConfig(): void
    {
        if (PHP_OS_FAMILY !== 'Windows' || getenv('OPENSSL_CONF')) {
            return;
        }

        foreach ([
            'C:\Program Files\Git\mingw64\etc\ssl\openssl.cnf',
            'C:\Program Files\Git\usr\ssl\openssl.cnf',
        ] as $candidate) {
            if (is_file($candidate)) {
                putenv("OPENSSL_CONF={$candidate}");
                return;
            }
        }
    }
}
