@extends('layouts.app')

@section('title', 'Destination — CamboTrips')

@section('meta_description', 'Explore Cambodia\'s rich provinces — from ancient temples in the north to relaxing beaches in the south.')

@section('content')
  <!-- Hero Section -->
  <div class="hero">
    <img class="hero-img" src="https://www.image2url.com/r2/default/images/1781278883159-ee67d154-ad85-4700-88e7-1c016986fb64.png" alt="Cambodia destination" />
    <div class="hero-overlay">
      <h1 class="hero-title">Destination</h1>
      <p class="hero-subtitle">Explore the rich provinces of Cambodia. From ancient temples<br>in the North to relaxing beaches in the South.</p>
    </div>
  </div>

  <div class="divider"></div>

  <!-- Province Grid -->
  <div class="provinces-grid" id="province-grid">
    @foreach($provinces as $province)
      <a href="{{ route('destination.show', $province->id) }}" class="province-card">
        <div class="province-img-wrap">
          <img src="{{ $province->image }}" alt="{{ $province->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=600&q=60'">
          <div class="province-overlay">
            <span class="province-label">Province</span>
            <h2 class="province-name">{{ strtoupper($province->name) }}</h2>
            <p class="province-tagline">{{ $province->tagline }}</p>
          </div>
        </div>
        <p class="province-desc">{{ $province->description }}</p>
      </a>
    @endforeach
  </div>
@endsection
