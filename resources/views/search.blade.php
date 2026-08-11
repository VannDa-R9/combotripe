@extends('layouts.app')

@section('title', ($query ? $query . ' — ' : '') . 'Search — CamboTrips')
@section('meta_description', 'Search for destinations, temples, beaches, mountains and more across Cambodia.')

@section('content')
  <!-- Search Hero -->
  <div class="search-hero">
    <img src="https://www.image2url.com/r2/default/images/1781278986796-2dc1cee5-42a8-4d51-a06f-6fffa5fda0c9.png" alt="Cambodia landscape" />
    <div class="search-hero-overlay">
      <h1 style="color: var(--color-white); font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; text-shadow: 2px 3px 10px rgba(0,0,0,0.5);">Find Your Next Adventure</h1>
      <form action="{{ route('search') }}" method="GET" class="search-input-wrap">
        <span class="search-icon">🔍</span>
        <input
          id="main-search-input"
          type="text"
          name="q"
          class="search-input"
          placeholder="Search destinations, temples, beaches..."
          value="{{ $query }}"
          autocomplete="off"
        />
        @if(!empty($category))
          <input type="hidden" name="category" value="{{ $category }}" />
        @endif
      </form>
      <div class="filter-chips-group">
        <span class="filter-label">Quick:</span>
        @php
          $quickCategories = ['Temple', 'Beach', 'Nature', 'Mountain', 'Culture', 'Nightlife'];
        @endphp
        @foreach($quickCategories as $cat)
          <a href="{{ route('search', ['category' => $cat, 'q' => $query]) }}" class="filter-chip {{ $category === $cat ? 'active' : '' }}">{{ $cat }}</a>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Results Area -->
  <div class="search-page-content">
    <div class="search-main">
      @if(!empty($query) || !empty($category))
        <h2 class="search-results-title">
          @if(!empty($query) && !empty($category))
            Results for "{{ $query }}" in {{ $category }}
          @elseif(!empty($query))
            Results for "{{ $query }}"
          @else
            Browsing: {{ $category }}
          @endif
          <span style="font-weight: 400; font-style: normal; text-decoration: none; font-size: 0.9rem; color: var(--color-muted); margin-left: 8px;">({{ $places->count() }} {{ Str::plural('result', $places->count()) }})</span>
        </h2>

        <!-- Active Filter Chips -->
        <div class="search-category-row">
          <a href="{{ route('search', ['q' => $query]) }}" class="chip {{ empty($category) ? 'active' : '' }}">All</a>
          @foreach($quickCategories as $cat)
            <a href="{{ route('search', ['category' => $cat, 'q' => $query]) }}" class="chip {{ $category === $cat ? 'active' : '' }}">{{ $cat }}</a>
          @endforeach
        </div>
      @endif

      @if($places->count() > 0)
        <div class="cards-grid">
          @foreach($places as $place)
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
                @if($place->province)
                  <p style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">📍 {{ $place->province->name }}</p>
                @endif
              </div>
            </a>
          @endforeach
        </div>
      @elseif(!empty($query) || !empty($category))
        <div class="no-results">
          <p style="font-size: 3rem; margin-bottom: 16px;">🏝️</p>
          <p style="font-weight: 600; font-size: 1.1rem; margin-bottom: 8px;">No destinations found</p>
          <p>Try a different search term or browse by category above.</p>
          <a href="{{ route('search') }}" class="btn-accent" style="margin-top: 20px; display: inline-block;">Clear Search</a>
        </div>
      @else
        <div class="no-results">
          <p style="font-size: 3rem; margin-bottom: 16px;">✨</p>
          <p style="font-weight: 600; font-size: 1.1rem; margin-bottom: 8px;">Start your search</p>
          <p>Type a keyword above or pick a category to explore Cambodia's treasures.</p>
        </div>
      @endif
    </div>
  </div>
@endsection
