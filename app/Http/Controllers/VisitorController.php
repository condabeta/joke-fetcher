<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorController extends Controller
{
    public function track(Request $request)
    {
        $data = $request->validate([
            'ip' => 'nullable|string|max:45',
            'city' => 'nullable|string|max:120',
            'country' => 'nullable|string|max:120',
            'device' => 'nullable|string|max:50',
            'user_agent' => 'nullable|string|max:512',
        ]);

        Visitor::create([
            'ip' => $data['ip'] ?? $request->ip(),
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'device' => $data['device'] ?? 'Unknown',
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            'visited_at' => Carbon::now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function data()
    {
        $since = Carbon::now()->subHours(24);

        $rows = Visitor::where('visited_at', '>=', $since)->get();

        $byHour = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i)->format('Y-m-d H:00');
            $byHour[$hour] = 0;
        }

        $seenPerHour = [];
        foreach ($rows as $row) {
            $hour = Carbon::parse($row->visited_at)->format('Y-m-d H:00');
            if (!array_key_exists($hour, $byHour)) {
                continue;
            }
            $key = $hour.'|'.$row->ip;
            if (isset($seenPerHour[$key])) {
                continue;
            }
            $seenPerHour[$key] = true;
            $byHour[$hour]++;
        }

        $byCity = Visitor::selectRaw('COALESCE(NULLIF(city, ""), "Unknown") as city, COUNT(*) as total')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        return response()->json([
            'hourly' => [
                'labels' => array_keys($byHour),
                'values' => array_values($byHour),
            ],
            'cities' => [
                'labels' => $byCity->pluck('city'),
                'values' => $byCity->pluck('total'),
            ],
            'total' => Visitor::count(),
        ]);
    }
}
