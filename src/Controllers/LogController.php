<?php

namespace OpenAdminCore\Admin\Controllers;

use Illuminate\Support\Arr;
use OpenAdminCore\Admin\Auth\Database\OperationLog;
use OpenAdminCore\Admin\Grid;

class LogController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.operation_log');
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OperationLog());

        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.name', trans('admin.name'));
        $grid->column('method', trans('admin.http.method'))->display(function ($method) {
            $color = Arr::get(OperationLog::$methodColors, $method, 'grey');

            return "<span class=\"badge bg-$color\">$method</span>";
        })->sortable();
        $grid->column('path', trans('admin.http.path'))->label('info')->sortable();
        $grid->column('ip')->label('primary')->sortable();
        $grid->column('input', trans('admin.input'))->display(function ($input) {
            $input = json_decode($input, true);
            $input = Arr::except($input, ['_pjax', '_token', '_method', '_previous_']);
            if (empty($input)) {
                return '<code>{}</code>';
            }

            return '<pre>'.json_encode($input, JSON_PRETTY_PRINT | JSON_HEX_TAG).'</pre>';
        })->sortable();

        $grid->column('created_at', trans('admin.created_at'))->sortable();

        $grid->actions(function (Grid\Displayers\Actions\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $userModel = config('admin.database.users_model');

            $filter->equal('user_id', 'User')->select($userModel::all()->pluck('name', 'id'));
            $filter->equal('method')->select(array_combine(OperationLog::$methods, OperationLog::$methods));
            $filter->like('path');
            $filter->equal('ip');
        });

        return $grid;
    }

    /**
     * @param mixed $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $ids = explode(',', $id);

        if (OperationLog::destroy(array_filter($ids))) {
            $data = [
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ];
        } else {
            $data = [
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ];
        }

        return response()->json($data);
    }
}
