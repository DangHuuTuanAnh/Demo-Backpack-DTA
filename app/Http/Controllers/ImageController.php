<?php

namespace App\Http\Controllers;

use DOMDocument;
use Illuminate\Http\Request;
use App\Models\ImageModel;
use Intervention\Image\Facades\Image;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $image = ImageModel::latest()->first();
        return view('createimage', compact('image'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'filename' => 'image|required|mimes:jpeg,png,jpg,gif,svg'
        ]);

        $originalImage= $request->file('filename');
        $thumbnailImage = Image::make($originalImage);

//        $namefile = $originalImage->getClientOriginalName();

//        $thumbnailPath = Storage::disk('public')->putFileAs('thumbnail-post',$originalImage,$namefile);
//        $mobiImage = $thumbnailImage->resize(150,150);
//        $mobiPath = Storage::disk('public')->putFileAs('mobi-post',$mobiImage);


        $thumbnailPath = public_path().'/thumbnail/';
        $originalPath = public_path().'/images/';

        $thumbnailImage->save($originalPath.time().$originalImage->getClientOriginalName());
        $thumbnailImage->resize(150,150);
        $thumbnailImage->save($thumbnailPath.time().$originalImage->getClientOriginalName());

//        $imagemodel= new ImageModel();
//        $imagemodel->filename=time().$originalImage->getClientOriginalName();
//        $imagemodel->save();

        return back()->with('success', 'Your images has been successfully Upload');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function getSummernote(){
        return view('createimage');
    }

    public function postSummernote(Request $request){


        $this->validate($request, [
            'message' => 'required'
        ]);
        $message=$request->input('message');

        $dom = new DomDocument();
        libxml_use_internal_errors(true);
//        $dom->loadHtml($message, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $test = $dom->loadHtml($message);
        $images = $dom->getElementsByTagName('img');

        foreach($images as $img){

            //Url Image:
            $src = $img->getAttribute('src');

            // if the img source is 'data-url'
            if(preg_match('/data:image/', $src)){
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                //Lấy đuôi file ảnh
                $mimetype = $groups['mime'];
                // Tạo tên tệp ngẫu nhiên
                $filename = uniqid();

                $filepath = "storage/image_content/$filename.$mimetype";
                // @see http://image.intervention.io/api/
                $image = Image::make($src)
                    // resize if required
                    /* ->resize(300, 200) */
                    ->encode($mimetype, 100)  // encode file to the specified mimetype
                    ->save(public_path($filepath));
                $new_src = asset($filepath);
                $img->removeAttribute('src');
                $img->setAttribute('src', $new_src);

                $my_save_dir = 'storage/image_content/';
                $filename = basename($new_src);
                $complete_save_loc = $my_save_dir . $filename;
//                dd($complete_save_loc);
//                file_put_contents($complete_save_loc, file_get_contents($new_src));
//                Storage::disk('public')->put($complete_save_loc,file_get_contents($new_src));
//                Storage::disk('public')->put($complete_save_loc,file_get_contents($src));

                $thumbnailImage = Image::make($complete_save_loc);

                $thumbnailPath = public_path().'/thumbnail/';
                $originalPath = public_path().'/images/';
                $imagepcPath = public_path().'/image_pc/';

                $thumbnailImage->save($originalPath.time().$filename);
                $thumbnailImage->resize(400,400);
                $thumbnailImage->save($imagepcPath.time().$filename);
                $thumbnailImage->resize(150,150);
                $thumbnailImage->save($thumbnailPath.time().$filename);

            }else{
                $my_save_dir = 'storage/image_content/';
                $filename = basename($src);
                $url = explode('.',$filename);
                $replace_url = str_replace($url[1],'jpg',$filename);
                $complete_save_loc = $my_save_dir . $replace_url;
                dd($complete_save_loc);
                file_put_contents($complete_save_loc, file_get_contents($src));
                $thumbnailImage = Image::make($complete_save_loc);

                $thumbnailPath = public_path().'/thumbnail/';
                $originalPath = public_path().'/images/';
                $imagepcPath = public_path().'/image_pc/';

                $thumbnailImage->save($originalPath.time().$replace_url);
                $thumbnailImage->resize(400,400);
                $thumbnailImage->save($imagepcPath.time().$replace_url);
                $thumbnailImage->resize(150,150);
                $thumbnailImage->save($thumbnailPath.time().$replace_url);

            }
//            $thumbnailImage = Image::make($complete_save_loc);
//
//            $thumbnailPath = public_path().'/thumbnail/';
//            $originalPath = public_path().'/images/';
//            $imagepcPath = public_path().'/image_pc/';
//
//            $thumbnailImage->save($originalPath.time().$replace_url);
//            $thumbnailImage->resize(400,400);
//            $thumbnailImage->save($imagepcPath.time().$replace_url);
//            $thumbnailImage->resize(150,150);
//            $thumbnailImage->save($thumbnailPath.time().$replace_url);
            // <!--endif
        } // <!-
//        $product->message = $dom->saveHTML();
//        $product->save();

//        $image = Image::make('myimage.jpg')->resize(200, 100);
//        return $image->response('jpg');
    }

    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
