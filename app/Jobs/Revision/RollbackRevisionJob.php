<?php

namespace App\Jobs\Revision;

use App\Actions\Revision\RollbackRevisionAction;
use App\Actions\Revision\SendRevisionToApprovementAction;
use App\Revision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Exception;

class RollbackRevisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Revision $revision;
    private RollbackRevisionAction $action;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Revision $revision)
    {
        $this->revision = $revision;
        $this->action = new RollbackRevisionAction(new SendRevisionToApprovementAction());
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->action->handle($this->revision);
    }
}
