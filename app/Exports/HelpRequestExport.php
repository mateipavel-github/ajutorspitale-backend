<?php

namespace App\Exports;

use App\HelpRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HelpRequestExport implements FromQuery, WithHeadings
{
    
    use Exportable;
    
    public function __construct($query) {
        $this->query = $query;
    }

    public function headings(): array
    {
        return [
            'nr',
            'ce',
            'nume unitate (din cerere)',
            'Tip',
            'nume oficial spital de stat',
            'contact','functie',
            'telefon din cerere',
            'primita la',
            'ultima actualizare',
            'adresa gasita de un voluntar',
            'website',
            'facebook_page',
            'Județ',
            'Link'
        ];
    }
    
    public function query()
    {
        return $this->query;
    }

}
