<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\Shortcodes;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    private const SITE = 'Ruang Kembali';

    public function index()
    {
        $title = 'Artikel Ruang Kembali';
        $desc  = 'Cerita, tips, dan kabar terbaru dari Ruang Kembali.';

        SEOMeta::setTitle($title, false);
        SEOMeta::setDescription($desc);
        SEOMeta::setCanonical(url()->current());
        OpenGraph::setTitle($title)->setDescription($desc)->setUrl(url()->current())->setSiteName(self::SITE);
        TwitterCard::setTitle($title)->setDescription($desc);

        $articles = Article::published()->with('categories')->latest('published_at')->paginate(9);

        return view('articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        abort_unless(
            $article->status === 'published' && $article->published_at <= now(),
            404
        );

        $title = $article->meta_title ?: $article->title;
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<', ' <',
            Shortcodes::strip($article->excerpt ?: $article->content)))));
        $desc  = $article->meta_description ?: Str::limit($plain, 160);

        $img    = $article->og_auto ?: ($article->og_image ?: $article->featured_image);
        $imgUrl = $img ? asset('storage/' . $img) : null;
        $canonical = $article->canonical_url ?: route('articles.show', $article);

        SEOMeta::setTitle($title, false);   // false = jangan tambahkan suffix nama situs
        SEOMeta::setDescription($desc);
        SEOMeta::setCanonical($canonical);
        if ($article->noindex) {
            SEOMeta::setRobots('noindex, nofollow');
        }

        OpenGraph::setTitle($title)->setDescription($desc)->setUrl($canonical)
            ->setType('article')->setSiteName(self::SITE);
        TwitterCard::setTitle($title)->setDescription($desc);
        JsonLd::setType('Article')->setTitle($title)->setDescription($desc)->setUrl($canonical);
        JsonLd::addValue('datePublished', $article->published_at->toIso8601String());
        JsonLd::addValue('dateModified', $article->updated_at->toIso8601String());
        JsonLd::addValue('author', ['@type' => 'Person', 'name' => $article->author?->name ?? self::SITE]);

        // Gambar dipasang sekali saja (sebelumnya terpasang dua kali)
        if ($imgUrl) {
            $article->og_auto
                ? OpenGraph::addImage($imgUrl, ['width' => 1200, 'height' => 630])
                : OpenGraph::addImage($imgUrl);
            TwitterCard::setImage($imgUrl);
            JsonLd::addImage($imgUrl);
        }

        $article->load(['author', 'categories', 'tags']);
        $related = Article::published()->where('id', '!=', $article->id)
            ->latest('published_at')->take(3)->get();

        return view('articles.show', compact('article', 'related'));
    }
}