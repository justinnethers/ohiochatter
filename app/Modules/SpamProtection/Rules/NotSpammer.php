<?php

namespace App\Modules\SpamProtection\Rules;

use App\Modules\SpamProtection\Services\SpamProtectionService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSpammer implements DataAwareRule, ValidationRule
{
    protected array $data = [];
    protected SpamProtectionService $spamService;

    public function __construct()
    {
        $this->spamService = app(SpamProtectionService::class);
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = $this->data['email'] ?? '';
        $username = $this->data['username'] ?? '';

        $passed = $this->spamService->validateRegistration(
            request(),
            $email,
            $username
        );

        if (!$passed) {
            $fail($this->spamService->getBlockReason() ?? 'Registration blocked due to suspicious activity.');
        }
    }
}
