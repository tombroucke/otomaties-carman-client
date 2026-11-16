{{--
  Pickup and return location and moment fields, for components using HasRentalPeriod

  @param string $idPrefix
  @param string $columnClass
  @param string $shortleaseUrl
  @param bool $live Update the component on every change, defaults to false
--}}

@php($wireModel = ($live ?? false) ? 'wire:model.live' : 'wire:model')

<div class="{{ $columnClass }}">
  <label
    class="form-label"
    for="{{ $idPrefix }}-pickup-location"
  >{{ __('Pickup location', 'otomaties-carman-client') }}</label>
  <select
    class="form-select @error('pickupLocation') is-invalid @enderror"
    id="{{ $idPrefix }}-pickup-location"
    {{ $wireModel }}="pickupLocation"
  >
    <option value="">{{ __('Choose your pickup location', 'otomaties-carman-client') }}</option>
    @foreach ($this->locations as $location)
      <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
    @endforeach
  </select>
  @error('pickupLocation')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror

  <div class="form-check small mt-1">
    <input
      class="form-check-input"
      id="{{ $idPrefix }}-different-return-location"
      type="checkbox"
      wire:model.live="differentReturnLocation"
    >
    <label
      class="form-check-label"
      for="{{ $idPrefix }}-different-return-location"
    >{{ __('Choose a different return location', 'otomaties-carman-client') }}</label>
  </div>

  @if ($differentReturnLocation)
    <label
      class="visually-hidden"
      for="{{ $idPrefix }}-return-location"
    >{{ __('Return location', 'otomaties-carman-client') }}</label>
    <select
      class="form-select @error('returnLocation') is-invalid @enderror mt-2"
      id="{{ $idPrefix }}-return-location"
      {{ $wireModel }}="returnLocation"
    >
      <option value="">{{ __('Choose your return location', 'otomaties-carman-client') }}
      </option>
      @foreach ($this->locations as $location)
        <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
      @endforeach
    </select>
    @error('returnLocation')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  @endif
</div>

@foreach (['pickup' => __('Pickup', 'otomaties-carman-client'), 'return' => __('Return', 'otomaties-carman-client')] as $moment => $label)
  <div class="{{ $columnClass }}">
    <div class="row g-2">
      <div class="col-6">
        <label
          class="form-label"
          for="{{ $idPrefix }}-{{ $moment }}-date"
        >
          {{ $moment === 'pickup' ? __('Pickup date', 'otomaties-carman-client') : __('Return date', 'otomaties-carman-client') }}
        </label>
        <input
          class="form-control @error($moment . 'Date') is-invalid @enderror"
          id="{{ $idPrefix }}-{{ $moment }}-date"
          type="date"
          min="{{ wp_date('Y-m-d') }}"
          {{ $wireModel }}="{{ $moment }}Date"
        >
        @error($moment . 'Date')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-6">
        <label
          class="form-label"
          for="{{ $idPrefix }}-{{ $moment }}-hour"
        >
          {{ $moment === 'pickup' ? __('Pickup time', 'otomaties-carman-client') : __('Return time', 'otomaties-carman-client') }}
        </label>
        <div class="input-group">
          <select
            class="form-select @error($moment . 'Hour') is-invalid @enderror"
            id="{{ $idPrefix }}-{{ $moment }}-hour"
            aria-label="{{ __('Hour', 'otomaties-carman-client') }}"
            {{ $wireModel }}="{{ $moment }}Hour"
          >
            <option value=""></option>
            @foreach ($this->hours as $hour)
              <option value="{{ $hour }}">{{ $hour }}</option>
            @endforeach
          </select>
          <select
            class="form-select @error($moment . 'Minute') is-invalid @enderror"
            aria-label="{{ __('Minutes', 'otomaties-carman-client') }}"
            {{ $wireModel }}="{{ $moment }}Minute"
          >
            <option value=""></option>
            @foreach ($this->minutes as $minute)
              <option value="{{ $minute }}">{{ $minute }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    @if ($moment === 'pickup')
      <p class="form-text mb-0">
        {{ __('Evening pickup possible (on weekdays & subject to availability)', 'otomaties-carman-client') }}
      </p>
    @elseif ($shortleaseUrl)
      <p class="form-text mb-0 text-end">
        <a
          href="{{ $shortleaseUrl }}">{{ __('Renting for a longer period? Shortlease', 'otomaties-carman-client') }}</a>
      </p>
    @endif
  </div>
@endforeach
