<?php

namespace Otomaties\CarmanClient\Livewire\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Otomaties\CarmanClient\Facades\Rental;
use Throwable;

/**
 * Pickup and return locations and moments, shared by the booking and reservation form
 */
trait HasRentalPeriod
{
    public string $pickupLocation = '';

    public bool $differentReturnLocation = false;

    public string $returnLocation = '';

    public string $pickupDate = '';

    public string $pickupHour = '';

    public string $pickupMinute = '';

    public string $returnDate = '';

    public string $returnHour = '';

    public string $returnMinute = '';

    protected function rentalPeriodRules(): array
    {
        $locations = Rental::locations()->pluck('id');

        return [
            'pickupLocation' => ['required', Rule::in($locations)],
            'returnLocation' => ['exclude_unless:differentReturnLocation,true', 'required', Rule::in($locations)],
            'pickupDate' => ['required', 'date', 'after_or_equal:today'],
            'pickupHour' => ['required', Rule::in($this->hours())],
            'pickupMinute' => ['required', Rule::in($this->minutes())],
            'returnDate' => ['required', 'date', 'after_or_equal:pickupDate'],
            'returnHour' => ['required', Rule::in($this->hours())],
            'returnMinute' => ['required', Rule::in($this->minutes())],
        ];
    }

    protected function rentalPeriodValidationAttributes(): array
    {
        return [
            'pickupLocation' => __('pickup location', 'otomaties-carman-client'),
            'returnLocation' => __('return location', 'otomaties-carman-client'),
            'pickupDate' => __('pickup date', 'otomaties-carman-client'),
            'pickupHour' => __('pickup hour', 'otomaties-carman-client'),
            'pickupMinute' => __('pickup minute', 'otomaties-carman-client'),
            'returnDate' => __('return date', 'otomaties-carman-client'),
            'returnHour' => __('return hour', 'otomaties-carman-client'),
            'returnMinute' => __('return minute', 'otomaties-carman-client'),
        ];
    }

    /**
     * Add a validation error when the return moment isn't after the pickup moment
     */
    protected function validateRentalPeriodOrder(): bool
    {
        if ($this->returnMoment()?->gt($this->pickupMoment())) {
            return true;
        }

        $this->addError('returnDate', __('The return date must be after the pickup date.', 'otomaties-carman-client'));

        return false;
    }

    /**
     * Number of started days between pickup and return, null when the period is incomplete or invalid
     */
    protected function rentalDays(): ?int
    {
        $pickup = $this->pickupMoment();
        $return = $this->returnMoment();

        if (! $pickup || ! $return || $return->lte($pickup)) {
            return null;
        }

        return max(1, (int) ceil($pickup->diffInMinutes($return) / MINUTE_IN_SECONDS / 24));
    }

    protected function pickupMoment(): ?Carbon
    {
        return $this->moment($this->pickupDate, $this->pickupHour, $this->pickupMinute);
    }

    protected function returnMoment(): ?Carbon
    {
        return $this->moment($this->returnDate, $this->returnHour, $this->returnMinute);
    }

    private function moment(string $date, string $hour, string $minute): ?Carbon
    {
        if ($date === '' || $hour === '' || $minute === '') {
            return null;
        }

        try {
            return Carbon::parse("{$date} {$hour}:{$minute}", wp_timezone());
        } catch (Throwable) {
            return null;
        }
    }

    #[Computed]
    public function locations(): Collection
    {
        return Rental::locations();
    }

    #[Computed]
    public function hours(): array
    {
        return array_map(fn ($hour) => sprintf('%02d', $hour), range(7, 19));
    }

    #[Computed]
    public function minutes(): array
    {
        return ['00', '15', '30', '45'];
    }
}
