@extends('tablar::page')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Artikel</h2></div>
            <div class="col-auto ms-auto">
                <a href="{{ route('articles.create') }}" class="btn btn-dark">+ Tulis Artikel</a>
            </div>
        </div>
    </div>
</div>
@php
    $dot = fn ($s) => is_null($s) ? '#9ca3af' : ($s >= 70 ? '#16a34a' : ($s >= 40 ? '#f59e0b' : '#dc2626'));
    $sortUrl = fn ($col) => request()->fullUrlWithQuery([
        'orderby' => $col,
        'order'   => ($orderby === $col && $order === 'desc') ? 'asc' : 'desc',
    ]);
    $tab = fn ($label, $key, $n) => '<a href="' . e(route('articles.index', $key ? ['status' => $key] : [])) . '"'
        . ((request('status') ?: null) === $key ? ' style="font-weight:bold;color:#000"' : '') . '>'
        . $label . ' (' . $n . ')</a>';
@endphp
<div class="page-body">
    <div class="container-xl">
        <div class="card"><div class="card-body">
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Tab status --}}
<div class="mb-3">
    {!! $tab('Semua', null, $counts['all']) !!} |
    {!! $tab('Terbit', 'published', $counts['published']) !!} |
    {!! $tab('Draft', 'draft', $counts['draft']) !!} |
    {!! $tab('Hapus', 'trash', $counts['trash']) !!}
