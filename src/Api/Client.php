<?php

namespace Otomaties\CarmanClient\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Client
{
    public function __construct(
        private ?string $apiUrl,
        private ?string $apiToken,
        private ?string $company,
    ) {}

    /**
     * Fetch all records of a company resource, e.g. "cars" or "car-categories"
     */
    public function all(string $resource): array
    {
        return $this->request()
            ->retry(2, 500)
            ->get($resource)
            ->throw()
            ->json('data', []);
    }

    /**
     * Check a discount code entered by a customer
     *
     * @return array{code: string, discount_percentage: float}|null Null when the code can not be used
     */
    public function validateDiscountCode(string $code): ?array
    {
        $response = $this->request()
            ->timeout(10)
            ->post('discount-codes/validate', ['code' => $code]);

        if ($response->unprocessableEntity()) {
            return null;
        }

        return $response->throw()->json('data');
    }

    public function host(): ?string
    {
        return parse_url((string) $this->apiUrl, PHP_URL_HOST) ?: null;
    }

    private function request(): PendingRequest
    {
        if (! $this->apiUrl || ! $this->apiToken || ! $this->company) {
            throw new RuntimeException('Carman API is not configured. Set CARMAN_API_URL, CARMAN_API_TOKEN and CARMAN_COMPANY in your .env file.');
        }

        return Http::baseUrl(rtrim($this->apiUrl, '/').'/companies/'.rawurlencode($this->company).'/')
            ->withToken($this->apiToken)
            ->acceptJson()
            ->timeout(30);
    }
}
