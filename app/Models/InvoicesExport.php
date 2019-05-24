<?php

namespace App\Models;

use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class InvoicesExport implements FromCollection, WithHeadings
{

    use Exportable;

    public function __construct($data = [],$importName)
    {
        $this->data = $data;
        $this->importName = $importName;
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
        if($this->importName == "LocalResources")
        {
            return [
                'Name',
                'Description Of Service(s)',
                'Insurance Type(s)',
                'Phone Number',
                'Website',
                'Street Address',
                'City',
                'State',
                'Zip',
                'Error Description'
            ];
        }
        elseif($this->importName == "CrisisResources")
        {
            return [
                'Crisis Resource Name',
                'Description Of Service(s)',
                'Phone Number',
                'Type(Hotline/Textline)',
                'Website',
                'Error Description',
            ];
        }
        elseif($this->importName == "Responders")
        {
            return [
                'Title',
                'Responder First Name',
                'Responder Last Name',
                'Primary Email',
                'Employee ID',
                'Position/Role',
                'Responder Level (1/2/3)',
                'Error Description',
            ];
        }
        elseif($this->importName == "Students")
        {
            return [
                'Student First Name',
                'Student Last Name',
                'Grade/Year Level',
                'Email',
                'Student ID',
                'Designated Responder ID',
                'Error Description',
            ];
        }


        
    }

    public function getFileUrl($fileName){
        $fileName = 'app/files/'.$fileName;
        return url('/') ."/". Storage::disk('local')->url($fileName);
    }
}