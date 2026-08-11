@extends('layouts.app')

@section('title', $place->name . ' — CamboTrips')
@section('meta_description', $place->tagline ?? 'Discover ' . $place->name . ' in Cambodia')

@section('content')
  <!-- Back Button -->
  <a href="{{ url()->previous() }}" class="btn-back">← Back</a>

  <!-- Hero Banner -->
  <div class="place-hero">
    <img src="{{ $place->image }}" alt="{{ $place->name }}" onerror="this.src='https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=1200&q=60'" />
    <div class="place-hero-overlay">
      <h1 class="place-hero-title">{{ $place->name }}</h1>
      @if($place->tagline)
        <p class="place-hero-tagline">{{ $place->tagline }}</p>
      @endif
    </div>
  </div>

  <!-- Quick Info Bar -->
  @if($place->quick_info && is_array($place->quick_info))
    <div class="quick-info-bar">
      @php
        $qiIcons = ['📍', '🕐', '💰', '⭐'];
        $qiLabels = ['Location', 'Best Time', 'Entry Fee', 'Rating'];
        $i = 0;
      @endphp
      @foreach($place->quick_info as $key => $value)
        <div class="quick-info-card">
          <div class="qi-label">
            <span>{{ $qiIcons[$i % 4] ?? '📌' }}</span>
            {{ is_string($key) ? ucfirst(str_replace('_', ' ', $key)) : ($qiLabels[$i] ?? 'Info') }}
          </div>
          <div class="qi-value">{{ $value }}</div>
        </div>
        @php $i++; @endphp
      @endforeach
    </div>
  @endif

  <div class="place-content">
    <!-- About Section -->
    @if($place->about)
      <h2 class="place-section-title">About This Place</h2>
      <div class="about-grid">
        <p class="about-text">{{ $place->about }}</p>
        @if($place->about_image || $place->about_image_2 || $place->about_image_3)
          <div class="about-gallery">
            @if($place->about_image)
              <div class="about-img">
                <img src="{{ $place->about_image }}" alt="{{ $place->name }} scenery 1" onerror="this.parentElement.style.display='none'" />
              </div>
            @endif
            @if($place->about_image_2)
              <div class="about-img">
                <img src="{{ $place->about_image_2 }}" alt="{{ $place->name }} scenery 2" onerror="this.parentElement.style.display='none'" />
              </div>
            @endif
            @if($place->about_image_3)
              <div class="about-img">
                <img src="{{ $place->about_image_3 }}" alt="{{ $place->name }} scenery 3" onerror="this.parentElement.style.display='none'" />
              </div>
            @endif
          </div>
        @endif
      </div>
    @endif

    <!-- Things To Do -->
    @if($place->activities->count() > 0)
      <h2 class="place-section-title">Things To Do</h2>
      <div class="things-todo">
        <ul>
          @foreach($place->activities as $activity)
            <li>{{ $activity->name }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Map Link -->
    @if($place->map_link)
      <a href="{{ $place->map_link }}" target="_blank" rel="noopener" class="map-link">
        📍 Open in Google Maps
      </a>
    @endif

    <!-- Local Foods -->
    @if($place->foods->count() > 0)
      <h2 class="place-section-title">Local Foods to Try</h2>
      <div class="place-cards-grid">
        @foreach($place->foods as $food)
          <div class="place-sub-card">
            @if($food->image)
              <img src="{{ $food->image }}" alt="{{ $food->name }}" loading="lazy" onerror="this.style.display='none'" />
            @endif
            <div class="place-sub-card-body">
              <h3 class="place-sub-name">{{ $food->name }}</h3>
              @if($food->description)
                <p class="place-sub-desc">{{ $food->description }}</p>
              @endif
              @if($food->price)
                <p class="place-sub-price">{{ $food->price }}</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <!-- Hotels -->
    @if($place->hotels->count() > 0)
      <h2 class="place-section-title">Where to Stay</h2>
      <div class="place-cards-grid">
        @foreach($place->hotels as $hotel)
          <div class="place-sub-card" @if($hotel->map_link) onclick="window.open('{{ $hotel->map_link }}', '_blank')" style="cursor: pointer;" @endif>
            @if($hotel->image)
              <img src="{{ $hotel->image }}" alt="{{ $hotel->name }}" loading="lazy" onerror="this.style.display='none'" />
            @endif
            <div class="place-sub-card-body">
              <h3 class="place-sub-name">{{ $hotel->name }}</h3>
              @if($hotel->description)
                <p class="place-sub-desc">{{ $hotel->description }}</p>
              @endif
              @if($hotel->price)
                <p class="place-sub-price">{{ $hotel->price }}</p>
              @endif
              @if($hotel->map_link)
                <a href="{{ $hotel->map_link }}" target="_blank" rel="noopener" class="map-link" style="font-size: 0.78rem; padding: 6px 14px; margin-top: 8px;" onclick="event.stopPropagation();">📍 View on Map</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <!-- Restaurants -->
    @if($place->restaurants->count() > 0)
      <h2 class="place-section-title">Where to Eat</h2>
      <div class="place-cards-grid">
        @foreach($place->restaurants as $restaurant)
          <div class="place-sub-card" @if($restaurant->map_link) onclick="window.open('{{ $restaurant->map_link }}', '_blank')" style="cursor: pointer;" @endif>
            @if($restaurant->image)
              <img src="{{ $restaurant->image }}" alt="{{ $restaurant->name }}" loading="lazy" onerror="this.style.display='none'" />
            @endif
            <div class="place-sub-card-body">
              <h3 class="place-sub-name">{{ $restaurant->name }}</h3>
              @if($restaurant->description)
                <p class="place-sub-desc">{{ $restaurant->description }}</p>
              @endif
              @if($restaurant->map_link)
                <a href="{{ $restaurant->map_link }}" target="_blank" rel="noopener" class="map-link" style="font-size: 0.78rem; padding: 6px 14px; margin-top: 8px;" onclick="event.stopPropagation();">📍 View on Map</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <!-- Reviews Section -->
    <h2 class="place-section-title">Reviews & Ratings</h2>

    <!-- Rating Summary -->
    <div class="review-summary">
      <div class="review-avg">
        <span class="review-avg-number">{{ $place->average_rating }}</span>
        <div class="review-stars">
          @for($s = 1; $s <= 5; $s++)
            <span class="star {{ $s <= round($place->average_rating) ? 'filled' : '' }}">★</span>
          @endfor
        </div>
        <span class="review-count">{{ $place->reviews_count }} {{ Str::plural('review', $place->reviews_count) }}</span>
      </div>
    </div>

    <!-- Review Form (Auth Only) -->
    @auth
      @php
        $userReview = $place->reviews->where('user_id', auth()->id())->first();
      @endphp
      @if(!$userReview)
        <div class="review-form-card">
          <h3 class="review-form-title">Share Your Experience</h3>
          <form action="{{ route('reviews.store', $place->id) }}" method="POST">
            @csrf
            <div class="star-rating-input" id="starRatingInput">
              <span class="star-label">Your Rating:</span>
              @for($s = 1; $s <= 5; $s++)
                <span class="star-btn" data-value="{{ $s }}">★</span>
              @endfor
              <input type="hidden" name="rating" id="ratingValue" value="" required />
            </div>
            @error('rating')
              <p class="form-error">{{ $message }}</p>
            @enderror
            <div class="form-group" style="margin-top: 14px;">
              <textarea name="comment" class="form-input no-icon" rows="4" placeholder="Tell us about your experience at {{ $place->name }}..." required maxlength="1000" style="resize: vertical; padding: 14px 16px;">{{ old('comment') }}</textarea>
            </div>
            @error('comment')
              <p class="form-error">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn-accent" style="margin-top: 4px;">Submit Review ⭐</button>
          </form>
        </div>
      @else
        <div class="review-form-card" style="text-align: center;">
          <p style="color: var(--color-accent); font-weight: 600;">✅ You have already reviewed this place.</p>
        </div>
      @endif
    @else
      <div class="review-form-card" style="text-align: center;">
        <p>Want to share your experience? <a href="{{ route('login') }}" style="color: var(--color-accent); font-weight: 600;">Log in</a> to leave a review.</p>
      </div>
    @endauth

    <!-- Existing Reviews List -->
    @if($place->reviews->count() > 0)
      <div class="reviews-list">
        @foreach($place->reviews->sortByDesc('created_at') as $review)
          <div class="review-item">
            <div class="review-header">
              <div class="review-avatar">{{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}</div>
              <div>
                <span class="review-author">{{ $review->user->name ?? 'Anonymous' }}</span>
                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
              </div>
            </div>
            <div class="review-stars" style="margin: 8px 0;">
              @for($s = 1; $s <= 5; $s++)
                <span class="star {{ $s <= $review->rating ? 'filled' : '' }}" style="font-size: 0.9rem;">★</span>
              @endfor
            </div>
            <p class="review-text">{{ $review->comment }}</p>
          </div>
        @endforeach
      </div>
    @else
      <p class="no-results" style="padding: 30px 0;">No reviews yet. Be the first to share your experience!</p>
    @endif
  </div>
@endsection

@section('scripts')
<script>
  // Star rating interactive selection
  document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('starRatingInput');
    if (!container) return;

    const stars = container.querySelectorAll('.star-btn');
    const input = document.getElementById('ratingValue');

    stars.forEach(star => {
      star.addEventListener('mouseenter', () => {
        const val = parseInt(star.dataset.value);
        stars.forEach((s, i) => {
          s.classList.toggle('hovered', i < val);
        });
      });

      star.addEventListener('mouseleave', () => {
        const current = parseInt(input.value) || 0;
        stars.forEach((s, i) => {
          s.classList.remove('hovered');
          s.classList.toggle('selected', i < current);
        });
      });

      star.addEventListener('click', () => {
        const val = parseInt(star.dataset.value);
        input.value = val;
        stars.forEach((s, i) => {
          s.classList.toggle('selected', i < val);
        });
      });
    });
  });
</script>
@endsection
