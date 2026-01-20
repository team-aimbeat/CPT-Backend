<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\SubscriberDataTable;
use App\Models\Level;
use App\Helpers\AuthHelper;

use App\Http\Requests\LevelRequest;

class SubscriberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SubscriberDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.subscribers')] );
        $auth_user = AuthHelper::authSession();
        if( !$auth_user->can('subscribers-list') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $assets = ['data-table'];

        $headerAction = '';

        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

}
