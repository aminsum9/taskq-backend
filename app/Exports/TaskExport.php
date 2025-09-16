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

    public function __construct($title = null,$assignee = null,$start_due_date = null,$end_due_date = null,$min_time_tracked = null,$max_time_tracked = null,$status = null,$priority = null)
    {
        $this->totalTask = Task::count();
        $this->totalTimeTracked = Task::sum('time_tracked');
        $this->title = $title;
        $this->assignee = $assignee;
        $this->start_due_date = $start_due_date;
        $this->end_due_date = $end_due_date;
        $this->min_time_tracked = $min_time_tracked;
        $this->max_time_tracked = $max_time_tracked;
        $this->status = $status;
        $this->priority = $priority;
    }

    public function collection()
    {
        $data = Task::select('title','assignee','due_date','time_tracked','status','priority');

        if($this->title){
            $data = $$data->where('title','LIKE','%'.$this->title.'%');
        }

        if($this->assignee){
            $assignees = explode(",",$this->assignee);
            $data = $$data->whereInArray('assignee',$assignees);
        }

        if($this->start_due_date){
            $data = $$data->where('due_date','>',$this->start_due_date);
        }
        if($this->end_due_date){
            $data = $$data->where('due_date','<',$this->end_due_date);
        }

        if($this->min_time_tracked){
            $data = $$data->where('time_tracked','>',$this->min_time_tracked);
        }
        if($this->max_time_tracked){
            $data = $$data->where('time_tracked','<',$this->max_time_tracked);
        }

        if($this->status){
            $status = explode(",",$this->status);
            $data = $$data->whereInArray('status',$status);
        }

        if($this->priority){
            $priority = explode(",",$this->priority);
            $data = $$data->whereInArray('priority',$priority);
        }


        $data = $data->get();

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