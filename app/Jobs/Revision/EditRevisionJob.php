<?php

namespace App\Jobs\Revision;

use App\Actions\Revision\EditRevisionAction;
use App\Revision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EditRevisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Revision $revision;
    private string $filePath;
    private EditRevisionAction $action;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Revision $revision, string $filePath)
    {
        $this->revision = $revision;
        $this->filePath = $filePath;
        $this->action = new EditRevisionAction();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Job started');
        $this->action->handle($this->revision, $this->filePath);
    }
}
