<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));
        $category = trim($request->input('category', ''));

        // Synonym mapping dictionary
        $synonymMap = [
            'sea' => 'beach',
            'mountain' => 'nature',
            'water' => 'waterfall',
            'temples' => 'temple',
            'islands' => 'island',
        ];

        // Gather all search queries, including synonyms
        $searchTerms = [];
        if (!empty($query)) {
            $searchTerms[] = $query;
            $lowerQuery = strtolower($query);
            foreach ($synonymMap as $synKey => $synVal) {
                if (str_contains($lowerQuery, $synKey)) {
                    $searchTerms[] = $synVal;
                }
            }
        }

        $placesQuery = Place::with(['province', 'tags', 'reviews']);

        // Filter by text search
        if (!empty($query)) {
            $placesQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('name', 'like', "%{$term}%")
                      ->orWhere('tagline', 'like', "%{$term}%")
                      ->orWhere('about', 'like', "%{$term}%")
                      ->orWhereHas('province', function ($sub) use ($term) {
                          $sub->where('name', 'like', "%{$term}%");
                      })
                      ->orWhereHas('tags', function ($sub) use ($term) {
                          $sub->where('name', 'like', "%{$term}%");
                      });
                }
            });
        }

        // Filter by category tag directly
        if (!empty($category)) {
            $placesQuery->whereHas('tags', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        $places = $placesQuery->get();

        return view('search', compact('places', 'query', 'category'));
    }

    /**
     * Endpoint for AJAX Live Search dropdown
     */
    public function liveSearch(Request $request)
    {
        $query = trim(substr((string) $request->input('q', ''), 0, 100));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'provinces' => [],
                'places' => [],
                'foods' => [],
                'hotels' => [],
                'tags' => [],
            ]);
        }

        // 1. Places
        $places = Place::with('province')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('tagline', 'like', "%{$query}%")
            ->limit(4)
            ->get()
            ->map(function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'meta' => $place->province ? $place->province->name : ($place->tagline ?? 'Destination'),
                    'image' => $place->image,
                    'url' => route('place.show', $place->id),
                    'category' => 'Place',
                ];
            });

        // 2. Provinces
        $provinces = \App\Models\Province::where('name', 'like', "%{$query}%")
            ->orWhere('tagline', 'like', "%{$query}%")
            ->limit(4)
            ->get()
            ->map(function ($prov) {
                return [
                    'id' => $prov->id,
                    'name' => $prov->name,
                    'meta' => $prov->tagline ?? 'Province in Cambodia',
                    'image' => $prov->image,
                    'url' => route('destination.show', $prov->id),
                    'category' => 'Province',
                ];
            });

        // 3. Foods
        $foods = \App\Models\Food::with('place')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(4)
            ->get()
            ->map(function ($food) {
                return [
                    'id' => $food->id,
                    'name' => $food->name,
                    'meta' => $food->place ? $food->place->name : 'Local Food',
                    'image' => $food->image,
                    'url' => $food->place ? route('place.show', $food->place_id) : route('explore'),
                    'category' => 'Food',
                ];
            });

        // 4. Hotels
        $hotels = \App\Models\Hotel::with('place')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(4)
            ->get()
            ->map(function ($hotel) {
                return [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'meta' => $hotel->price ? '$' . $hotel->price : 'Hotel',
                    'image' => $hotel->image,
                    'url' => $hotel->place ? route('place.show', $hotel->place_id) : route('explore'),
                    'category' => 'Hotel',
                ];
            });

        // 5. Tags
        $tags = \App\Models\Tag::where('name', 'like', "%{$query}%")
            ->limit(4)
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => '#' . $tag->name,
                    'meta' => 'Category Tag',
                    'image' => null,
                    'url' => route('explore', ['tags' => $tag->name]),
                    'category' => 'Tag',
                ];
            });

        return response()->json([
            'provinces' => $provinces,
            'places' => $places,
            'foods' => $foods,
            'hotels' => $hotels,
            'tags' => $tags,
        ]);
    }
}

