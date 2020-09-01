<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use DOMDocument;


/**
 * Class PostCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PostCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {

        CRUD::setModel(\App\Models\Post::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/post');
        CRUD::setEntityNameStrings('post', 'posts');

        $this->crud->operation('list', function () {
            $this->crud->addColumn('title');
            $this->crud->addColumn([
                'label' => 'Thumbnail',
                'type' => 'image',
                'name' => 'image',
                'height' => '100px',
                'width' => '80px'
            ]);
            $this->crud->addColumn([
                'label' => 'Category',
                'type' => 'select',
                'name' => 'category_id',
                'entity' => 'category',
                'attribute' => 'name',
                'wrapper'   => [
                    'href' => function ($crud, $column, $entry, $related_key) {
                        return backpack_url('category/'.$related_key.'/show');
                    },
                ],
            ]);
            $this->crud->addColumn('tags');

            $this->crud->addFilter([ // select2 filter
                'name' => 'category_id',
                'type' => 'select2',
                'label'=> 'Category',
            ], function () {
                return \Backpack\NewsCRUD\app\Models\Category::all()->keyBy('id')->pluck('name', 'id')->toArray();
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'category_id', $value);
            });

            $this->crud->addFilter([ // select2_multiple filter
                'name' => 'tags',
                'type' => 'select2_multiple',
                'label'=> 'Tags',
            ], function () {
                return \Backpack\NewsCRUD\app\Models\Tag::all()->keyBy('id')->pluck('name', 'id')->toArray();
            }, function ($values) { // if the filter is active
                $this->crud->query = $this->crud->query->whereHas('tags', function ($q) use ($values) {
                    foreach (json_decode($values) as $key => $value) {
                        if ($key == 0) {
                            $q->where('tags.id', $value);
                        } else {
                            $q->orWhere('tags.id', $value);
                        }
                    }
                });
            });
        });



    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */

    protected function setupListOperation()
    {

//        CRUD::setFromDb(); // columns
//            CRUD::column('title');
//            CRUD::column('image');
//            CRUD::column('content');
//            CRUD::column('slug');

            if(! backpack_user()->hasPermissionTo('delete post','backpack')){
                $this->crud->denyAccess('delete');
            }

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */


    protected function store(PostRequest $request)
    {
            CRUD::setValidation(PostRequest::class);
            $post = new Post();
            $title = $request->input('title');
            $slug = $request->input('slug');
            $content = $request->input('content');
            $image = $request->input('image');
            $category_id = $request->input('category_id');
            $tags = $request->input('tags');

            //Xử lý image content
            $dom = new DomDocument;
            libxml_use_internal_errors(true);
            $content_post = $dom->loadHTML($content);
            $tag_img = $dom->getElementsByTagName('img');

            foreach ($tag_img as $img){
                $src = $img->getAttribute('src');

                if(preg_match('/data:image/', $src)){
                    // get the mimetype
                    preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                    //Lấy đuôi file ảnh
                    $mimetype = $groups['mime'];
                    // Tạo tên tệp ngẫu nhiên
                    $filename = uniqid();

                    $filepath = "storage/image_content/$filename.$mimetype";
                    // @see http://image.intervention.io/api/
                    $content_img = Image::make($src)
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
//                file_put_contents($complete_save_loc, file_get_contents($new_src));
//                Storage::disk('public')->put($complete_save_loc,file_get_contents($new_src));
//                Storage::disk('public')->put($complete_save_loc,file_get_contents($src));

                    $imageContent = Image::make($complete_save_loc);

                    $imageContentWebPath = public_path().'/storage/image_mobi/';
                    $imageContentMobiPath = public_path().'/storage/image_web/';

                    $imageContent->save($imageContentWebPath.time().$filename);
                    $imageContent->resize(250,150);
                    $imageContent->save($imageContentMobiPath.time().$filename);

                }else{
                    $my_save_dir = 'storage/image_content/';
                    $filename = basename($src);
                    $url = explode('.',$filename);
                    $replace_url = str_replace($url[1],'jpg',$filename);
                    $complete_save_loc = $my_save_dir . $replace_url;
                    file_put_contents($complete_save_loc, file_get_contents($src));
                    $imageContent = Image::make($complete_save_loc);

                    $imageContentWebPath = public_path().'/storage/image_web/';
                    $imageContentMobiPath = public_path().'/storage/image_mobi/';

                    $imageContent->save($imageContentWebPath.time().$replace_url);
                    $imageContent->resize(150,150);
                    $imageContent->save($imageContentMobiPath.time().$replace_url);
                }
            }
            $post->title = $title;
            $post->slug = str::slug($title);
            $post->content = $content;
            $post->image = $image;
            $post->category_id = $category_id;
            $post->user_id = Auth::user()->id;

            //Xử lý thumbnail:
            $name_thumbnail = basename($image);
            $thumbnailImage = Image::make($image);
            $thumbnailMobiPath = public_path().'/storage/image_mobi/';
            $originalWebPath = public_path().'/storage/image_web/';
            $thumbnailTabletPath = public_path().'/storage/image_tablet/';
            $thumbnailImage->save($originalWebPath.$name_thumbnail);
            $thumbnailImage->resize(400,350);
            $thumbnailImage->save($thumbnailTabletPath.$name_thumbnail);
            $thumbnailImage->resize(150,100);
            $thumbnailImage->save($thumbnailMobiPath.$name_thumbnail);

            $post->save();
            $img = $post->images()->create([
                'path'=> $name_thumbnail,
            ]);
        \Alert::success(trans('Tạo mới thành công !'))->flash();

        foreach ($tags as $tag){
            $post->tags()->attach($tag);
        }

        return redirect()->route('post.index');

    }

    public function edit($id)
    {
        $user_id= Post::find($id)->user_id;
        if(backpack_user()->hasPermissionTo('update post','backpack') || (backpack_user()->id == $user_id)){
            $this->crud->hasAccessOrFail('update');
            // get entry ID from Request (makes sure its the last ID for nested resources)
            $id = $this->crud->getCurrentEntryId() ?? $id;
            $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
            // get the info for that entry
            $this->data['entry'] = $this->crud->getEntry($id);
            $this->data['crud'] = $this->crud;
            $this->data['saveAction'] = $this->crud->getSaveAction();
            $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;

            $this->data['id'] = $id;

            // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
            return view($this->crud->getEditView(), $this->data);
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    public function update()
    {
        $request = $this->crud->validateRequest();
        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());

        if(backpack_user()->hasPermissionTo('update post','backpack') || (Auth::user()->id == $item->user_id)){
            $this->crud->hasAccessOrFail('update');

            // execute the FormRequest authorization and validation, if one is required
//            $request = $this->crud->validateRequest();
//            // update the row in the db
//            $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
//                $this->crud->getStrippedSaveRequest());
            $this->data['entry'] = $this->crud->entry = $item;

            // show a success message
//            \Alert::success(trans('backpack::crud.update_success'))->flash();
            \Alert::success(trans('Cập nhật thành công !'))->flash();

            // save the redirect choice for next time
            $this->crud->setSaveAction();

            return $this->crud->performSaveAction($item->getKey());
        }else{
//            \Alert::error(trans('Cập nhật không thành công !'))->flash();
//            return redirect()->route('post.index');
            $this->crud->hasAccessOrFail('something');

        }

    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(PostRequest::class);

//        CRUD::setFromDb(); // fields
        $this->crud->addField([
           'name'=>'title',
           'type'=>'text',
            'label'=>'Title',
        ]);

        $this->crud->addField([
            'name' => 'slug',
            'label' => 'Slug (URL)',
            'type' => 'text',
            'hint' => 'Will be automatically generated from your title, if left empty.',
            // 'disabled' => 'disabled'
        ]);
        $this->crud->addField([
           'name' => 'content',
           'type' => 'ckeditor',
           'label' => 'Content',
        ]);
        $this->crud->addField([
            'name' => 'image',
            'label' => 'Image',
            'type' => 'browse',
        ]);
        $this->crud->addField([
            'label' => 'Category',
            'type' => 'relationship',
            'name' => 'category_id',
            'entity' => 'category',
            'attribute' => 'name',
            'inline_create' => true,
            'ajax' => true,
        ]);
        $this->crud->addField([
            'label' => 'Tags',
            'type' => 'relationship',
            'name' => 'tags', // the method that defines the relationship in your Model
            'entity' => 'tags', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
            'inline_create' => ['entity' => 'tag'],
            'ajax' => true,
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function fetchCategory()
    {
        return $this->fetch(\Backpack\NewsCRUD\app\Models\Category::class);
    }

    public function fetchTags()
    {
        return $this->fetch(\Backpack\NewsCRUD\app\Models\Tag::class);
    }

    public function destroy($id)
    {
        if(backpack_user()->hasPermissionTo('delete post','backpack')){
            $this->crud->hasAccessOrFail('delete');

            // get entry ID from Request (makes sure its the last ID for nested resources)
            $id = $this->crud->getCurrentEntryId() ?? $id;
            return $this->crud->delete($id);
        }

    }

}
