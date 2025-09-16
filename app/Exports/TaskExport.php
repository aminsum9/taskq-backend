<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TaskExport implements FromCollection, WithHeadings, WithEvents
{
    protected $totalTask;
    protected $totalTimeTracked;

    public function __construct()
    {
        $this->totalTask = Task::count();
        $this->totalTimeTracked = Task::sum('time_tracked');
    }

    public function collection()
    {
        $data = Task::select('title','assignee','due_date','time_tracked','status','priority')->get();

        $data = collect($data)->map(function($item) {
            $item->time_tracked = $item->time_tracked == '0' ? 0 : $item->time_tracked;
            return $item;
        });
        return $data;
    }

    public function headings(): array
    {
        return [
            'Title','Assignee','Due Date','Time Tracked','Status','Priority'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $rowCount = Task::count() + 2; 

                $event->sheet->setCellValue('E' . $rowCount, 'TOTAL TASK');
                $event->sheet->setCellValue('F' . $rowCount, $this->totalTask);

                $event->sheet->getStyle('E' . $rowCount . ':F' . $rowCount)
                    ->applyFromArray([
                        'font' => ['bold' => true],
                    ]);

                $rowCount2 = Task::count() + 3; 

                $event->sheet->setCellValue('E' . $rowCount2, 'TOTAL TIME TRACKED');
                $event->sheet->setCellValue('F' . $rowCount2, $this->totalTimeTracked);

                 $event->sheet->getStyle('E' . $rowCount2 . ':F' . $rowCount2)
                    ->applyFromArray([
                        'font' => ['bold' => true],
                    ]);
            },
        ];
    }
}