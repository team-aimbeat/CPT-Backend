<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\DietDataTable;
use App\Helpers\AuthHelper;
use App\Models\Diet;
use App\Models\DietTranslation;
use App\Models\LanguageList;

use App\Http\Requests\DietRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class DietController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DietDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.diet')] );
        $auth_user = AuthHelper::authSession();
        if( !$auth_user->can('diet-list') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $assets = ['data-table'];

        $headerAction = $auth_user->can('diet-add') ? '<a href="'.route('diet.create').'" class="btn btn-sm btn-primary" role="button">'.__('message.add_form_title', [ 'form' => __('message.diet')]).'</a>' : '';

        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if( !auth()->user()->can('diet-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = __('message.add_form_title',[ 'form' => __('message.diet')]);
        
         $languages = LanguageList::where('status', 1)
        ->orderBy('is_default', 'desc')
        ->get();

        return view('diet.form', compact('pageTitle','languages'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     
     
    
    
    
    public function store(DietRequest $request)
{
    if (!auth()->user()->can('diet-add')) {
        return redirect()->back()
            ->withErrors(__('message.permission_denied_for_account'));
    }

    /* ----------------------------
     | 1. CREATE DIET (MAIN TABLE)
     |----------------------------*/
    $dietData = $request->except(['translations', 'diet_image', 'diet_video']);
    $diet = Diet::create($dietData);

    /* ----------------------------
     | 2. IMAGE UPLOAD
     |----------------------------*/
    if ($request->hasFile('diet_image')) {

        $file = $request->file('diet_image');
        $safeName = time().'_'.Str::slug(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        ).'.'.$file->getClientOriginalExtension();

        $path = public_path('storage/uploads/diet_image');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $safeName);

        $diet->update([
            'diet_image' => 'uploads/diet_image/'.$safeName
        ]);
    }

    /* ----------------------------
     | 3. SAVE TRANSLATIONS
     |----------------------------*/
    if ($request->filled('translations')) {

        foreach ($request->translations as $langId => $translation) {

            // Skip empty rows
            if (
                empty($translation['title']) &&
                empty($translation['ingredients']) &&
                empty($translation['description'])
            ) {
                continue;
            }

            DietTranslation::create([
                'diet_id'     => $diet->id,
                'language_id' => $langId,
                'title'       => $translation['title'] ?? null,
                'ingredients' => $translation['ingredients'] ?? null,
                'description' => $translation['description'] ?? null,
            ]);
        }
    }

    return redirect()
        ->route('diet.index')
        ->withSuccess(__('message.save_form', ['form' => __('message.diet')]));
}
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Diet::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if( !auth()->user()->can('diet-edit') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        // $data = Diet::findOrFail($id);
        $pageTitle = __('message.update_form_title',[ 'form' => __('message.diet') ]);
        
        $data = Diet::with('translations')->findOrFail($id);

    $languages = LanguageList::where('status', 1)
        ->orderBy('is_default', 'desc')
        ->get();

        return view('diet.form', compact('data','id','pageTitle','languages'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     
    
    
    
    public function update(DietRequest $request, $id)
{
    if (!auth()->user()->can('diet-edit')) {
        return redirect()->back()
            ->withErrors(__('message.permission_denied_for_account'));
    }

    $diet = Diet::findOrFail($id);

    /* ----------------------------
     | 1. UPDATE MAIN DIET DATA
     |----------------------------*/
    $dietData = $request->except(['translations', 'diet_image', 'diet_video']);
    $diet->update($dietData);

    /* ----------------------------
     | 2. IMAGE UPDATE (SAFE)
     |----------------------------*/
    if ($request->hasFile('diet_image')) {

        if ($diet->diet_image) {
            $oldPath = public_path('storage/' . $diet->diet_image);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('diet_image');
        $safeName = time().'_'.Str::slug(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        ).'.'.$file->getClientOriginalExtension();

        $path = public_path('storage/uploads/diet_image');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $safeName);

        $diet->update([
            'diet_image' => 'uploads/diet_image/'.$safeName
        ]);
    }

    /* ----------------------------
     | 3. UPDATE TRANSLATIONS
     |----------------------------*/
    if ($request->filled('translations')) {

        foreach ($request->translations as $langId => $translation) {

            // Skip empty translation rows
            if (
                empty($translation['title']) &&
                empty($translation['ingredients']) &&
                empty($translation['description'])
            ) {
                continue;
            }

            DietTranslation::updateOrCreate(
                [
                    'diet_id'     => $diet->id,
                    'language_id' => $langId,
                ],
                [
                    'title'       => $translation['title'] ?? null,
                    'ingredients' => $translation['ingredients'] ?? null,
                    'description' => $translation['description'] ?? null,
                ]
            );
        }
    }

    return redirect()
        ->route('diet.index')
        ->withSuccess(__('message.update_form', ['form' => __('message.diet')]));
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
            return redirect()->route('diet.index')->withErrors($message);
        }
        if( !auth()->user()->can('diet-delete') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $diet = Diet::findOrFail($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.diet')]);

        if($diet != '') {
            $diet->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.diet')]);
        }

        if(request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message ]);
        }

        return redirect()->back()->with($status,$message);
    }
}
