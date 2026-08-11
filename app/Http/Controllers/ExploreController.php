<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Tag;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        // Get active tags from request query
        $activeTags = $request->input('tags', []);
        if (is_string($activeTags)) {
            $activeTags = array_filter(explode(',', $activeTags));
        }

        // Get all available tags for filters
        $allTags = Tag::orderBy('name')->get();

        // Base query for places
        $query = Place::with(['tags', 'reviews', 'province']);

        if (!empty($activeTags)) {
            $query->whereHas('tags', function ($q) use ($activeTags) {
                $q->whereIn('name', $activeTags);
            });
        }

        $allPlaces = $query->get();

        // Curated & Popular selection (or fall back gracefully to chunk/slice of all places)
        $curatedIds = ['angkor-wat', 'koh-rong', 'tatai-waterfall', 'bokor-mountain', 'elephant-valley-project', 'yeak-laom-volcanic-lake', 'kulen-mountain'];
        $popularIds = ['kep-crab-market', 'kampot-old-town', 'dak-dam-waterfall', 'pub-street', 'zipline-angkor', 'bousra-waterfall', 'royal-palace-phnom-penh'];

        $curated = $allPlaces->filter(function ($place) use ($curatedIds) {
            return in_array($place->id, $curatedIds);
        })->values();

        // If curated has fewer items, take first items of allPlaces
        if ($curated->isEmpty() && $allPlaces->isNotEmpty()) {
            $curated = $allPlaces->take(4);
        }

        $popular = $allPlaces->filter(function ($place) use ($popularIds) {
            return in_array($place->id, $popularIds);
        })->values();

        // If popular is empty, take next slice of allPlaces
        if ($popular->isEmpty() && $allPlaces->isNotEmpty()) {
            $popular = $allPlaces->skip($curated->count())->take(4);
        }

        return view('explore', compact('allPlaces', 'curated', 'popular', 'allTags', 'activeTags'));
    }
}
