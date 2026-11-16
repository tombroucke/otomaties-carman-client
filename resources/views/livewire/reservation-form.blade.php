@use('Otomaties\CarmanClient\Pricing\PriceTable')

<div class="reservation-form">
  <form
    wire:submit="submit"
    novalidate
  >
    <section class="border border-primary rounded-5 p-4 p-lg-5 mb-5">
      <h2 class="text-primary mb-4">
        {{-- translators: %s: vehicle name --}}
        {{ sprintf(__('Book this %s', 'otomaties-carman-client'), $this->car['name']) }}
      </h2>

      <div class="row g-3">
        @include('carman-client::partials.rental-period', [
            'idPrefix' => 'reservation',
            'columnClass' => 'col-lg-4',
            'shortleaseUrl' => $shortleaseUrl,
            'live' => true,
        ])
      </div>
    </section>

    <div class="row g-5">
      <div class="col-lg-7">
        @if ($this->availableAccessories->isNotEmpty())
          <section class="mb-5">
            <h2 class="h4 mb-4">{{ __('Other customers also rented these accessories.', 'otomaties-carman-client') }}</h2>

            <div class="row row-cols-2 row-cols-md-4 g-4">
              @foreach ($this->availableAccessories as $accessory)
                @php($quantity = $accessories[$accessory['id']] ?? 0)
                <div
                  class="col text-center"
                  wire:key="accessory-{{ $accessory['id'] }}"
                >
                  <div class="ratio ratio-1x1 mb-2">
                    @if ($accessory['image'])
                      <img
                        class="object-fit-contain"
                        src="{{ $accessory['image'] }}"
                        alt=""
                        loading="lazy"
                      >
                    @else
                      <div class="d-flex align-items-center justify-content-center text-body-tertiary">
                        @svg('fas-box', ['height' => '2em'])
                      </div>
                    @endif
                  </div>

                  <h3 class="h6 mb-1">{{ $accessory['name'] }}</h3>
                  <p class="small text-primary mb-2">
                    @if ($accessory['is_free'])
                      {{ __('Free', 'otomaties-carman-client') }}
                    @else
                      {{-- translators: %s: price per day --}}
                      {{ sprintf(__('From %s/day', 'otomaties-carman-client'), PriceTable::format(PriceTable::price($accessory['prices'], 1))) }}
                    @endif
                  </p>

                  @if ($quantity)
                    <div class="input-group input-group-sm justify-content-center">
                      <button
                        class="btn btn-outline-primary"
                        type="button"
                        aria-label="{{ sprintf(__('Remove %s', 'otomaties-carman-client'), $accessory['name']) }}"
                        wire:click="removeAccessory({{ $accessory['id'] }})"
                      >@svg('fas-minus', ['height' => '1em'])</button>
                      <span class="input-group-text">{{ $quantity }}</span>
                      <button
                        class="btn btn-outline-primary"
                        type="button"
                        aria-label="{{ sprintf(__('Add %s', 'otomaties-carman-client'), $accessory['name']) }}"
                        wire:click="addAccessory({{ $accessory['id'] }})"
                        @disabled($quantity >= $accessory['max_amount'])
                      >@svg('fas-plus', ['height' => '1em'])</button>
                    </div>
                  @else
                    <button
                      class="btn btn-sm btn-outline-primary"
                      type="button"
                      wire:click="addAccessory({{ $accessory['id'] }})"
                    >{{ __('Add', 'otomaties-carman-client') }}</button>
                  @endif
                </div>
              @endforeach
            </div>
          </section>
        @endif

        @if ($this->insurances->count() > 1)
          <section class="mb-5">
            <h2 class="h4 mb-3">{{ __('Insurance', 'otomaties-carman-client') }}</h2>

            @foreach ($this->insurances as $option)
              <div
                class="form-check mb-2"
                wire:key="insurance-{{ $option['id'] }}"
              >
                <input
                  class="form-check-input @error('insurance') is-invalid @enderror"
                  id="reservation-insurance-{{ $option['id'] }}"
                  type="radio"
                  value="{{ $option['id'] }}"
                  wire:model.live="insurance"
                >
                <label
                  class="form-check-label"
                  for="reservation-insurance-{{ $option['id'] }}"
                >
                  {{ $option['name'] }}
                  @if ($option['subtitle'])
                    <span class="d-block small text-body-secondary">{{ $option['subtitle'] }}</span>
                  @endif
                </label>
              </div>
            @endforeach
            @error('insurance')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </section>
        @endif

        <section>
          <h2 class="mb-3">{{ __('Contact details', 'otomaties-carman-client') }}</h2>

          <div class="form-check mb-3">
            <input
              class="form-check-input"
              id="reservation-is-business"
              type="checkbox"
              wire:model.live="isBusiness"
            >
            <label
              class="form-check-label"
              for="reservation-is-business"
            >{{ __('Professional use', 'otomaties-carman-client') }}</label>
          </div>

          <div class="row g-3">
            @php(
                $fields = array_filter([
                    'companyName' => $isBusiness ? ['label' => __('Company name', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'organization'] : null,
                    'vatNumber' => $isBusiness ? ['label' => __('VAT number', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'off'] : null,
                    'firstName' => ['label' => __('First name', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'given-name'],
                    'lastName' => ['label' => __('Last name', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'family-name'],
                    'phone' => ['label' => __('Phone number', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'tel', 'type' => 'tel'],
                    'email' => ['label' => __('Email address', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'email', 'type' => 'email'],
                    'street' => ['label' => __('Street and number', 'otomaties-carman-client'), 'class' => 'col-md-6', 'autocomplete' => 'street-address'],
                    'city' => ['label' => __('City', 'otomaties-carman-client'), 'class' => 'col-md-4', 'autocomplete' => 'address-level2'],
                    'postalCode' => ['label' => __('Postal code', 'otomaties-carman-client'), 'class' => 'col-md-2', 'autocomplete' => 'postal-code'],
                ])
            )
            @foreach ($fields as $name => $field)
              <div
                class="{{ $field['class'] }}"
                wire:key="contact-{{ $name }}"
              >
                <label
                  class="visually-hidden"
                  for="reservation-{{ $name }}"
                >{{ $field['label'] }}</label>
                <input
                  class="form-control @error($name) is-invalid @enderror"
                  id="reservation-{{ $name }}"
                  type="{{ $field['type'] ?? 'text' }}"
                  placeholder="{{ $field['label'] }}"
                  autocomplete="{{ $field['autocomplete'] }}"
                  wire:model.blur="{{ $name }}"
                >
                @error($name)
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            @endforeach

            <div class="col-12">
              <label
                class="visually-hidden"
                for="reservation-remarks"
              >{{ __('Remarks', 'otomaties-carman-client') }}</label>
              <textarea
                class="form-control @error('remarks') is-invalid @enderror"
                id="reservation-remarks"
                rows="4"
                placeholder="{{ __('Remarks (optional)', 'otomaties-carman-client') }}"
                wire:model.blur="remarks"
              ></textarea>
              @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <div class="form-check">
                <input
                  class="form-check-input @error('acceptTerms') is-invalid @enderror"
                  id="reservation-accept-terms"
                  type="checkbox"
                  wire:model="acceptTerms"
                >
                <label
                  class="form-check-label small"
                  for="reservation-accept-terms"
                >{{ __('I agree to the terms and conditions', 'otomaties-carman-client') }}</label>
                @error('acceptTerms')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-12">
              <button
                class="btn btn-primary"
                type="submit"
              >
                <span
                  wire:loading.remove
                  wire:target="submit"
                >{{ __('Book now', 'otomaties-carman-client') }}</span>
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
            </div>
          </div>
        </section>
      </div>

      <div class="col-lg-5">
        <aside
          class="card bg-light border-0 rounded-5 overflow-hidden sticky-lg-top"
          style="top: 1rem"
          aria-live="polite"
        >
          @if ($this->car['image'])
            <img
              class="card-img-top object-fit-cover"
              src="{{ $this->car['image'] }}"
              alt=""
              style="aspect-ratio: 16 / 9"
            >
          @endif

          <div class="card-body p-4">
            @php($estimate = $this->estimate)
            @if ($estimate)
              <h2 class="h4 text-primary mb-3">
                {{-- translators: %d: number of days --}}
                {{ sprintf(_n('Estimated cost - %d day', 'Estimated cost - %d days', $estimate->days(), 'otomaties-carman-client'), $estimate->days()) }}
              </h2>

              <div class="d-flex justify-content-between">
                <span>{{ __('Price', 'otomaties-carman-client') }} <small>{{ __('(incl. VAT)', 'otomaties-carman-client') }}</small></span>
                <span>{{ PriceTable::format($estimate->rentalPrice()) }}</span>
              </div>

              @if ($estimate->insurance() || $estimate->accessories()->isNotEmpty())
                <hr class="border-primary">
                @if ($estimate->insurance())
                  <div class="d-flex justify-content-between">
                    <span>{{ __('Insurance', 'otomaties-carman-client') }}</span>
                    <span>{{ PriceTable::format($estimate->insurance()['total']) }}</span>
                  </div>
                @endif
                @foreach ($estimate->accessories() as $line)
                  <div class="d-flex justify-content-between">
                    <span>{{ $line['quantity'] }}x {{ $line['name'] }}</span>
                    <span>{{ PriceTable::format($line['total']) }}</span>
                  </div>
                @endforeach
              @endif

              @if ($estimate->discount())
                <hr class="border-primary">
                <div class="d-flex justify-content-between text-success">
                  {{-- translators: 1: discount code, 2: percentage --}}
                  <span>{{ sprintf(__('Discount %1$s (-%2$s%%)', 'otomaties-carman-client'), $this->discount['code'], number_format_i18n($estimate->discountPercentage(), fmod($estimate->discountPercentage(), 1) ? 2 : 0)) }}</span>
                  <span>- {{ PriceTable::format($estimate->discount()) }}</span>
                </div>
              @endif

              <hr class="border-primary">
              <div class="d-flex justify-content-between fw-bold fs-5">
                <span>{{ __('Total', 'otomaties-carman-client') }} <small class="fw-normal fs-6">{{ __('(incl. VAT)', 'otomaties-carman-client') }}</small></span>
                <span>{{ PriceTable::format($estimate->total()) }}</span>
              </div>

              <div class="my-3">
                <label
                  class="visually-hidden"
                  for="reservation-discount-code"
                >{{ __('Discount code', 'otomaties-carman-client') }}</label>
                <div @class(['input-group', 'has-validation' => $errors->has('discountCode')])>
                  <input
                    @class([
                        'form-control border-primary',
                        'is-invalid' => $errors->has('discountCode'),
                        'is-valid' => $this->discount,
                    ])
                    id="reservation-discount-code"
                    type="text"
                    placeholder="{{ __('Enter discount code', 'otomaties-carman-client') }}"
                    wire:model="discountCode"
                    wire:keydown.enter.prevent="applyDiscountCode"
                  >
                  <button
                    class="btn btn-outline-primary"
                    type="button"
                    wire:click="applyDiscountCode"
                    wire:loading.attr="disabled"
                    wire:target="applyDiscountCode"
                  >{{ __('Apply', 'otomaties-carman-client') }}</button>
                  @error('discountCode')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  @if ($this->discount)
                    <div class="valid-feedback">{{ __('Discount code applied.', 'otomaties-carman-client') }}</div>
                  @endif
                </div>
              </div>

              <dl class="row small mb-0">
                @if ($estimate->kmIncluded())
                  <dt class="col-8 fw-normal">{{ __('Free km', 'otomaties-carman-client') }}</dt>
                  <dd class="col-4 text-end mb-0">{{ number_format_i18n($estimate->kmIncluded()) }} km</dd>
                @endif
                @if ($estimate->deposit())
                  <dt class="col-8 fw-normal">{{ __('Deposit', 'otomaties-carman-client') }}</dt>
                  <dd class="col-4 text-end mb-0">{{ PriceTable::format($estimate->deposit()) }}</dd>
                @endif
                @if ($estimate->extraKmPrice())
                  <dt class="col-8 fw-normal">{{ __('Extra km price', 'otomaties-carman-client') }}</dt>
                  <dd class="col-4 text-end mb-0">{{ PriceTable::format($estimate->extraKmPrice()) }}</dd>
                @endif
              </dl>
            @else
              <h2 class="h4 text-primary mb-3">{{ __('Estimated cost', 'otomaties-carman-client') }}</h2>
              <p class="mb-0">{{ __('Choose your pickup and return moment to see the estimated cost.', 'otomaties-carman-client') }}</p>
            @endif
          </div>
        </aside>
      </div>
    </div>
  </form>
</div>
