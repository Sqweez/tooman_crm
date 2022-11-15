<?php

namespace App\Jobs\Revision;

use App\Actions\Revision\SendRevisionToApprovementAction;
use App\Revision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;

class SendRevisionToApprove implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Revision $revision;
    public string $filePath;
    public SendRevisionToApprovementAction $action;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Revision $revision, string $filePath)
    {
        $this->revision = $revision;
        $this->filePath = $filePath;
        $this->action = new SendRevisionToApprovementAction();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        Log::info('The job was started');
        $this->action->handle($this->revision, $this->filePath);
    }
}
