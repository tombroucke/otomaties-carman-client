<?php

namespace Otomaties\CarmanClient\Livewire;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Otomaties\CarmanClient\Api\Client;
use Otomaties\CarmanClient\Booking\BookingCookie;
use Otomaties\CarmanClient\Facades\Rental;
use Otomaties\CarmanClient\Livewire\Concerns\HasRentalPeriod;
use Otomaties\CarmanClient\Pricing\Estimate;
use Otomaties\CarmanClient\Pricing\PriceTable;
use Throwable;

class ReservationForm extends Component
{
    use HasRentalPeriod;

    #[Locked]
    public string $vehicle = '';

    /**
     * Quantity per accessory, keyed by Carman ID
     *
     * @var array<int, int>
     */
    public array $accessories = [];

    public string $insurance = '';

    public string $discountCode = '';

    /**
     * The discount code as validated by Carman, with its percentage
     *
     * @var array{code: string, discount_percentage: float}|null
     */
    #[Locked]
    public ?array $discount = null;

    public bool $isBusiness = false;

    public string $companyName = '';

    public string $vatNumber = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $phone = '';

    public string $email = '';

    public string $street = '';

    public string $postalCode = '';

    public string $city = '';

    public string $remarks = '';

    public bool $acceptTerms = false;

    public string $shortleaseUrl = '';

    public function mount(string $vehicle, string $shortleaseUrl = ''): void
    {
        if (! Rental::vehicle($vehicle)) {
            throw new InvalidArgumentException("Unknown vehicle [{$vehicle}]");
        }

        $this->vehicle = $vehicle;
        $this->shortleaseUrl = $shortleaseUrl;
        $this->fill(Arr::except(BookingCookie::get(), 'vehicle'));
        $this->insurance = (string) ($this->insurances->keys()->first() ?? '');
    }

    /**
     * Remember the period and locations for the other forms
     */
    public function updated(string $property): void
    {
        if (in_array($property, BookingCookie::FIELDS, true)) {
            BookingCookie::put($this->only(BookingCookie::FIELDS));
        }
    }

    /**
     * A changed code has to be applied again
     */
    public function updatedDiscountCode(): void
    {
        if (trim($this->discountCode) !== ($this->discount['code'] ?? null)) {
            $this->discount = null;
        }
    }

    public function applyDiscountCode(): void
    {
        $this->discount = null;
        $this->resetValidation('discountCode');

        $code = trim($this->discountCode);
        if ($code === '') {
            return;
        }

        // Prevent guessing codes
        $key = 'carman-discount-code:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('discountCode', __('Too many attempts, please try again in a minute.', 'otomaties-carman-client'));

            return;
        }
        RateLimiter::hit($key);

        try {
            $this->discount = app(Client::class)->validateDiscountCode($code);
        } catch (Throwable $e) {
            report($e);
            $this->addError('discountCode', __('The discount code could not be checked, please try again later.', 'otomaties-carman-client'));

            return;
        }

        if (! $this->discount) {
            $this->addError('discountCode', __('This discount code is not valid.', 'otomaties-carman-client'));
        }
    }

    public function addAccessory(int $id): void
    {
        $accessory = $this->availableAccessories->get($id);
        if (! $accessory) {
            return;
        }

        $this->accessories[$id] = min(($this->accessories[$id] ?? 0) + 1, $accessory['max_amount']);
    }

    public function removeAccessory(int $id): void
    {
        $quantity = ($this->accessories[$id] ?? 0) - 1;

        if ($quantity > 0) {
            $this->accessories[$id] = $quantity;
        } else {
            unset($this->accessories[$id]);
        }
    }

    public function submit(): void
    {
        $this->validate();

        if (! $this->validateRentalPeriodOrder()) {
            return;
        }

        // TODO: send the reservation to Carman
    }

    protected function rules(): array
    {
        return [
            ...$this->rentalPeriodRules(),
            'accessories' => ['array'],
            'accessories.*' => ['integer', 'min:1'],
            'insurance' => $this->insurances->isEmpty()
                ? ['nullable']
                : ['required', Rule::in($this->insurances->keys()->map(fn ($id) => (string) $id))],
            'discountCode' => ['nullable', 'string', 'max:50', function (string $attribute, mixed $value, Closure $fail) {
                if (! $this->discount) {
                    $fail(__('This discount code is not valid.', 'otomaties-carman-client'));
                }
            }],
            'companyName' => ['exclude_unless:isBusiness,true', 'required', 'string', 'max:255'],
            'vatNumber' => ['exclude_unless:isBusiness,true', 'required', 'string', 'max:50'],
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'postalCode' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'acceptTerms' => ['accepted'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            ...$this->rentalPeriodValidationAttributes(),
            'insurance' => __('insurance', 'otomaties-carman-client'),
            'discountCode' => __('discount code', 'otomaties-carman-client'),
            'companyName' => __('company name', 'otomaties-carman-client'),
            'vatNumber' => __('VAT number', 'otomaties-carman-client'),
            'firstName' => __('first name', 'otomaties-carman-client'),
            'lastName' => __('last name', 'otomaties-carman-client'),
            'phone' => __('phone number', 'otomaties-carman-client'),
            'email' => __('email address', 'otomaties-carman-client'),
            'street' => __('street and number', 'otomaties-carman-client'),
            'postalCode' => __('postal code', 'otomaties-carman-client'),
            'city' => __('city', 'otomaties-carman-client'),
            'remarks' => __('remarks', 'otomaties-carman-client'),
            'acceptTerms' => __('terms and conditions', 'otomaties-carman-client'),
        ];
    }

    #[Computed]
    public function car(): array
    {
        return Rental::vehicle($this->vehicle);
    }

    /**
     * Accessories that can be rented with this vehicle, recommended ones first
     */
    #[Computed]
    public function availableAccessories(): Collection
    {
        return Rental::accessories()
            ->only($this->car['accessories'])
            ->sortByDesc('is_recommended');
    }

    /**
     * Insurances for this vehicle, cheapest first
     */
    #[Computed]
    public function insurances(): Collection
    {
        if ($this->car['hide_insurance']) {
            return collect();
        }

        return Rental::insurances()
            ->sortBy(fn (array $insurance) => $insurance['is_free'] ? 0 : PriceTable::price($insurance['prices'], 1));
    }

    /**
     * The estimated cost, null as long as the rental period is incomplete
     */
    #[Computed]
    public function estimate(): ?Estimate
    {
        $days = $this->rentalDays();
        if (! $days) {
            return null;
        }

        return new Estimate(
            vehicle: $this->car,
            days: $days,
            accessories: collect($this->accessories)
                ->filter(fn (int $quantity, int $id) => $this->availableAccessories->has($id))
                ->map(fn (int $quantity, int $id) => [
                    'accessory' => $this->availableAccessories->get($id),
                    'quantity' => $quantity,
                ])
                ->all(),
            insurance: $this->insurances->get((int) $this->insurance),
            discountPercentage: $this->discount['discount_percentage'] ?? 0,
        );
    }

    public function render()
    {
        return view('carman-client::livewire.reservation-form');
    }
}
