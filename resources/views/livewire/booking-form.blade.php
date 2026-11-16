<div class="booking-form">
  <ul
    class="nav nav-tabs"
    role="tablist"
  >
    @foreach ($this->categories as $tab)
      <li
        class="nav-item"
        role="presentation"
        wire:key="booking-tab-{{ $tab['slug'] }}"
      >
        <button
          @class(['nav-link', 'active' => $tab['slug'] === $this->category])
          id="booking-{{ $tab['slug'] }}-tab"
          type="button"
          role="tab"
          aria-controls="booking-{{ $tab['slug'] }}"
          aria-selected="{{ $tab['slug'] === $this->category ? 'true' : 'false' }}"
          wire:click="selectCategory('{{ $tab['slug'] }}')"
        >
          @if ($tab['icon'])
            @svg($tab['icon'], ['height' => '1em'])
          @elseif ($tab['icon_url'])
            <img
              src="{{ $tab['icon_url'] }}"
              alt=""
              height="16"
            >
          @endif
          <span>{{ $tab['name'] }}</span>
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content bg-white">
    @foreach ($this->categories as $tab)
      <div
        @class(['tab-pane', 'fade', 'show active' => $tab['slug'] === $this->category])
        id="booking-{{ $tab['slug'] }}"
        role="tabpanel"
        aria-labelledby="booking-{{ $tab['slug'] }}-tab"
        wire:key="booking-pane-{{ $tab['slug'] }}"
      >
          @if ($tab['slug'] === $this->category)
            <form
              wire:submit="submit"
              novalidate
            >
              <div class="row">
                <div class="col-lg-3">
                  <label
                    class="form-label"
                    for="booking-vehicle"
                  >{{ __('Vehicle type', 'otomaties-carman-client') }}</label>
                  <select
                    class="form-select @error('vehicle') is-invalid @enderror"
                    id="booking-vehicle"
                    wire:model="vehicle"
                  >
                    <option value="">{{ __('Choose a vehicle', 'otomaties-carman-client') }}</option>
                    @foreach ($this->vehicles as $option)
                      <option
                        value="{{ $option['id'] }}"
                        wire:key="booking-vehicle-{{ $option['id'] }}"
                      >{{ $option['name'] }}</option>
                    @endforeach
                  </select>
                  @error('vehicle')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                @include('carman-client::partials.rental-period', [
                    'idPrefix' => 'booking',
                    'columnClass' => 'col-lg-3',
                    'shortleaseUrl' => $shortleaseUrl,
                ])
              </div>

              <button
                class="btn btn-primary w-100 mt-3"
                type="submit"
              >
                <span
                  wire:loading.remove
                  wire:target="submit"
                >{{ __('Calculate price', 'otomaties-carman-client') }}</span>
                <span
                  wire:loading
                  wire:target="submit"
                >
                  <span
                    class="spinner-border spinner-border-sm"
                    aria-hidden="true"
                  ></span>
                  <span class="visually-hidden">{{ __('Loading...', 'otomaties-carman-client') }}</span>
                </span>
              </button>
            </form>
          @endif
      </div>
    @endforeach
  </div>
</div>
