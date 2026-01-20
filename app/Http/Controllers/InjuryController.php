<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\InjuryDataTable;
use App\Models\Injury;
use App\Helpers\AuthHelper;

use App\Http\Requests\InjuryRequest;

class InjuryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(InjuryDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.injury')] );
        $auth_user = AuthHelper::authSession();
        if( !$auth_user->can('injury-list') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $assets = ['data-table'];

        $headerAction = $auth_user->can('injury-add') ? '<a href="'.route('injury.create').'" class="btn btn-sm btn-primary" role="button">'.__('message.add_form_title', [ 'form' => __('message.injury')]).'</a>' : '';

        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if( !auth()->user()->can('injury-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $pageTitle = __('message.add_form_title',[ 'form' => __('message.injury')]);

        return view('injury.form', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InjuryRequest $request)
    {
        if( !auth()->user()->can('injury-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $injury = Injury::create($request->all());

        storeMediaFile($injury,$request->injury_image, 'injury_image'); 

        return redirect()->route('injury.index')->withSuccess(__('message.save_form', ['form' => __('message.injury')]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Injury::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if( !auth()->user()->can('injury-edit') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $data = Injury::findOrFail($id);
        $pageTitle = __('message.update_form_title',[ 'form' => __('message.injury') ]);

        return view('injury.form', compact('data','id','pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(InjuryRequest $request, $id)
    {
        if( !auth()->user()->can('injury-edit') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $injury = Injury::findOrFail($id);

        // injury data...
        $injury->fill($request->all())->update();

        // Save injury image...
        if (isset($request->injury_image) && $request->injury_image != null) {
            $injury->clearMediaCollection('injury_image');
            $injury->addMediaFromRequest('injury_image')->toMediaCollection('injury_image');
        }

        if(auth()->check()){
            return redirect()->route('injury.index')->withSuccess(__('message.update_form',['form' => __('message.injury')]));
        }
        return redirect()->back()->withSuccess(__('message.update_form',['form' => __('message.injury') ] ));

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
            return redirect()->route('injury.index')->withErrors($message);
        }
        if( !auth()->user()->can('injury-delete') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $injury = Injury::findOrFail($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.injury')]);

        if($injury != '') {
            $injury->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.injury')]);
        }

        if(request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message ]);
        }

        return redirect()->back()->with($status,$message);
    }
}
