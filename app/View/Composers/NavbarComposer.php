<?php

namespace App\View\Composers;

use App\Models\EventCategory;
use Illuminate\View\View;

class NavbarComposer
{
    public function compose(View $view): void
    {
        $view->with(
            'eventCategories',
            EventCategory::with(['events' => function ($query) {
                $query->published()
                    ->select('id', 'event_category_id', 'name', 'event_code', 'start_at', 'end_at')
                    ->latest('start_at')
                    ->limit(6); // batasi biar dropdown gak kepanjangan
            }])
            // ->has('events') // skip kategori yang gak punya event published
            ->orderBy('name')
            ->get()
        );
    }
}