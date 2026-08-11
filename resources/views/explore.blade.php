@extends('layouts.app')

@section('title', 'Explore Cambodia — CamboTrips')
@section('meta_description', 'Explore curated and popular destinations across Cambodia. Filter by tags to find your perfect adventure.')

@section('content')
  <!-- Explore Header -->
  <div class="hero">
    <img class="hero-img" src="https://www.image2url.com/r2/default/images/1781278995347-d7bcd9f1-1793-4248-aa58-650194368acd.png" alt="Explore Cambodia" />
    <div class="hero-overlay">
      <h1 class="hero-title">Explore Cambodia</h1>
      <p class="hero-subtitle">Discover hidden gems, iconic landmarks, and unforgettable experiences across the Kingdom of Wonder.</p>
    </div>
  </div>

  <!-- Tag Filter Chips -->
  <div class="chips-row" style="justify-content: center;">
    <a href="{{ route('explore') }}" class="chip {{ empty($activeTags) ? 'active' : '' }}">All</a>
    @foreach($allTags as $tag)
      @php
        $isActive = in_array($tag->name, $activeTags);
        $newTags = $isActive
          ? array_diff($activeTags, [$tag->name])
          : array_merge($activeTags, [$tag->name]);
        $tagUrl = route('explore') . (!empty($newTags) ? '?tags=' . implode(',', $newTags) : '');
      @endphp
      <a href="{{ $tagUrl }}" class="chip {{ $isActive ? 'active' : '' }}">{{ $tag->name }}</a>
    @endforeach
  </div>

  <div class="divider"></div>

  <!-- Curated Picks -->
  @if($curated->count() > 0)
    <div class="section">
      <h2 class="section-title">✨ Curated Picks</h2>
      <div class="cards-row">
        @foreach($curated as $place)
          <a href="{{ route('place.show', $place->id) }}" class="card place-card">
            <div class="card-img-wrap">
              <img src="{{ $place->image }}" alt="{{ $place->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&q=60'" />
              <div class="card-tags">
                @foreach($place->tags->take(2) as $tag)
                  <span class="tag">{{ $tag->name }}</span>
                @endforeach
              </div>
            </div>
            <div class="card-body">
              <h3 class="card-title">{{ $place->name }}</h3>
              @if($place->reviews_count > 0)
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-accent2); margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                  ⭐ {{ $place->average_rating }} <span style="color: var(--color-muted); font-weight: normal;">({{ $place->reviews_count }})</span>
                </div>
              @endif
              <p class="card-desc">{{ $place->tagline }}</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  <!-- Popular Spots -->
  @if($popular->count() > 0)
    <div class="section">
      <h2 class="section-title">🔥 Popular Spots</h2>
      <div class="cards-row">
        @foreach($popular as $place)
          <a href="{{ route('place.show', $place->id) }}" class="card place-card">
            <div class="card-img-wrap">
              <img src="{{ $place->image }}" alt="{{ $place->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&q=60'" />
              <div class="card-tags">
                @foreach($place->tags->take(2) as $tag)
                  <span class="tag">{{ $tag->name }}</span>
                @endforeach
              </div>
            </div>
            <div class="card-body">
              <h3 class="card-title">{{ $place->name }}</h3>
              @if($place->reviews_count > 0)
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-accent2); margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                  ⭐ {{ $place->average_rating }} <span style="color: var(--color-muted); font-weight: normal;">({{ $place->reviews_count }})</span>
                </div>
              @endif
              <p class="card-desc">{{ $place->tagline }}</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  <!-- All Destinations Grid -->
  @if($allPlaces->count() > 0)
    <div class="section">
      <h2 class="section-title">🧭 All Destinations</h2>
      <div class="cards-row">
        @foreach($allPlaces as $place)
          <a href="{{ route('place.show', $place->id) }}" class="card place-card">
            <div class="card-img-wrap">
              <img src="{{ $place->image }}" alt="{{ $place->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&q=60'" />
              <div class="card-tags">
                @foreach($place->tags->take(2) as $tag)
                  <span class="tag">{{ $tag->name }}</span>
                @endforeach
              </div>
            </div>
            <div class="card-body">
              <h3 class="card-title">{{ $place->name }}</h3>
              @if($place->reviews_count > 0)
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-accent2); margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                  ⭐ {{ $place->average_rating }} <span style="color: var(--color-muted); font-weight: normal;">({{ $place->reviews_count }})</span>
                </div>
              @endif
              <p class="card-desc">{{ $place->tagline }}</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  <!-- Empty State -->
  @if($allPlaces->count() === 0)
    <div class="no-results">
      <p style="font-size: 2.5rem; margin-bottom: 12px;">🔍</p>
      <p style="font-weight: 600; margin-bottom: 6px;">No places match your filters</p>
      <p>Try removing some tag filters to see more results.</p>
      <a href="{{ route('explore') }}" class="btn-accent" style="margin-top: 20px; display: inline-block;">Clear Filters</a>
    </div>
  @endif
@endsection
