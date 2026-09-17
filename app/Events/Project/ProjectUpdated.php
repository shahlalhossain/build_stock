<?php

namespace App\Events\Project;

use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }
}
