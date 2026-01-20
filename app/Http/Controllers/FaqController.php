<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\FaqDataTable;
use App\Models\Faq;
use App\Helpers\AuthHelper;

use App\Http\Requests\FaqRequest;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(FaqDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.faq')] );
        $auth_user = AuthHelper::authSession();
        if( !$auth_user->can('faq-list') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $assets = ['data-table'];

        $headerAction = $auth_user->can('faq-add') ? '<a href="'.route('faqs.create').'" class="btn btn-sm btn-primary" role="button">'.__('message.add_form_title', [ 'form' => __('message.faq')]).'</a>' : '';

        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if( !auth()->user()->can('faq-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = __('message.add_form_title',[ 'form' => __('message.faq')]);

        return view('faq.form', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FaqRequest $request)
    {
        if( !auth()->user()->can('faq-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        Faq::create($request->all());

        return redirect()->route('faqs.index')->withSuccess(__('message.save_form', ['form' => __('message.faq')]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Faq::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if( !auth()->user()->can('faq-edit') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $data = Faq::findOrFail($id);
        $pageTitle = __('message.update_form_title',[ 'form' => __('message.faq') ]);

        return view('faq.form', compact('data','id','pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FaqRequest $request, $id)
    {
        if( !auth()->user()->can('faq-edit') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $faq = Faq::findOrFail($id);

        // faq data...
        $faq->fill($request->all())->update();

        if(auth()->check()){
            return redirect()->route('faqs.index')->withSuccess(__('message.update_form',['form' => __('message.faq')]));
        }
        return redirect()->back()->withSuccess(__('message.update_form',['form' => __('message.faq') ] ));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(env('APP_DEMO')){
            $message = __('message.demo_permission_denied');
            return redirect()->route('faqs.index')->withErrors($message);
        }
        if( !auth()->user()->can('faq-delete') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $faq = Faq::findOrFail($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.faq')]);

        if($faq != '') {
            $faq->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.faq')]);
        }

        if(request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message ]);
        }

        return redirect()->back()->with($status,$message);
    }
}
