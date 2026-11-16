<?php

namespace Otomaties\CarmanClient\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Otomaties\CarmanClient\Booking\BookingCookie;
use Otomaties\CarmanClient\Facades\Rental;
use Otomaties\CarmanClient\Livewire\Concerns\HasRentalPeriod;

class BookingForm extends Component
{
    use HasRentalPeriod;

    #[Url]
    public string $category = '';

    public string $vehicle = '';

    public string $shortleaseUrl = '';

    #[Locked]
    public string $bookingPageUrl = '';

    #[Locked]
    public bool $onBookingPage = false;

    public function mount(string $category = '', string $shortleaseUrl = ''): void
    {
        $category = $this->category ?: $category;
        $this->category = Rental::category($category) ? $category : Rental::categories()->value('slug');
        $this->shortleaseUrl = $shortleaseUrl;

        $bookingPage = get_field('booking_page', 'option');
        if ($bookingPage) {
            $this->bookingPageUrl = get_permalink($bookingPage);
            $this->onBookingPage = get_queried_object_id() === $bookingPage->ID;
        }

        $query = (array) request()->query('booking', []);
        $this->fill($query ? BookingCookie::sanitize($query) : BookingCookie::get());
    }

    public function selectCategory(string $category): void
    {
        if (! Rental::category($category) || $category === $this->category) {
            return;
        }

        $this->category = $category;
        $this->reset('vehicle');
        $this->resetValidation();
        $this->dispatch('category-changed', category: $category);
    }

    /**
     * Update the vehicle overview on the booking page, or redirect to the booking page
     */
    public function submit(): void
    {
        $this->validate();

        if (! $this->validateRentalPeriodOrder()) {
            return;
        }

        $booking = $this->only(BookingCookie::FIELDS);
        BookingCookie::put($booking);

        if ($this->onBookingPage) {
            $this->dispatch('booking-updated', category: $this->category, booking: $booking);

            return;
        }

        $this->redirect(add_query_arg(urlencode_deep([
            'category' => $this->category,
            'booking' => array_filter($booking),
        ]), $this->bookingPageUrl));
    }

    protected function rules(): array
    {
        return [
            'vehicle' => ['required', Rule::in($this->vehicles()->pluck('id'))],
            ...$this->rentalPeriodRules(),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'vehicle' => __('vehicle type', 'otomaties-carman-client'),
            ...$this->rentalPeriodValidationAttributes(),
        ];
    }

    #[Computed]
    public function categories(): Collection
    {
        return Rental::categories();
    }

    #[Computed]
    public function vehicles(): Collection
    {
        return Rental::vehicles($this->category);
    }

    public function render()
    {
        return view('carman-client::livewire.booking-form');
    }
}
