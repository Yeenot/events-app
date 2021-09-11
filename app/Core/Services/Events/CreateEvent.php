<?php

namespace App\Core\Services\Events;

use App\Models\Event;
use Illuminate\Support\Arr;

class CreateEvent
{
    public function execute($data)
    {
        $days = Arr::pull($data, 'days');
        $daysOfweek = [];
        foreach($days as $day) {
            $daysOfweek[] = [ 'day' => $day ];
        }

        $event = Event::create($data);
        $event->days()->createMany($daysOfweek);

        return $event;
    }
}