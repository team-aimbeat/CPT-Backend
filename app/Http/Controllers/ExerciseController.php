<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\ExerciseDataTable;
use App\Models\Exercise;
use App\Helpers\AuthHelper;
use App\Models\BodyPart;
use App\Models\ExerciseVideo;
use App\Models\EquipmentVideo;
use App\Models\Equipment;
use App\Models\LanguageList;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\ExerciseRequest;
use App\Jobs\ConvertToHLS;
use App\Jobs\TranscodeEquipmentVideo;
use App\Jobs\TranscodeExerciseVideo;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ExerciseDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.exercise')] );
        $auth_user = AuthHelper::authSession();
        if( !$auth_user->can('exercise-list') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $assets = ['data-table'];

        $headerAction = $auth_user->can('exercise-add') ? '<a href="'.route('exercise.create').'" class="btn btn-sm btn-primary" role="button">'.__('message.add_form_title', [ 'form' => __('message.exercise')]).'</a>' : '';

        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
     
     
    public function video(Exercise $exercise)  
    {
        $pageTitle = "Add Video for " . $exercise->name; 
        $languages = LanguageList::all();
        $exerciseVideos = $exercise->exerciseVideos()->with('languageList')->get(); 
        
        return view('exercise.video', compact('pageTitle', 'languages', 'exercise', 'exerciseVideos'));
    }

    public function videoList()
    {
        if (!auth()->user()->can('exercise-list')) {
            return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        }

        $pageTitle = 'Equipment Videos';
        $equipmentVideos = EquipmentVideo::with(['languageList', 'equipment'])
            ->orderByDesc('id')
            ->get();

        return view('exercise.video-list', compact('pageTitle', 'equipmentVideos'));
    }

    public function videoCreate()
    {
        if (!auth()->user()->can('exercise-add')) {
            return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        }

        $pageTitle = 'Add Equipment Video';
        $languages = LanguageList::all();

        return view('exercise.video-create', compact('pageTitle', 'languages'));
    }



    public function destroyVideo(ExerciseVideo $exerciseVideo)
    {
        $exerciseId = $exerciseVideo->exercise_id; 
        $exerciseVideo->delete();
        return redirect()->route('exercise.index', $exerciseId)
                         ->with('success', 'Exercise video deleted successfully!');
    }
     
    public function storeVideo(Request $request)
    {
        $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
            'language_id' => 'required|exists:language_lists,id', 
            'video_file' => 'required|file|mimetypes:video/*|max:512000',
        ]);

        $videoPath = $this->storeExerciseVideoFile($request->file('video_file'));
        if (empty($videoPath) || !Storage::disk('s3')->exists($videoPath)) {
            return redirect()
                ->back()
                ->withErrors('Video upload failed. Please try again.');
        }

        $exerciseVideo = ExerciseVideo::create([
            'exercise_id' => $request->exercise_id,
            'languagelist_id' => $request->language_id,
            'video_url' => $videoPath,
            'transcoding_status' => 'pending',
        ]);

        TranscodeExerciseVideo::dispatch($exerciseVideo->id, $videoPath);

        return redirect()->route('exercise.index')->with('success', 'Exercise video added successfully!');
    }

    public function destroyEquipmentVideo(EquipmentVideo $equipmentVideo)
    {
        $equipmentVideo->delete();

        return redirect()
            ->route('exercise.video.list')
            ->with('success', 'Video deleted successfully!');
    }

    public function storeEquipmentVideo(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'equipment_id' => 'required|exists:equipment,id',
            'language_id' => 'required|exists:language_lists,id',
            'video_file' => 'required|file|mimetypes:video/*|max:2048000',
        ]);

        $videoPath = $this->storeEquipmentVideoFile($request->file('video_file'));
        if (empty($videoPath) || !Storage::disk('s3')->exists($videoPath)) {
            return redirect()
                ->back()
                ->withErrors('Video upload failed. Please try again.');
        }

        $equipmentVideo = EquipmentVideo::create([
            'equipment_id' => $request->equipment_id,
            'title' => $request->title,
            'languagelist_id' => $request->language_id,
            'video_url' => $videoPath,
            'transcoding_status' => 'pending',
        ]);

        TranscodeEquipmentVideo::dispatch($equipmentVideo->id, $videoPath);

        return redirect()
            ->route('exercise.video.list')
            ->with('success', 'Equipment video added successfully!');
    }

    protected function storeEquipmentVideoFile($file)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'videos/originals/equipment/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = 'original.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
    }

    protected function storeExerciseVideoFile($file)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'videos/originals/exercise/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = 'original.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
    }
     
    public function create()
    {
        if( !auth()->user()->can('exercise-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = __('message.add_form_title',[ 'form' => __('message.exercise')]);

        $selected_equipment = [];

        return view('exercise.form', compact('pageTitle', 'selected_equipment'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   
    
   public function store(ExerciseRequest $request)
    {
        
        if( !auth()->user()->can('exercise-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
    
        $data = $request->all();
    
        $exercise = Exercise::create($data);
        
       
        
        if ($request->hasFile('exercise_image')) {
            $path = $this->storeExerciseAsset($request->file('exercise_image'), 'images');
            $exercise->exercise_image = $path;
            $exercise->save();
        }
        
        
        
        if (request()->has('exercise_video') && !empty(request('exercise_video'))) {
            $exercise->video_url = request('exercise_video');
            $exercise->save();
        }
    
    
        
        
        if ($request->hasFile('primary_video')) {
            $path = $this->storeExerciseGifVideoFile($request->file('primary_video'));
            $exercise->exercise_gif = $path;
            $exercise->exercise_gif_transcoding_status = 'pending';
            $exercise->save();
            TranscodeExerciseVideo::dispatch($exercise->id, $path, 'exercise_gif');
        }
    
    
    
    
    
        return redirect()->route('exercise.index')->withSuccess(__('message.save_form', ['form' => __('message.exercise')]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Exercise::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if( !auth()->user()->can('exercise-edit') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $data = Exercise::findOrFail($id);
        $pageTitle = __('message.update_form_title',[ 'form' => __('message.exercise') ]);

        $selected_equipment = [];
        if (isset($data->equipment_id)) {
            $selected_equipment = Equipment::where('id', $data->equipment_id)->get()->mapWithKeys(function ($item) {
                return [ $item->id => $item->title ];
            });
        }
        return view('exercise.form', compact('data','id','pageTitle','selected_equipment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ExerciseRequest $request, $id)
    {
    
        if (!auth()->user()->can('exercise-edit')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
    
        $exercise = Exercise::findOrFail($id);
    
        $data = $request->all();
    
        if (!isset($data['exercise_gif'])) {
            $data['exercise_gif'] = $exercise->exercise_gif;
        }
    
        $exercise->update($data);
    
        if ($request->hasFile('exercise_image')) {
            if (!empty($exercise->exercise_image)) {
                Storage::disk('s3')->delete($exercise->exercise_image);
            }

            $path = $this->storeExerciseAsset($request->file('exercise_image'), 'images');
            $exercise->exercise_image = $path;
            $exercise->save();
        }
    
       
        if (request()->has('exercise_video') && !empty(request('exercise_video'))) {
            $exercise->video_url = request('exercise_video');
            $exercise->save();
        }
    
        if ($request->hasFile('primary_video')) {
            if (!empty($exercise->exercise_gif)) {
                Storage::disk('s3')->delete($exercise->exercise_gif);
            }

            $path = $this->storeExerciseGifVideoFile($request->file('primary_video'));
            $exercise->exercise_gif = $path;
            $exercise->exercise_gif_transcoding_status = 'pending';
            $exercise->save();
            TranscodeExerciseVideo::dispatch($exercise->id, $path, 'exercise_gif');
        }
    
    
    
    
        return redirect()->route('exercise.index')->withSuccess(__('message.update_form', ['form' => __('message.exercise')]));
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
            return redirect()->route('exercise.index')->withErrors($message);
        }
        if( !auth()->user()->can('exercise-delete') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $exercise = Exercise::findOrFail($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.exercise')]);

        if($exercise != '') {
            $exercise->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.exercise')]);
        }

        if(request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message ]);
        }

        return redirect()->back()->with($status,$message);
    }

    protected function storeExerciseAsset($file, $type)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'images/exercise/' . $type . '/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = 'file.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
    }

    protected function storeExerciseGifVideoFile($file)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'videos/originals/exercise_gif/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = 'original.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
    }
}
