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
            'video_url' => 'required',
        ]);

        ExerciseVideo::create([
            'exercise_id' => $request->exercise_id,
            'languagelist_id' => $request->language_id,
            'video_url' => $request->video_url,
        ]);

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
            'equipment_id' => 'required|exists:equipment,id',
            'language_id' => 'required|exists:language_lists,id',
            'video_file' => 'required',
        ]);

        $videoPath = $this->storeEquipmentVideoFile($request->file('video_file'));

        $equipmentVideo = EquipmentVideo::create([
            'equipment_id' => $request->equipment_id,
            'languagelist_id' => $request->language_id,
            'video_url' => $videoPath,
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
     
    public function create()
    {
        if( !auth()->user()->can('exercise-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = __('message.add_form_title',[ 'form' => __('message.exercise')]);

        return view('exercise.form', compact('pageTitle'));
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
       
       
        
      
        if( request('type') == 'duration' ) {
            if(request('hours') != null && request('minute') != null && request('second') != null ) {
                $data['duration'] = request('hours').':'.request('minute').':'.request('second') ?? null;
                $data['based'] = null;
                $data['sets'] = null;
            }
        }
    
        
        if( request('type') == 'sets' ) {
            if(request('reps') != null && !in_array(null, request('reps')) ) {
                $save_data = [];
                foreach($request->reps as $i => $value) {
                    $save_data[] = [
                        'set' => $request->set[$i] ?? null,
                        'reps' => $value,
                        'weight' => $request->weight[$i] != null ? $request->weight[$i] : null,
                        'note' => $request->note[$i] ?? null,
                    ];
                }
                $data['sets'] = $save_data;
                $data['duration'] = null;
            }
        }
    
     
        unset($data['set']);
        unset($data['weight']);
        unset($data['reps']);
        unset($data['note']);    
        unset($data['hours']);
        unset($data['minute']);
        unset($data['second']);
    
        $exercise = Exercise::create($data);
        
       
        
         if ($request->hasFile('exercise_image')) {
        $file = $request->file('exercise_image');
    
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
    
        $exercise->exercise_image = 'uploads/exercise_gif/' . $filename;
        $exercise->save();
    }
        
        
        
    //     if ($request->hasFile('exercise_video')) {
    
    //     $file = $request->file('exercise_video');
    //     $filename = time() . '_' . $file->getClientOriginalName();
    
    //     $targetFolder = public_path('storage/uploads/exercise_gif');
    
    //     if (!file_exists($targetFolder)) {
    //         mkdir($targetFolder, 0755, true);
    //     }
    
    //     $file->move($targetFolder, $filename);
    
    //     $exercise->video_url = 'uploads/exercise_gif/' . $filename;
    //     $exercise->save();
    // }
    
    if (request()->has('exercise_video') && !empty(request('exercise_video'))) {
    
    $youtube_url_or_id = request('exercise_video');
  
    // $youtube_id = $this->extractYoutubeId($youtube_url_or_id);
    
    $exercise->video_url = $youtube_url_or_id; 
    $exercise->save();
}
    
    
        
        
        if ($request->hasFile('primary_video')) {
        $file = $request->file('primary_video');
    
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
    
        $exercise->exercise_gif = 'uploads/exercise_gif/' . $filename;
        $exercise->save();
    }
    
    
    
     if ($request->hasFile('english_video')) {
        $file = $request->file('english_video');
    
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
    
        $exercise->english_video = 'uploads/exercise_gif/' . $filename;
        $exercise->save();
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

        $selected_bodypart = [];
        if(isset($data->bodypart_ids)) {
            $selected_bodypart = BodyPart::whereIn('id', $data->bodypart_ids)->get()->mapWithKeys(function ($item) {
                return [ $item->id => $item->title ];
            });
        }
        return view('exercise.form', compact('data','id','pageTitle','selected_bodypart'));
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
    
        $data['equipment_id'] = $request->input('equipment_id') ?? null;
        $data['exercise_id']  = $request->input('exercise_id') ?? null;
    
        if ($request->input('type') === 'duration') {
            if ($request->filled('hours') && $request->filled('minute') && $request->filled('second')) {
                $data['duration'] = $request->hours . ':' . $request->minute . ':' . $request->second;
                $data['sets'] = null;
                $data['based'] = null;
            }
        }
    
        if ($request->input('type') === 'sets') {
            if ($request->reps != null && !in_array(null, $request->reps)) {
                $save_data = []; 
                foreach ($request->reps as $i => $value) {
                    $save_data[] = [
                        'set'    => $request->set[$i] ?? null,
                        'reps'   => $value,
                        'weight' => $request->weight[$i] ?? null,
                        'note'   => $request->note[$i] ?? null,
                    ];
                }
                $data['sets']     = $save_data;
                $data['duration'] = null;
            }
        }
    
        unset($data['set'], $data['weight'], $data['reps'], $data['note'], $data['hours'], $data['minute'], $data['second']);
    
        if (!isset($data['exercise_gif'])) {
            $data['exercise_gif'] = $exercise->exercise_gif;
        }
    
        $exercise->update($data);
    
        if ($request->hasFile('exercise_image')) {
            if ($exercise->exercise_image) {
                $oldFile = public_path('storage/' . $exercise->exercise_image); 
                if (file_exists($oldFile)) {
                    unlink($oldFile); 
                }
            }
    
            $file = $request->file('exercise_image');
    
            $filename = time() . '_' . $file->getClientOriginalName();
    
            $targetFolder = public_path('storage/uploads/exercise_gif');
    
            $file->move($targetFolder, $filename);
    
            $exercise->exercise_image = 'uploads/exercise_gif/' . $filename;
            $exercise->save(); 
        }
    
       
        if ($request->hasFile('exercise_video')) {
        if ($exercise->video_url) {
            $oldFile = public_path('storage/' . $exercise->exercise_gif);
            if (file_exists($oldFile)) {
                unlink($oldFile); 
            }
        }
    
        $file = $request->file('exercise_video');
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
    
        $exercise->video_url = 'uploads/exercise_gif/' . $filename;
        $exercise->save();
    }
    
        if ($request->hasFile('primary_video')) {
        if ($exercise->exercise_gif) {
            $oldFile = public_path('storage/' . $exercise->exercise_gif);
            if (file_exists($oldFile)) {
                unlink($oldFile); 
            }
        }
    
        $file = $request->file('primary_video');
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
        $exercise->exercise_gif = 'uploads/exercise_gif/' . $filename;
        $exercise->save();
    }
    
    
     if ($request->hasFile('english_video')) {
        if ($exercise->english_video) {
            $oldFile = public_path('storage/' . $exercise->english_video);
            if (file_exists($oldFile)) {
                unlink($oldFile); 
            }
        }
    
        $file = $request->file('english_video');
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
        $exercise->english_video = 'uploads/exercise_gif/' . $filename;
        $exercise->save();
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
}
