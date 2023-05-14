<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use View;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Defect;
use App\Traits\HasMediaUploads;
use Carbon\Carbon as Carbon;

class ImageController extends Controller
{
    use HasMediaUploads;

    public function __construct() {
        
    }

    public function uploadMedia(Request $request)
    {
        $authUser = Auth::user();
        $contents = $request->get('image_string');
        if (! starts_with($contents, 'data:image/')) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Invalid image data.');
        }

        if (strpos($contents, 'data:') === 0) {
            list($ext, $data) = explode(',', $contents, 2);
            $ext = str_replace('data:image/', '', $ext);
            $ext = str_replace(';base64', '', $ext);
            $ext = ".".$ext;
        }

        $outputFileName = hash_hmac('sha256', str_random(40), config('app.key'));
        $temp_image_path = $this->createImageFromString($contents, $outputFileName);
        $temp_image_path = $this->resizeImageToStandardSize($temp_image_path);
        $text_line_1 = Carbon::now()->format('H:i d M Y');
        $text_line_2 = null;
        $text_line_3 = $authUser->email;
        $temp_image_path = $this->createImageOverlay($temp_image_path, $text_line_1, $text_line_2, $text_line_3);

        \Log::info('createdimagefromstring returns :');
        \Log::info($temp_image_path);

        Storage::disk('S3_uploads')->put('manual_defect/' . $outputFileName . $ext, file_get_contents($temp_image_path));

        return config('filesystems.disks.S3_uploads.domain') . '/manual_defect/' . $outputFileName . $ext;
    }
}
