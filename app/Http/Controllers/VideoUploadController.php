<?php

namespace App\Http\Controllers;

use App\Jobs\VideoConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Owenoj\LaravelGetId3\GetId3;
use App\Models\Video;
class VideoUploadController extends Controller
{
    public function showUploadForm()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            //'video'=> 'required|mimes:mp4,ppx,pdf,ogv,jpg,webm|max:1999',
            'video'=> 'required',

        ]);
        if($request->hasFile('video')){
            $filenameWithExt= $request->file('video')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('video')->getClientOriginalExtension();
            $fileNameToStore = $filename. '_'.time().'.'.$extension;
            $path = $request->file('video')->storeAs('public/videos/',$fileNameToStore);
            $track = GetId3::fromDiskAndPath('local', $path);
            $video = new Video();
            $video->path = $path;
            $video->metadata = mb_convert_encoding($track->extractInfo(), 'UTF-8', 'UTF-8');$track->extractInfo();
            $video->save();
            VideoConverter::dispatch($video)
                ->delay(now()->addMinutes(10));
            dd($video->metadata);
        }



    }
}
