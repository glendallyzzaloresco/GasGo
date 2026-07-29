<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodingController extends Controller
{
    private function nominatimHeaders(): array
    {
        return [
            'User-Agent' => 'GasGo/1.0',
            'Accept-Language' => 'en',
        ];
    }

    private function geoapifyKey(): ?string
    {
        return env('GEOAPIFY_API_KEY');
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
            'left' => ['nullable', 'numeric'],
            'top' => ['nullable', 'numeric'],
            'right' => ['nullable', 'numeric'],
            'bottom' => ['nullable', 'numeric'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $params = [
            'format' => 'json',
            'addressdetails' => 1,
            'countrycodes' => 'ph',
            'limit' => $validated['limit'] ?? 6,
            'q' => $validated['q'],
            'dedupe' => 1,
        ];

        $hasBounds = isset($validated['left'], $validated['top'], $validated['right'], $validated['bottom']);
        if ($hasBounds) {
            $params['viewbox'] = implode(',', [
                $validated['left'],
                $validated['top'],
                $validated['right'],
                $validated['bottom'],
            ]);
        }

        try {
            $results = [];

            if ($hasBounds) {
                $boundedParams = $params;
                $boundedParams['bounded'] = 1;

                $boundedResponse = Http::withHeaders($this->nominatimHeaders())
                    ->timeout(8)
                    ->get('https://nominatim.openstreetmap.org/search', $boundedParams);

                if ($boundedResponse->successful()) {
                    $boundedData = $boundedResponse->json();
                    $results = is_array($boundedData) ? $boundedData : [];
                }
            }

            if (count($results) === 0) {
                $response = Http::withHeaders($this->nominatimHeaders())
                    ->timeout(8)
                    ->get('https://nominatim.openstreetmap.org/search', $params);

                if (! $response->successful()) {
                    return response()->json(['results' => []], 200);
                }

                $data = $response->json();
                $results = is_array($data) ? $data : [];
            }

            return response()->json(['results' => $results], 200);
        } catch (\Throwable $exception) {
            return response()->json(['results' => []], 200);
        }
    }

    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'zoom' => ['nullable', 'integer', 'min:5', 'max:18'],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $zoom = (int) ($validated['zoom'] ?? 18);
        $apiKey = $this->geoapifyKey();

        try {
            // 1. Try Geoapify Reverse Geocoding if API key is provided
            if (! empty($apiKey)) {
                $response = Http::timeout(8)
                    ->get('https://api.geoapify.com/v1/geocode/reverse', [
                        'lat' => $lat,
                        'lon' => $lng,
                        'format' => 'json',
                        'apiKey' => $apiKey,
                    ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    $feature = $payload['results'][0] ?? $payload['features'][0]['properties'] ?? null;

                    if ($feature) {
                        $street = $feature['street'] ?? null;
                        if (! empty($feature['housenumber'])) {
                            $street = $feature['housenumber'] . ' ' . $street;
                        }

                        $suburb = $feature['suburb']
                            ?? $feature['district']
                            ?? $feature['neighbourhood']
                            ?? $feature['village']
                            ?? null;

                        $city = $feature['city']
                            ?? $feature['town']
                            ?? $feature['municipality']
                            ?? $feature['county']
                            ?? null;

                        return response()->json([
                            'display_name' => $feature['formatted'] ?? $feature['address_line1'] ?? null,
                            'address' => $feature,
                            'street' => $street,
                            'suburb' => $suburb,
                            'city' => $city,
                        ], 200);
                    }
                }
            }

            // 2. Fallback to OpenStreetMap Nominatim
            $response = Http::withHeaders($this->nominatimHeaders())
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'addressdetails' => 1,
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => $zoom,
                ]);

            if ($response->successful()) {
                $payload = $response->json();
                $address = is_array($payload['address'] ?? null) ? $payload['address'] : [];

                $street = trim((($address['house_number'] ?? '') . ' ' . ($address['road'] ?? '')));
                if ($street === '') {
                    $street = $address['road'] ?? $address['pedestrian'] ?? $address['residential'] ?? null;
                }

                $suburb = $address['barangay']
                    ?? $address['suburb']
                    ?? $address['neighbourhood']
                    ?? $address['village']
                    ?? $address['hamlet']
                    ?? null;

                $city = $address['city']
                    ?? $address['town']
                    ?? $address['municipality']
                    ?? $address['county']
                    ?? null;

                return response()->json([
                    'display_name' => $payload['display_name'] ?? null,
                    'address' => $address,
                    'street' => $street,
                    'suburb' => $suburb,
                    'city' => $city,
                ], 200);
            }

            return response()->json([
                'display_name' => null,
                'address' => null,
                'street' => null,
                'suburb' => null,
                'city' => null,
            ], 200);
        } catch (\Throwable $exception) {
            return response()->json([
                'display_name' => null,
                'address' => null,
                'street' => null,
                'suburb' => null,
                'city' => null,
            ], 200);
        }
    }
}
