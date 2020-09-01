<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ResizeController extends Controller
{
    function index()
    {
        return view('resize');
    }

    function resize_image(Request $request)
    {
//        $this->validate($request, [
//            'image'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
//        ]);
//
//        $image = $request->file('image');
//
//        $image_name = time() . '.' . $image->getClientOriginalExtension();
//
//        $destinationPath = public_path('/thumbnail');
//        $resize_image = Image::make($image->getRealPath());
//
//        $resize_image->resize(150, 150, function($constraint){
//            $constraint->aspectRatio();
//        })->save($destinationPath . '/' . $image_name);
//
//        $destinationPath = public_path('/images');
//
//        $image->move($destinationPath, $image_name);
//
//        return back()
//            ->with('success', 'Image Upload successful')
//            ->with('imageName', $image_name);

        if($request->has('image')){
            $image = $request->file('image');
            $namefile = $image->getClientOriginalName();
            $thumbnail_post = Storage::disk('public')->putFileAs('thumbnail-post',$image,$namefile);
            $url = Storage::url($thumbnail_post);
            $resize = $url->resize(150,150);

            return $resize->response('jpg');
        }

    }
}
