<?php

namespace App\Models;

use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DataExport implements FromCollection, WithHeadings
{

    use Exportable;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function collection()
    {
        // if(!file_exists(storage_path().'/files'))
        //     mkdir(storage_path().'app/files', 777, false);
        
        return $this->data;
    }

    // public function map($row): array
    // {
    //     return [
    //         'Name',
    //         'Surname',
    //         'Email',
    //         'Twitter',
    //     ];
    // }

    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Student ID',
            'Email',
            'Grade',
            'Designated Responder ID'
        ];
    }

    public function getFileUrl($fileName){
        $fileName = 'app/files/'.$fileName;
        return url('/') ."/". Storage::disk('local')->url($fileName);
    }
}