<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventYoutubeLink extends Model
{
    protected $fillable = ['event_id', 'url', 'title', 'sort_order'];

    protected $appends = ['embed_url'];

    public function getEmbedUrlAttribute()
    {
        $url = $this->url;

        try {
            $parsed = parse_url($url);
            parse_str($parsed['query'] ?? '', $query);

            if (str_contains($parsed['host'] ?? '', 'youtube.com') && isset($query['v'])) {
                return 'https://www.youtube.com/embed/' . $query['v'];
            }

            if (str_contains($parsed['host'] ?? '', 'youtu.be')) {
                return 'https://www.youtube.com/embed/' . ltrim($parsed['path'] ?? '', '/');
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
