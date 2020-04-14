<?php

namespace App\Exports;

use App\HelpRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use TextHelper;
use Date;

class HelpRequestExport implements FromQuery, WithHeadings, WithMapping
{
    
    use Exportable;
    
    public function __construct($query) {
        $this->query = $query;
    }

    public function headings(): array
    {
        return [
            'nr', //a
            'ce', //b
            'nume unitate (din cerere)', //c
            'Tip', //d
            'nume oficial spital de stat', //e
            'contact', //f
            'functie', //g
            'telefon din cerere', //h
            'primita la', //i
            'ultima actualizare', //j
            'adresa gasita de un voluntar', //k
            'website', //l
            'facebook_page', //m
            'Judet', //n
            'Link' //o
        ];
    }

    public function map($item): array {
        return [
            $item->a,
            TextHelper::englishCharactersOnly($item->b),
            TextHelper::englishCharactersOnly($item->c),
            TextHelper::englishCharactersOnly($item->d),
            TextHelper::englishCharactersOnly($item->e),
            TextHelper::englishCharactersOnly($item->f),
            TextHelper::englishCharactersOnly($item->g),
            "'".$item->h,
            $item->i,
            $item->j,
            TextHelper::englishCharactersOnly($item->k),
            TextHelper::englishCharactersOnly($item->l),
            TextHelper::englishCharactersOnly($item->m),
            TextHelper::englishCharactersOnly($item->n),
            $item->o
        ];
    }
    
    public function query()
    {
        return $this->query;
    }

}
