<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class EarthDataDetailsExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        private readonly Collection $rows,
        private readonly string $sheetTitle = '土單使用明細'
    ) {}

    public function collection(): Collection
    {
        return $this->rows->map(function ($r) {
            return [
                'Barcode'        => (string)($r->barcode ?? ''),
                '列印時間'          => (string)($r->print_at ?? ''),
                '核銷時間'          => (string)($r->verified_at ?? ''),
                '核銷人員'          => (string)($r->verified_by_name ?? ''),
                '建立時間'          => (string)($r->created_at ?? ''),
            ];
        });
    }

    public function headings(): array
    {
        return ['Barcode', '列印時間', '核銷時間', '核銷人員', '建立時間'];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
