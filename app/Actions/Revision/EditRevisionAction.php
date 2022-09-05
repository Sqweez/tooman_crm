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
            RevisionProducts::where('revision_id', $revision->id)
                ->where('product_id', $result['id'])
                ->update(
                    ['fact_quantity' => $result['count']]
                );
        });
        $revision->update([
            'edited_pivot_file' => 'storage/' . $filePath,
            'edited_pivot_at' => now(),
        ]);
    }

    private function parseEditFile(Spreadsheet $file): \Illuminate\Support\Collection {
        $sheet = $file->getActiveSheet();
        $rows = $sheet->getRowIterator();
        $output = collect([]);
        foreach ($rows as $key => $row) {
            if ($key > 2) {
                $output->push([
                    'id' => $sheet->getCell('A' . $key)->getValue(),
                    'count' => intval($sheet->getCell('H' . $key)->getValue()) ?: null
                ]);
            }
        }
        return $output->filter(function ($item) {
            return !is_null($item['count'] > 0);
        });
    }

}
