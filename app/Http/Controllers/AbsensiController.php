<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    private $apiKey;
    private $workspaceId;
    private $userId;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('CLOCKIFY_API_KEY');
        $this->workspaceId = env('CLOCKIFY_WORKSPACE_ID');
        $this->userId = env('CLOCKIFY_USER_ID');
        $this->apiUrl = 'https://api.clockify.me/api/v1';
    }

    public function clockIn()
    {
        $url = "{$this->apiUrl}/workspaces/{$this->workspaceId}/time-entries";

        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey
        ])->post($url, [
            "start" => now()->toIso8601String(),
            "description" => "Clock In"
        ]);

        if (!$response->successful()) {
            return response()->json(["error" => "Failed to Clock In"], 500);
        }

        return response()->json($response->json());
    }

    public function clockOut()
    {
        // Ambil daftar time entry yang sedang berjalan
        $runningEntry = Http::withHeaders([
            'X-Api-Key' => $this->apiKey
        ])->get("{$this->apiUrl}/workspaces/{$this->workspaceId}/user/{$this->userId}/time-entries");

        $entries = $runningEntry->json();



        if (empty($entries) || !isset($entries[0]['id'])) {
            return response()->json(['message' => 'No active time entry found'], 404);
        }


        $timeEntryId = $entries[0]['id'];
        $startTime = $entries[0]['timeInterval']['start']; // Ambil waktu mulai


        // Stop Time Entry dengan metode PUT
        $endTime = now()->toIso8601String();
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->put("{$this->apiUrl}/workspaces/{$this->workspaceId}/time-entries/{$timeEntryId}", [
            "start" => $startTime,
            "end" => $endTime
        ]);

        // Hitung durasi kerja dalam detik
        $start = \Carbon\Carbon::parse($startTime);
        $end = \Carbon\Carbon::parse($endTime);
        $durationInSeconds = $start->diffInSeconds($end);

        // Konversi durasi ke format HH:MM:SS
        $hours = floor($durationInSeconds / 3600);
        $minutes = floor(($durationInSeconds % 3600) / 60);
        $seconds = $durationInSeconds % 60;
        $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        return response()->json([
            "message" => "Clock Out successful",
            "start" => $startTime,
            "end" => $endTime,
            "duration" => $formattedDuration // Kirim durasi dalam format HH:MM:SS
        ]);
    }
}
