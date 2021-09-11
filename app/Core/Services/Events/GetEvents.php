<?php

namespace App\Core\Services\Events;

use App\Models\Event;

class GetEvents
{
    public function execute()
    {
        return Event::with('days')->latest()->get();
    }
}