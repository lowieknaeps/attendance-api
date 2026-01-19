<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GraphUserTokenClient
{
    public function getWithToken(string $token, string $url, array $query = [], array $headers = []): array
    {
        $fullUrl = str_starts_with($url, 'http')
            ? $url
            : 'https://graph.microsoft.com/' . ltrim($url, '/');

        $res = Http::withOptions([
            'connect_timeout' => 20,  
            'timeout' => 60,         
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // forceer IPv4
            ],
        ])
        ->withToken($token)
        ->withHeaders($headers)
        ->retry(3, 1000)             
        ->get($fullUrl, $query);
            

        if ($res->status() === 401) {
            throw new \RuntimeException('Graph token verlopen of ongeldig (401). Plak een nieuw token.');
        }

        if (! $res->ok()) {
            throw new \RuntimeException('Graph error: ' . $res->body());
        }

        return $res->json();
    }
}
