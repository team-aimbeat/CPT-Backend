<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WorkoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $method = strtolower($this->method());

        $rules = [];
        switch ($method) {
            case 'post':
                $rules = [
                    'title' => 'required',
                    'workout_days_plan' => 'required|in:3,6',
                    'video_url' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v,video/webm|max:512000',
                    'stetch_video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v,video/webm|max:512000',
                ];
                break;
            case 'patch':
                $rules = [
                    'title' => 'required',
                    'workout_days_plan' => 'required|in:3,6',
                    'video_url' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v,video/webm|max:512000',
                    'stetch_video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v,video/webm|max:512000',
                ];
                break;
        }

        return $rules;
    }

    public function messages()
    {
        return [ ];
    }
    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status' => true,
            'message' => $validator->errors()->first(),
            'all_message' =>  $validator->errors()
        ];

        if ( request()->is('api*')){
           throw new HttpResponseException( response()->json($data,422) );
        }

        if ($this->ajax()) {
            throw new HttpResponseException(response()->json($data,422));
        } else {
            throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
        }
    }
}
