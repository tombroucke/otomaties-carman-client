<div
  class="vehicle-overview"
  x-on:booking-updated.window="$el.scrollIntoView({ behavior: 'smooth' })"
>
  <h1 class="mb-4">{{ $this->heading }}</h1>

  @if ($this->bookingSummary)
    <p class="lead">{{ $this->bookingSummary }}</p>
  @endif

  <div class="row g-4">
    <aside class="col-lg-3">
      @foreach ($this->filters as $key => $filter)
        <fieldset
          class="@unless ($loop->last) border-bottom @endunless mb-3 pb-3"
          wire:key="filter-{{ $category }}-{{ $key }}"
        >
          <legend class="h6 text-primary">{{ $filter['label'] }}</legend>
          @foreach ($filter['options'] as $value => $label)
            <div
              class="form-check"
              wire:key="filter-{{ $category }}-{{ $key }}-{{ $value }}"
            >
              <input
                class="form-check-input"
                id="filter-{{ $key }}-{{ $value }}"
                type="checkbox"
                value="{{ $value }}"
                wire:model.live="{{ $key }}"
              >
              <label
                class="form-check-label"
                for="filter-{{ $key }}-{{ $value }}"
              >{{ $label }}</label>
            </div>
          @endforeach
        </fieldset>
      @endforeach
    </aside>

    <div class="col-lg-9">
      <div
        class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4"
        wire:loading.class="opacity-50"
      >
        @forelse ($this->vehicles as $vehicle)
          <div
            class="col"
            wire:key="vehicle-{{ $vehicle['id'] }}"
          >
            <x-carman-client::vehicle-card :vehicle="$vehicle">
              <a
                class="btn btn-primary w-100"
                href="{{ $vehicle['url'] }}"
              >
                {{ __('Calculate / Book', 'otomaties-carman-client') }}
              </a>
            </x-carman-client::vehicle-card>
          </div>
        @empty
          <div class="col-12 w-100">
            <div
              class="alert alert-info"
              role="alert"
            >
              {{ __('No vehicles match your filters.', 'otomaties-carman-client') }}
              <button
                class="btn btn-link p-0 align-baseline"
                type="button"
                wire:click="resetFilters"
              >{{ __('Reset filters', 'otomaties-carman-client') }}</button>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