</div>
{{-- @if(request('tag'))
    <a href="{{ route('articles.index', request()->except('tag', 'page')) }}" class="badge bg-red-lt text-decoration-none">Tag: {{ $tags_active_name ?? request('tag') }} ✕</a>
@endif --}}
{{-- <form method="GET" class="article-filter mb-3">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="hidden" name="tag" value="{{ request('tag') }}">

    <div class="article-filter-row">
        <div class="filter-item filter-date">
            <select name="month" class="form-select">
                <option value="">Semua tanggal</option>
                @foreach($months as $m)
                    <option value="{{ $m }}" @selected(request('month') === $m)>
                        {{ \Carbon\Carbon::parse($m . '-01')->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-item filter-category">
            <select name="category" class="form-select">
                <option value="">Semua kategori</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}"
                        @selected((string) request('category') === (string) $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @foreach([
            'seo' => 'Semua skor SEO',
            'readability' => 'Semua skor keterbacaan'
        ] as $name => $label)
            <div class="filter-item filter-score">
                <select name="{{ $name }}" class="form-select">
                    <option value="">{{ $label }}</option>
                    <option value="good" @selected(request($name) === 'good')>
                        Baik (hijau)
                    </option>
                    <option value="ok" @selected(request($name) === 'ok')>
                        Cukup (oranye)
                    </option>
                    <option value="bad" @selected(request($name) === 'bad')>
                        Buruk (merah)
                    </option>
                    <option value="none" @selected(request($name) === 'none')>
                        Belum dihitung
                    </option>
                </select>
            </div>
        @endforeach

        <div class="filter-submit">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="ti ti-filter me-1"></i> Filter
            </button>
        </div>
    </div>
    <div class="article-search-row">
        <div class="article-search">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Cari judul artikel..."
            >

            <button type="submit" class="btn btn-outline-secondary">
                <i class="ti ti-search me-1"></i> Cari
            </button>
        </div>
    </div>
</form> --}}

<form method="GET" class="wp-article-toolbar mb-3">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="hidden" name="tag" value="{{ request('tag') }}">

    <div class="wp-toolbar-filters">
        <select name="month" class="form-select">
            <option value="">Semua tanggal</option>
            @foreach($months as $m)
                <option value="{{ $m }}" @selected(request('month') === $m)>
                    {{ \Carbon\Carbon::parse($m . '-01')->translatedFormat('F Y') }}
                </option>
            @endforeach
        </select>

        <select name="category" class="form-select">
            <option value="">Semua kategori</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}"
                    @selected((string) request('category') === (string) $c->id)>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        @foreach([
            'seo' => 'Semua skor SEO',
            'readability' => 'Semua skor keterbacaan'
        ] as $name => $label)
            <select name="{{ $name }}" class="form-select">
                <option value="">{{ $label }}</option>
                <option value="good" @selected(request($name) === 'good')>Baik</option>
                <option value="ok" @selected(request($name) === 'ok')>Cukup</option>
                <option value="bad" @selected(request($name) === 'bad')>Buruk</option>
                <option value="none" @selected(request($name) === 'none')>Belum dihitung</option>
            </select>
        @endforeach

        <button type="submit" class="btn btn-outline-secondary">
            Filter
        </button>
    </div>

    <div class="wp-toolbar-search">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            class="form-control"
            placeholder="Cari judul..."
        >
        <button type="submit" class="btn btn-dark">
            Cari
        </button>
    </div>
</form>
{{-- Form tersembunyi untuk aksi per baris (tidak boleh bersarang di dalam form bulk) --}}
<form id="row-form" method="POST" action="{{ route('articles.bulk') }}" style="display:none">
    @csrf
    <input type="hidden" name="bulk_action">
    <input type="hidden" name="ids[]">
</form>

{{-- Bulk --}}
<form method="POST" action="{{ route('articles.bulk') }}">
    @csrf
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <select name="bulk_action" class="form-select"
            style="width:auto; min-width:155px" required>
            <option value="">Bulk actions</option>

            @if(request('status') === 'trash')
                <option value="restore">Restore</option>
                <option value="delete">Hapus permanen</option>
            @else
                <option value="publish">Terbitkan</option>
                <option value="draft">Jadikan draft</option>
                <option value="trash">Pindah ke Trash</option>
            @endif
        </select>

        <button type="submit" class="btn btn-outline-secondary">
            Apply
        </button>

        <span class="ms-auto text-muted small">
            {{ $articles->total() }} artikel
        </span>
    </div>
    {{-- <div class="d-flex gap-2 mb-2">
        <select name="bulk_action" class="form-select" style="width:auto" required>
            <option value="">Bulk actions</option>
            @if(request('status') === 'trash')
                <option value="restore">Restore</option>
                <option value="delete">Hapus permanen</option>
            @else
                <option value="publish">Terbitkan</option>
                <option value="draft">Jadikan draft</option>
                <option value="trash">Pindah ke Trash</option>
            @endif
        </select>
        <button class="btn btn-outline-secondary">Apply</button>
        <span class="ms-auto text-muted">{{ $articles->total() }} artikel</span>
    </div> --}}

    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th style="width:30px"><input type="checkbox" id="check-all"></th>
                    <th><a href="{{ $sortUrl('title') }}">Judul</a></th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Tag</th>
                    <th>Views</th>
                    <th><a href="{{ $sortUrl('created_at') }}">Tanggal</a></th>
                    <th title="Skor SEO">SEO</th>
                    <th title="Skor keterbacaan">Baca</th>
                </tr>
            </thead>
            <tbody>
            @forelse($articles as $a)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check"></td>
                    <td class="article-title-cell">
                        <strong class="article-title-cell">
                            @if($a->trashed()) {{ $a->title }}
                            @else <a href="{{ route('articles.edit', $a) }}">{{ $a->title }}</a>
                            @endif
                        </strong>
                        <div class="small">
                            @if($a->trashed())
                                <a href="#" onclick="rowAction('restore', {{ $a->id }}); return false;">Restore</a> |
                                <a href="#" class="text-danger"
                                   onclick="rowAction('delete', {{ $a->id }}, 'Hapus permanen?'); return false;">Hapus permanen</a>
                            @else
                                <a href="{{ route('articles.edit', $a) }}">Edit</a> |
                                <a href="#" class="text-danger"
                                   onclick="rowAction('trash', {{ $a->id }}); return false;">Hapus</a>
                                @if($a->status === 'published')
                                    | <a href="{{ route('articles.show', $a) }}" target="_blank">Lihat</a>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td>{{ $a->author->name ?? '-' }}</td>
                    <td>{{ $a->categories->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td style="min-width:200px;max-width:240px;white-space:normal;word-break:break-word">
                        @forelse($a->tags as $t)
                        <a href="{{ route('articles.index', array_merge(request()->except('page'), ['tag' => $t->slug])) }}"
                        class="badge bg-secondary-lt mb-1 text-decoration-none">{{ $t->name }}</a>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>{{ number_format($a->views) }}</td>
                    <td style="white-space:nowrap">
                        {{ $a->status === 'published' ? 'Terbit' : 'Draft' }}<br>
                        {{ ($a->published_at ?? $a->created_at)->format('Y/m/d \a\t h:i a') }}
                    </td>
                    <td>
                        <span title="{{ $a->seo_score ?? '-' }}/100"
                              style="display:inline-block;width:13px;height:13px;border-radius:50%;background:{{ $dot($a->seo_score) }}"></span>
                    </td>
                    <td>
                        <span title="{{ $a->readability_score ?? '-' }}/100"
                              style="display:inline-block;width:13px;height:13px;border-radius:50%;background:{{ $dot($a->readability_score) }}"></span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted">Tidak ada artikel.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</form>

{{ $articles->links() }}

<script>
document.getElementById('check-all').addEventListener('change', e =>
    document.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked));

function rowAction(action, id, confirmMsg) {
    if (confirmMsg && !confirm(confirmMsg)) return;
    const f = document.getElementById('row-form');
    f.elements['bulk_action'].value = action;
    f.elements['ids[]'].value = id;
    f.submit();
}
</script>
        </div></div>
    </div>
</div>
@endsection