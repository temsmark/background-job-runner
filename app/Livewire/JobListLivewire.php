<?php

namespace App\Livewire;

use App\Models\JobLog;
use Livewire\Component;
use Livewire\WithPagination;

class JobListLivewire extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;




    public function render()
    {
        $jobs=JobLog::query()
            ->when($this->search, fn($query) =>
            $query->where('message', 'like', '%' . $this->search . '%')
            )
            ->with('job')
            ->latest()
            ->paginate($this->perPage);


        return view('livewire.job-list-livewire',compact('jobs'));
    }
}
