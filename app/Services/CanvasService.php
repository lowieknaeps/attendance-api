<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CanvasService
{
    public function courses(string $token, string $baseUrl): array
    {
        $url = rtrim($baseUrl, '/') . '/courses?enrollment_state=active&per_page=100';

        $all = [];

        while ($url) {
            $res = Http::withToken($token)->get($url)->throw();

            $all = array_merge($all, $res->json() ?? []);

            $url = $this->nextLink($res->header('Link'));
        }

        return $all;
    }

    private function nextLink(?string $linkHeader): ?string
    {
        if (! $linkHeader) return null;
        foreach (explode(',', $linkHeader) as $part) {
            if (str_contains($part, 'rel="next"')) {
                if (preg_match('/<([^>]+)>/', $part, $m)) return $m[1];
            }
        }

        return null;
    }
}
