<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    private function buildHeaders(): array
    {
        return [
            'Accept-Language' => 'en',
            'User-Agent' => config('app.name', 'GasGo') . '/1.0 (location-service)',
        ];
    }

    private function toFloat($value, float $fallback = 0.0): float
    {
        if (!is_numeric($value)) {
            return $fallback;
        }

        return (float) $value;
    }

    private function composeDisplayNameFromAddress(?array $address): ?string
    {
        if (!$address) {
            return null;
        }

        $parts = [
            $address['house_number'] ?? null,
            $address['road'] ?? null,
            $address['suburb'] ?? null,
            $address['quarter'] ?? null,
            $address['neighbourhood'] ?? null,
            $address['village'] ?? null,
            $address['hamlet'] ?? null,
            $address['barangay'] ?? null,
            $address['city'] ?? null,
            $address['town'] ?? null,
            $address['municipality'] ?? null,
            $address['state'] ?? null,
            $address['country'] ?? null,
        ];

        $filtered = array_values(array_filter($parts, function ($value) {
            return is_string($value) && trim($value) !== '';
        }));

        if (count($filtered) === 0) {
            return null;
        }

        return implode(', ', array_slice($filtered, 0, 6));
    }

    private function reverseLookup(float $lat, float $lng, int $zoom): ?array
    {
        $response = Http::withHeaders($this->buildHeaders())
            ->timeout(8)
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'addressdetails' => 1,
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => $zoom,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            return null;
        }

        return $payload;
    }

    private function searchNearestByCoordinate(float $lat, float $lng): ?array
    {
        $response = Http::withHeaders($this->buildHeaders())
            ->timeout(8)
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'addressdetails' => 1,
                'countrycodes' => 'ph',
                'limit' => 1,
                'q' => $lat . ', ' . $lng,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (!is_array($payload) || !isset($payload[0]) || !is_array($payload[0])) {
            return null;
        }

        return $payload[0];
    }

    private function searchWithParams(array $params): array
    {
        $response = Http::withHeaders($this->buildHeaders())
            ->timeout(8)
            ->get('https://nominatim.openstreetmap.org/search', $params);

        if (!$response->successful()) {
            return [];
        }

        $results = $response->json();
        return is_array($results) ? $results : [];
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
            'limit' => $validated['limit'] ?? 8,
            'dedupe' => 1,
            'q' => $validated['q'],
        ];

        $hasBounds = isset($validated['left'], $validated['top'], $validated['right'], $validated['bottom']);
        if ($hasBounds) {
            $params['viewbox'] = implode(',', [
                $validated['left'],
                $validated['top'],
                $validated['right'],
                $validated['bottom'],
            ]);
            $params['bounded'] = 1;
        }

        try {
            $results = [];

            if ($hasBounds) {
                $boundedParams = $params;
                $boundedParams['bounded'] = 1;
                $results = $this->searchWithParams($boundedParams);

                // If strict bounds returns nothing, retry with viewbox bias only.
                if (count($results) === 0) {
                    $results = $this->searchWithParams($params);
                }
            } else {
                $results = $this->searchWithParams($params);
            }

            return response()->json([
                'results' => $results,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'results' => [],
                'message' => 'Location search failed.',
            ], 200);
        }
    }

    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'zoom' => ['nullable', 'integer', 'min:5', 'max:18'],
        ]);

        $lat = $this->toFloat($validated['lat']);
        $lng = $this->toFloat($validated['lng']);
        $requestedZoom = (int) ($validated['zoom'] ?? 18);
        $zoomAttempts = array_values(array_unique([$requestedZoom, 17, 16, 14, 12, 10, 8, 6, 5]));

        try {
            foreach ($zoomAttempts as $zoom) {
                $json = $this->reverseLookup($lat, $lng, $zoom);
                if (!$json) {
                    continue;
                }

                $displayName = $json['display_name'] ?? null;
                $address = is_array($json['address'] ?? null) ? $json['address'] : null;

                if (!$displayName && $address) {
                    $displayName = $this->composeDisplayNameFromAddress($address);
                }

                if ($displayName || $address) {
                    return response()->json([
                        'display_name' => $displayName,
                        'address' => $address,
                    ], 200);
                }
            }

            $nearest = $this->searchNearestByCoordinate($lat, $lng);
            if ($nearest) {
                $nearestDisplayName = $nearest['display_name'] ?? null;
                $nearestAddress = is_array($nearest['address'] ?? null) ? $nearest['address'] : null;

                if (!$nearestDisplayName && $nearestAddress) {
                    $nearestDisplayName = $this->composeDisplayNameFromAddress($nearestAddress);
                }

                if ($nearestDisplayName || $nearestAddress) {
                    return response()->json([
                        'display_name' => $nearestDisplayName,
                        'address' => $nearestAddress,
                    ], 200);
                }
            }

            return response()->json([
                'display_name' => null,
                'address' => null,
            ], 200);
        } catch (\Throwable $exception) {
            return response()->json([
                'display_name' => null,
                'address' => null,
            ], 200);
        }
    }
}
