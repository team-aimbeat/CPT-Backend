<?php

namespace App\DataTables;

use App\Models\UserExercise;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use App\Traits\DataTableTrait;
use Carbon\Carbon;

class UserExerciseDataTable extends DataTable
{
    use DataTableTrait;
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
            ->addColumn('exercise_image', function ($item) {
                return '<img src="'+getSingleMedia($item->exercise, 'exercise_image', null)+'" class="rounded-pill avatar-130 img-fluid"  alt="exercise-img">';
            })
            ->rawColumns(['exercise_image']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $model = UserExercise::where('user_id', $this->user_id)->with('exercise')
                                ->orderBy('created_at', 'desc');
        return $this->applyScopes($model);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            
            // ['data' => 'id', 'name' => 'id', 'title' =>  __('message.id')],
            ['data' => 'exercise_image', 'name' => 'exercise_image', 'title' => __('message.image')],
            ['data' => 'exercise.title', 'name' => 'exercise.title', 'title' => __('message.name')],
            // ['data' => 'status', 'name' => 'status', 'title' => __('message.status')], 
            ['data' => 'exercise.duration', 'name' => 'exercise.duration', 'title' => __('message.duration')], 
            // ['data' => 'sets', 'name' => 'sets', 'title' => __('message.sets')], 
            // ['data' => 'based', 'name' => 'based', 'title' => __('message.status')], 
            // ['data' => 'type', 'name' => 'type', 'title' => __('message.status')], 
            ['data' => 'created_at', 'name' => 'created_at', 'title' => __('message.created_at')],
            // ['data' => 'updated_at', 'name' => 'updated_at', 'title' => __('message.updated_at')],

            // Column::computed('action')
            //       ->exportable(false)
            //       ->printable(false)
            //       ->title(__('message.action'))
            //       ->searchable(false)
            //       ->width(60)
            //       ->addClass('text-center hide-search'),
        ];
        
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('exercise-datatable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(
                        route('user.exercise.list'),
                        null,
                        ['user_id' => $this->user_id]
                        ) // Define the route here
                    ->parameters($this->getBuilderParameters());
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Users_' . date('YmdHis');
    }
}
