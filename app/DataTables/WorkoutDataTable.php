<?php

namespace App\DataTables;

use App\Models\Workout;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use App\Traits\DataTableTrait;

class WorkoutDataTable extends DataTable
{
    use DataTableTrait;

    protected function formatNumberSummary($values, string $prefix = ''): string
    {
        $values = collect($values)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->sort()
            ->values();

        if ($values->isEmpty()) {
            return '-';
        }

        return $values
            ->map(fn ($value) => $prefix . $value)
            ->implode(', ');
    }

    protected function formatWorkoutSchedule($workout): string
    {
        $days = $workout->workoutDay ?? collect();

        if ($days->isEmpty()) {
            return '-';
        }

        return $days
            ->map(function ($day) {
                $month = (int) ($day->month_no ?? 1);
                $week = (int) ($day->week ?? 0);
                $dayNo = (int) ($day->day ?? 0);

                if ($week < 1 || $dayNo < 1) {
                    return null;
                }

                return "M{$month} / W{$week} / D{$dayNo}";
            })
            ->filter()
            ->unique()
            ->values()
            ->implode('<br>');
    }
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('status', function($query) {
                $status = 'warning';
                switch ($query->status) {
                    case 'active':
                        $status = 'primary';
                        break;
                    case 'inactive':
                        $status = 'warning';
                        break;
                }
                return '<span class="text-capitalize badge bg-'.$status.'">'.$query->status.'</span>';
            })
            ->editColumn('level.title', function($query) {
                return optional($query->level)->title ?? '-';
            })
            ->filterColumn('level.title', function($query, $keyword) {
                return $query->orWhereHas('level', function($q) use($keyword) {
                    $q->where('title', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('workout_type.title', function($query) {
                return optional($query->workouttype)->title ?? '-';
            })
            ->addColumn('month_no_summary', function ($query) {
                return $this->formatNumberSummary(
                    optional($query->workoutDay)->pluck('month_no')->all(),
                    'M'
                );
            })
            ->addColumn('week_summary', function ($query) {
                return $this->formatNumberSummary(
                    optional($query->workoutDay)->pluck('week')->all(),
                    'W'
                );
            })
            ->addColumn('day_summary', function ($query) {
                return $this->formatNumberSummary(
                    optional($query->workoutDay)->pluck('day')->all(),
                    'D'
                );
            })
            ->addColumn('schedule_summary', function ($query) {
                return $this->formatWorkoutSchedule($query);
            })
            ->filterColumn('workout_type.title', function($query, $keyword) {
                return $query->orWhereHas('workouttype', function($q) use($keyword) {
                    $q->where('title', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('created_at', function($query){
                return dateAgoFormate($query->created_at, true); 
            })
            ->editColumn('updated_at', function($query){
                return dateAgoFormate($query->updated_at, true);
            })
            ->addColumn('action', function($workout){
                $id = $workout->id;
                return view('workout.action',compact('workout','id'))->render();
            })
            ->addIndexColumn()
            ->order(function ($query) {
                if (request()->has('order')) {
                    $order = request()->order[0];
                    $column_index = $order['column'];

                    $column_name = 'id';
                    $direction = 'desc';
                    if( $column_index != 0) {
                        $column_name = request()->columns[$column_index]['data'];
                        $direction = $order['dir'];
                    }
    
                    $query->orderBy($column_name, $direction);
                }
            })
            ->rawColumns(['action','status', 'schedule_summary']);
    }

    public function dataTableForGrid()
    {
        return datatables(Workout::query())
                ->addColumn('img', function($data){
                    return view('workout.image_list',compact('data'))->render();
                })
                ->editColumn('status', function($query) {
                    $status = 'warning';
                    switch ($query->status) {
                        case 'active':
                            $status = 'primary';
                            break;
                        case 'inactive':
                            $status = 'warning';
                            break;
                    }
                    return '<span class="text-capitalize badge bg-'.$status.'">'.$query->status.'</span>';
                })
                ->editColumn('level.title', function($query) {
                    return optional($query->level)->title ?? '-';
                })
                ->filterColumn('level.title', function($query, $keyword) {
                    return $query->orWhereHas('level', function($q) use($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('workout_type.title', function($query) {
                    return optional($query->workouttype)->title ?? '-';
                })
                ->addColumn('month_no_summary', function ($query) {
                    return $this->formatNumberSummary(
                        optional($query->workoutDay)->pluck('month_no')->all(),
                        'M'
                    );
                })
                ->addColumn('week_summary', function ($query) {
                    return $this->formatNumberSummary(
                        optional($query->workoutDay)->pluck('week')->all(),
                        'W'
                    );
                })
                ->addColumn('day_summary', function ($query) {
                    return $this->formatNumberSummary(
                        optional($query->workoutDay)->pluck('day')->all(),
                        'D'
                    );
                })
                ->addColumn('schedule_summary', function ($query) {
                    return $this->formatWorkoutSchedule($query);
                })
                ->filterColumn('workout_type.title', function($query, $keyword) {
                    return $query->orWhereHas('workouttype', function($q) use($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('created_at', function($query){
                    return dateAgoFormate($query->created_at, true); 
                })
                ->editColumn('updated_at', function($query){
                    return dateAgoFormate($query->updated_at, true);
                })
                ->addColumn('action', function($workout){
                    $id = $workout->id;
                    return view('workout.action',compact('workout','id'))->render();
                })
                ->addIndexColumn()
                ->order(function ($query) {
                    if (request()->has('order')) {
                        $order = request()->order[0];
                        $column_index = $order['column'];

                        $column_name = 'id';
                        $direction = 'desc';
                        if( $column_index != 0) {
                            $column_name = request()->columns[$column_index]['data'];
                            $direction = $order['dir'];
                        }

                        $query->orderBy($column_name, $direction);
                    }
                })
                ->rawColumns(['action','status', 'img', 'schedule_summary']);
    
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Workout $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Workout $model)
    {
        return $model->newQuery()->with([
            'level:id,title',
            'workouttype:id,title',
            'workoutDay:id,workout_id,month_no,week,day,sequence',
        ]);
    }


    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')
            ->searchable(false)
            ->title(__('message.srno'))
            ->orderable(false),
            ['data' => 'title', 'name' => 'title', 'title' => __('message.title')],
            ['data' => 'level.title', 'name' => 'level.title', 'title' => __('message.level'), 'orderable' => false],   
            ['data' => 'workout_type.title', 'name' => 'workout_type.title', 'title' => __('message.workouttype'), 'orderable' => false],  
            ['data' => 'month_no_summary', 'name' => 'month_no_summary', 'title' => 'Month', 'orderable' => false, 'searchable' => false],
            ['data' => 'week_summary', 'name' => 'week_summary', 'title' => 'Week', 'orderable' => false, 'searchable' => false],
            ['data' => 'day_summary', 'name' => 'day_summary', 'title' => 'Day', 'orderable' => false, 'searchable' => false],
            ['data' => 'schedule_summary', 'name' => 'schedule_summary', 'title' => 'Month / Week / Day', 'orderable' => false, 'searchable' => false],
            ['data' => 'status', 'name' => 'status', 'title' => __('message.status')],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => __('message.created_at')],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => __('message.updated_at')],
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->title(__('message.action'))
                  ->width(60)
                  ->addClass('text-center hide-search'),
        ];
    }

    public function getColumnsForGrid()
    {
        return [
            ['data' => 'title', 'name' => 'title', 'title' => __('message.title')],
            ['data' => 'level.title', 'name' => 'level.title', 'title' => __('message.level'), 'orderable' => false],   
            ['data' => 'workout_type.title', 'name' => 'workout_type.title', 'title' => __('message.workouttype'), 'orderable' => false],  
            ['data' => 'month_no_summary', 'name' => 'month_no_summary', 'title' => 'Month', 'orderable' => false],
            ['data' => 'week_summary', 'name' => 'week_summary', 'title' => 'Week', 'orderable' => false],
            ['data' => 'day_summary', 'name' => 'day_summary', 'title' => 'Day', 'orderable' => false],
            ['data' => 'schedule_summary', 'name' => 'schedule_summary', 'title' => 'Month / Week / Day', 'orderable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => __('message.created_at')],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => __('message.updated_at')],
            ['data' => 'status', 'name' => 'status', 'title' => __('message.status')],
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->title(__('message.action'))
                  ->width(60)
                  ->addClass('text-center hide-search'),
            
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Workout_' . date('YmdHis');
    }
}
