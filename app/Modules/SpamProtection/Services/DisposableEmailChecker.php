<?php

namespace App\Modules\SpamProtection\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DisposableEmailChecker
{
    protected array $localList = [
        // Common disposable email domains
        'tempmail.com',
        'throwaway.email',
        'guerrillamail.com',
        'guerrillamail.info',
        'guerrillamail.net',
        'guerrillamail.org',
        'mailinator.com',
        '10minutemail.com',
        '10minutemail.net',
        'temp-mail.org',
        'fakeinbox.com',
        'sharklasers.com',
        'trashmail.com',
        'yopmail.com',
        'getairmail.com',
        'dispostable.com',
        'tempail.com',
        'throwawaymail.com',
        'maildrop.cc',
        'mailnesia.com',
        'mintemail.com',
        'mytemp.email',
        'tempr.email',
        'discard.email',
        'spamgourmet.com',
        'mailcatch.com',
        'tempinbox.com',
        'fakemailgenerator.com',
        'emailondeck.com',
        'getnada.com',
        'mohmal.com',
        'tempmailo.com',
        'burnermail.io',
        'temp-mail.io',
        'fakemail.net',
        'emailfake.com',
        'crazymailing.com',
        'tempmailer.com',
        'spambox.us',
        'mailsac.com',
        'inboxkitten.com',
        'temp-mail.ru',
        'dropmail.me',
        'harakirimail.com',
        'mailforspam.com',
    ];

    public function isDisposable(string $email): bool
    {
        $domain = $this->extractDomain($email);

        // Check local list first (fast)
        if (in_array($domain, $this->localList, true)) {
            return true;
        }

        // Check cached remote list
        $remoteDomains = $this->getRemoteDisposableDomains();
        if (in_array($domain, $remoteDomains, true)) {
            return true;
        }

        return false;
    }

    protected function extractDomain(string $email): string
    {
        return strtolower(substr(strrchr($email, '@'), 1));
    }

    protected function getRemoteDisposableDomains(): array
    {
        return Cache::remember('disposable_email_domains', 86400, function () {
            try {
                $response = Http::timeout(5)->get(
                    'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/master/disposable_email_blocklist.conf'
                );

                if ($response->successful()) {
                    return array_filter(
                        array_map('trim', explode("\n", $response->body())),
                        fn ($line) => !empty($line) && !str_starts_with($line, '#')
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch disposable email domains list', [
                    'error' => $e->getMessage(),
                ]);
            }

            return [];
        });
    }

    public function getLocalList(): array
    {
        return $this->localList;
    }
}
