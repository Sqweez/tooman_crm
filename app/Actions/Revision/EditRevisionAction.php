<?php

namespace App\Actions\Revision;

use App\Revision;
use App\RevisionProducts;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EditRevisionAction {

    public function handle(Revision $revision, string $filePath) {
        $file = IOFactory::load(storage_path('app/public/' . $filePath));
        $results = $this->parseEditFile($file);
        $results->each(function ($result) use ($revision) {
            \Log::info('product #' . $result['id']);
            RevisionProducts::where('revision_id', $revision->id)
                ->where('product_id', $result['id'])
                ->update(
                    ['fact_quantity' => $result['count']]
                );
        });
        $revision->update([
            'edited_pivot_file' => 'storage/' . $filePath,
            'edited_pivot_at' => now(),
            'status' => 2
        ]);
    }

    private function parseEditFile(Spreadsheet $file): \Illuminate\Support\Collection {
        $sheet = $file->getActiveSheet();
        $rows = $sheet->getRowIterator();
        $output = collect([]);
        foreach ($rows as $key => $row) {
            if ($key > 2) {
                $countText = trim($sheet->getCell('H' . $key)->getValue());
                if (strlen($countText) === 0) {
                    $count = null;
                } else {
                    $count = intval($countText);
                }
                $output->push([
                    'id' => $sheet->getCell('A' . $key)->getValue(),
                    'count' => $count,
                ]);
            }
        }
        return $output->filter(function ($item) {
            return !is_null($item['count']);
        });
    }

}
