<?php

namespace App\DataTables;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;




class ApprovedClientsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        if(Auth::user()->hasRole('admin')){
            return datatables()
                ->eloquent($query)
                ->addColumn('receptionist', function ($data){
                    return $this->getReceptionist($data);
                });
        }
        return datatables()
            ->eloquent($query);
    }





    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ApprovedClient $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Client $model)
    {
        $is_receptionist = Auth::user()->hasRole('receptionist') ;
        return $model->newQuery()->with('user')->select('clients.*')
                ->where('approval',"=" ,true)
                ->when($is_receptionist,function($query){
                        return $query->where('receptionist_id',Auth::user()->receptionist->id);
                });
    }



    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('client-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        if(Auth::user()->hasRole('admin')){
            return [
                Column::make('user.name')->title("Name"),
                Column::make('user.email')->title("Email"),
                Column::make('mobile')->title("Mobile"),
                Column::make('country')->title("Country"),
                Column::make('gender')->title("Gender"),
                Column::computed('receptionist')
                    ->exportable(true)
                    ->printable(true)
                    ->width(60)
                    ->addClass('text-center'),
        ];

        }

        return [
            Column::make('user.name')->title("Name"),
            Column::make('user.email')->title("Email"),
            Column::make('mobile')->title("Mobile"),
            Column::make('country')->title("Country"),
            Column::make('gender')->title("Gender"),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'ApprovedClients_' . date('YmdHis');
    }

    protected function getReceptionist($data){
        return $data->receptionist->user->name;
    }

}
