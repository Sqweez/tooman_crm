<?php

namespace App\Actions\Revision;

use App\Revision;
use PhpOffice\PhpSpreadsheet\Exception;

class RollbackRevisionAction {

    private SendRevisionToApprovementAction $action;

    public function __construct(SendRevisionToApprovementAction $action) {
        $this->action = $action;
    }

    /**
     * @throws Exception
     */
    public function handle(Revision $revision) {
        $this->action->handle($revision, str_replace('storage/', '', 'public/' . $revision->original_loaded_revision_file));
        $revision->update([
            'edited_pivot_file' => null,
            'edited_pivot_at' => null
        ]);
    }
}
