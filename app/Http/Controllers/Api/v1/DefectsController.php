<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Defect;
use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\DefectsRepository;

class DefectsController extends APIController
{
	private $defectRepository;

	public function __construct(DefectsRepository $defectRepository)
	{
		$this->defectRepository = new DefectsRepository;
	}

	public function resolveDefect(Request $request)
    {
    	$this->defectRepository->resolveDefect($request->all());
    	return response()->json(['message' => "Defect has been resolved successfully", "status_code" => 200], 200);
    }

    public function getDefectImages($id,Request $request) {
	    $defect = Defect::find($id);
        $images = [];
	    if ($defect) {
	        foreach ($defect->getMedia() as $media) {
                $url = getPresignedUrl($media);
                array_push($images,$url);
            }
        }

	    return response()->json(['status_code' => 200,'message' => 'Defect Images','images' => $images ]);
    }
}
