@extends('layouts.app')

@section('title', 'CamboTrips — Welcome to the Kingdom of Wonder')

@section('content')
  <!-- Hero Section -->
  <div class="hero-dual" style="position: relative; display: block;">
    <img class="hero-img" src="https://www.image2url.com/r2/default/images/1781277939613-fe493f83-edb7-4d45-8044-f914fa804fa7.png" alt="CamboTrips banner" style="width: 100%; height: 100%; object-fit: cover;" />
    <div class="hero-overlay" style="position: absolute; border-radius: var(--radius-lg);">
      <h1 class="hero-title">Welcome to the Kingdom of Wonder</h1>
    </div>
  </div>

  <!-- Top Destinations Section -->
  <div class="section">
    <h2 class="section-title">Top Destination</h2>
    <div class="swiper top-destinations-swiper" id="top-destinations">
      <div class="swiper-wrapper">
        @foreach($topPlaces as $place)
          <div class="swiper-slide">
            <a href="{{ route('place.show', $place->id) }}" class="card place-card">
              <div class="card-img-wrap">
                <img src="{{ $place->image }}" alt="{{ $place->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&q=60'">
                <div class="card-tags">
                  @foreach($place->tags->take(2) as $tag)
                    <span class="tag">{{ $tag->name }}</span>
                  @endforeach
                </div>
              </div>
              <div class="card-body">
                <h3 class="card-title">{{ $place->name }}</h3>
                @if($place->reviews_count > 0)
                  <div class="rating-badge" style="font-size: 0.8rem; font-weight: 600; color: var(--color-accent2); margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                    ⭐ {{ $place->average_rating }} <span style="color: var(--color-muted); font-weight: normal;">({{ $place->reviews_count }} {{ Str::plural('review', $place->reviews_count) }})</span>
                  </div>
                @endif
                <p class="card-desc">{{ $place->tagline }}</p>
              </div>
            </a>
          </div>
        @endforeach
      </div>
      <!-- Swiper Controls -->
      <div class="swiper-pagination" style="margin-top: 20px;"></div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
    <a href="{{ route('destination.index') }}" class="view-all">View All Destination</a>
  </div>
@endsection
