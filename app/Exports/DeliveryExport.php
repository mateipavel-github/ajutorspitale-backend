<?php

namespace App\Exports;

use App\Delivery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

use Metadata;
use TextHelper;
use Date;

class DeliveryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    use Exportable;

    public function __construct($delivery_ids) {
        $this->delivery_ids = $delivery_ids;
        $this->rowCount = 0;
    }

    public function headings(): array
    {
        return [
            'Nume expeditor (cine trimite)',
            'Adresa ridicare',
            'Oras',
            'Judet',
            'Persoana de contact',
            'Numar tel expeditor',
            'Nume Destinatar (cine primeste)',
            'Adresa livrare',
            'Oras',
            'Judet',
            'Persoana de contact',
            'Numar del destinatar',
            'Numar colete',
            'Dimensiuni',
            'Kg'
        ];
    }

    public function map($delivery): array
    {
        return [
            $delivery->sender_name,
            $delivery->sender_address,
            $delivery->sender_city_name,
            Metadata::getCountyById($delivery->sender_county_id)->label,
            $delivery->sender_contact_name,
            $delivery->sender_phone_number,
            $delivery->medical_unit ? $delivery->medical_unit->name : '',
            $delivery->destination_address,
            $delivery->destination_city_name,
            Metadata::getCountyById($delivery->destination_county_id)->label,
            $delivery->destination_contact_name,
            $delivery->destination_phone_number,
            $delivery->packages,
            $delivery->size,
            $delivery->weight
        ];
    }
    
    public function collection()
    {
        $list = Delivery::with('medical_unit')->whereIn('id', $this->delivery_ids)->get();
        $this->rowCount = count($list);
        return $list;
    }

    public function registerEvents(): array
    {

        $headerStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' =>  12,
                'bold' =>  true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ]
        ];

        $cellStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' =>  11,
                'bold' =>  false
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($headerStyle, $cellStyle) {
                \Log::info('WOOHOOO '.$this->rowCount);
                $headers = 'A1:O1'; // All headers
                $event->sheet->getDelegate()->getStyle($headers)->applyFromArray($headerStyle);
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(33);

                $cells = 'A2:O' . ($this->rowCount+1);
                $event->sheet->getDelegate()->getStyle($cells)->applyFromArray($cellStyle);
            },
        ];
    }
}