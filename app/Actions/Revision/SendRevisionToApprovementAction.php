<?php

namespace App\Actions\Revision;

use App\Revision;
use App\RevisionProducts;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SendRevisionToApprovementAction {

    /**
     * @throws Exception
     */
    public function handle(Revision $revision, $filePath) {
        str_replace('storage/', '', $filePath);
        $excelFile = $this->loadFile($filePath);
        $results = $this->parseRevisionFile($excelFile);
        $results->each(function ($result) use ($revision) {
            Log::info('Product id: #' . $result['id']);
            RevisionProducts::where('revision_id', $revision->id)
                ->where('product_id', $result['id'])
                ->where('fact_quantity', '!=', $result['count'])
                ->update(
                    ['fact_quantity' => $result['count']]
                );
        });

        $revision->update([
            'original_loaded_revision_file' => 'storage/' . str_replace('public/', '', $filePath),
            'revision_sent_to_approve_at' => now(),
            'status' => Revision::STATUS_ON_APPROVE,
        ]);
    }

    /**
     * @throws Exception
     */
    private function parseRevisionFile(Spreadsheet $file): \Illuminate\Support\Collection {
        $sheet = $file->getActiveSheet();
        $rows = $sheet->getRowIterator();
        $output = collect([]);
        foreach ($rows as $key => $row) {
            if ($key > 2) {
                $output->push([
                    'id' => $sheet->getCell('A' . $key)->getValue(),
                    'count' => intval($sheet->getCell('E' . $key)->getValue()) ?: 0
                ]);
            }
        }
        return $output;
    }

    private function loadFile(string $filePath): Spreadsheet {
        $path = 'app/' . $filePath;
        $file = storage_path($path);
        return IOFactory::load($file);
    }
}
