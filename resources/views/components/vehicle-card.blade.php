@props(['vehicle'])

@php($price = $vehicle['price_per_day'])

<div
  {{ $attributes->class(['card vehicle-card h-100 bg-light d-flex flex-column justify-content-between position-relative']) }}
>
  <div>
    <div class="card-image">
      <div class="position-relative">
        <div class="ratio ratio-16x9 bg-light">
          @if ($vehicle['image'])
            <img
              class="card-img-top object-fit-cover"
              src="{{ $vehicle['image'] }}"
              alt="{{ $vehicle['name'] }}"
              loading="lazy"
            >
          @else
            <div class="d-flex align-items-center justify-content-center text-body-tertiary">
              @svg('fas-van-shuttle', ['height' => '3em'])
            </div>
          @endif
        </div>
        @if ($price)
          <span class="badge bg-primary rounded-pill position-absolute end-0 top-0 m-2">
            {{-- translators: %s: price per day --}}
            {{ sprintf(__('From %s/day', 'otomaties-carman-client'), '€' . number_format_i18n($price, fmod($price, 1) ? 2 : 0)) }}
          </span>
        @endif
      </div>
    </div>

    <div class="card-body p-3">
      <h3>
        <a
          class="stretched-link text-reset text-decoration-none"
          href="{{ $vehicle['url'] }}"
        >{{ $vehicle['name'] }}</a>
      </h3>

      @if ($vehicle['subtitle'])
        <p class="small fw-semibold text-primary">{{ $vehicle['subtitle'] }}</p>
      @endif

      <ul class="list-inline small mb-0">
        @foreach (Otomaties\CarmanClient\Facades\Rental::options()->only($vehicle['options']) as $option)
          <li class="list-inline-item">
            @if ($option['icon_url'])
              <img
                src="{{ $option['icon_url'] }}"
                alt=""
                height="16"
                loading="lazy"
                style="height: 1em;"
              >
            @endif
            {{ $option['name'] }}
          </li>
        @endforeach
      </ul>
    </div>
  </div>

  <div class="card-footer">
    {{ $slot }}
  </div>
</div>
