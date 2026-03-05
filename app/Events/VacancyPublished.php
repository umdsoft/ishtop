<?php

namespace App\Events;

use App\Models\Vacancy;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VacancyPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Vacancy $vacancy
    ) {}
}
