<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventYoutubeLink extends Model
{
    protected $fillable = ['event_id', 'url', 'title', 'sort_order'];

    protected $appends = ['embed_url'];

public function getEmbedUrlAttribute(): ?string
{
    $id = \App\Support\VideoEmbed::youtubeId((string) $this->url);

    return $id ? 'https://www.youtube-nocookie.com/embed/' . $id : null;
}
}
