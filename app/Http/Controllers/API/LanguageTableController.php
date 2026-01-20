<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LanguageVersionDetail;
use App\Http\Resources\LanguageTableResource;
use App\Models\LanguageList;

class LanguageTableController extends Controller
{
    public function getList(Request $request)
    {
        $version_data = LanguageVersionDetail::where('version_no',request('version_no'))->first();

        if (isset($version_data) && !empty($version_data)) {
            return json_custom_response([ 'status' => false, 'data' => [] ]);
        }

        $language_content = LanguageList::where('status','1')->orderBy('id', 'asc')->get();
        $language_version = LanguageVersionDetail::first();
        $items = LanguageTableResource::collection($language_content);

        $response = [
            'status' => true,
            'version_code' => $language_version->version_no,
            'default_language_id' => $language_version->default_language_id,
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }
    
    public function geLanguagetList(Request $request)
    {
        try {
          
            $languages = LanguageList::where('status', 1)
                                    ->orderBy('language_name')
                                    ->get();

            if ($languages->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No languages found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Language list fetched successfully.',
                'data' => $languages
            ], 200);

        } catch (\Exception $e) {
           
            \Log::error('Error fetching language list: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request.',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }
}
