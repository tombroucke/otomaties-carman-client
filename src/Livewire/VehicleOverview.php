<?php

namespace Otomaties\CarmanClient\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Otomaties\CarmanClient\Facades\Rental;

class VehicleOverview extends Component
{
    #[Url]
    public string $category = '';

    #[Url]
    public array $options = [];

    /**
     * Booking details from the booking form
     */
    #[Url]
    public array $booking = [];

    public function mount(string $category = ''): void
    {
        $category = $this->category ?: $category;
        $this->category = Rental::category($category) ? $category : Rental::categories()->value('slug');
    }

    #[On('category-changed')]
    public function selectCategory(string $category): void
    {
        if (! Rental::category($category)) {
            return;
        }

        $this->category = $category;
        $this->resetFilters();
    }

    #[On('booking-updated')]
    public function updateBooking(string $category, array $booking): void
    {
        $this->booking = $booking;

        if ($category !== $this->category) {
            $this->selectCategory($category);
        }
    }

    public function resetFilters(): void
    {
        $this->reset('options');
    }

    #[Computed]
    public function heading(): string
    {
        /* translators: %s: singular category name, e.g. "bestelwagen" */
        return sprintf(__('Rent your %s today', 'otomaties-carman-client'), Rental::category($this->category)['singular']);
    }

    /**
     * Human readable booking details, e.g. "Pickup in Kortrijk on 16/11/2026 09:00, return in ..."
     */
    #[Computed]
    public function bookingSummary(): ?string
    {
        $booking = $this->booking;
        if (empty($booking['pickupLocation']) || empty($booking['pickupDate']) || empty($booking['returnDate'])) {
            return null;
        }

        $locations = Rental::locations()->pluck('name', 'id');
        $returnLocation = ! empty($booking['differentReturnLocation']) ? ($booking['returnLocation'] ?? '') : $booking['pickupLocation'];
        $format = get_option('date_format').' '.get_option('time_format');
        $moment = fn (string $type) => wp_date($format, Carbon::parse(sprintf(
            '%s %s:%s',
            $booking[$type.'Date'],
            $booking[$type.'Hour'] ?? '00',
            $booking[$type.'Minute'] ?? '00',
        ), wp_timezone())->getTimestamp());

        return sprintf(
            /* translators: 1: pickup location, 2: pickup date, 3: return location, 4: return date */
            __('Pickup in %1$s on %2$s, return in %3$s on %4$s', 'otomaties-carman-client'),
            $locations->get($booking['pickupLocation'], $booking['pickupLocation']),
            $moment('pickup'),
            $locations->get($returnLocation, $returnLocation),
            $moment('return'),
        );
    }

    #[Computed]
    public function vehicles(): Collection
    {
        return Rental::vehicles($this->category)
            ->when($this->options, fn ($vehicles) => $vehicles->filter(
                fn ($vehicle) => empty(array_diff($this->options, $vehicle['options']))
            ));
    }

    /**
     * Filter groups, limited to the options available in the current category
     */
    #[Computed]
    public function filters(): Collection
    {
        $availableOptions = Rental::vehicles($this->category)->pluck('options')->flatten()->unique();

        return collect([
            'options' => [
                'label' => __('Options', 'otomaties-carman-client'),
                'options' => Rental::options()
                    ->only($availableOptions)
                    ->map(fn (array $option) => $option['name']),
            ],
        ])->filter(fn ($filter) => $filter['options']->isNotEmpty());
    }

    public function render()
    {
        return view('carman-client::livewire.vehicle-overview');
    }
}
