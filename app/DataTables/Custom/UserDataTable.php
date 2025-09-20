<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\User;

class UserDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
            //
    ];

    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id'),
            Column::create('full_name'),
            Column::create('phone'),
            Column::create('phone_verified_at'),
            Column::create('national_id')->setTitle('National ID'),
            Column::create('is_active'),
            // Column::create('is_registered'),
            Column::create('birth_date'),
            Column::create('email'),
            // Column::create('email_verified_at'),
            // Column::create('password'),
            Column::create('active_profile_type'),
            // Column::create('remember_token'),
            Column::create('created_at'),
            Column::create('updated_at'),
            Column::create('action'),
        ];
    }

        /**
     * Get the filters for the DataTable.
     *
     * @return array
     */
    public function filters()
    {
        return [
            'created_at' => Filter::date('Created Date','now'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = User::query();

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.user.columns._actions', compact('model'));
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->editColumn('updated_at', function ($model) {
                return $model->updated_at->format('Y-m-d H:i:s');
            })
            ->filter(fn ($query) => $this->applySearch($query))
            ->make(true);
    }
}
