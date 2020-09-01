<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use http\Env\Request;
use Illuminate\Support\Str;
use function GuzzleHttp\Psr7\str;

/**
 * Class TagCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TagCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\Tag::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/tag');
        $this->crud->setEntityNameStrings('tag', 'tags');
//        $this->crud->setFromDb();
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
//        $this->crud->setFromDb(); // columns
        $this->crud->addColumn('name');
        $this->crud->addColumn('slug');
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    public function index()
    {
        if(backpack_user()->hasPermissionTo('manager tags','backpack')){
            $this->crud->hasAccessOrFail('list');

            $this->data['crud'] = $this->crud;
            $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

            // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
            return view($this->crud->getListView(), $this->data);
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    public function store(TagRequest $request)
    {
        if(backpack_user()->hasPermissionTo('manager tags','backpack')){
        $this->crud->setValidation(TagRequest::class);
        $name = $request->input('name');
        $tag = new Tag();

        $tag->name = $name;
        $tag->slug = str::slug($name);
        $tag->save();

        \Alert::success(trans('Tạo mới thành công !'));

        return redirect()->route('tag.index');
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    public function update(TagRequest $request,$id)
    {
        if(backpack_user()->hasPermissionTo('manager tags','backpack')){
        $name = $request->input('name');
        $tag = Tag::find($id);

        $tag->name = $name;
        $tag->slug = str::slug($name);
        $tag->save();
        \Alert::success(trans('Cập nhật thành công !'));
        return redirect()->route('tag.index');
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TagRequest::class);

//        CRUD::setFromDb(); // fields
        $this->crud->addField([
           'label' => 'Name',
            'type' => 'text',
            'name' => 'name',
        ]);

        $this->crud->addField([
           'label' => 'Slug',
           'type' => 'text',
           'name' => 'slug',
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
}
