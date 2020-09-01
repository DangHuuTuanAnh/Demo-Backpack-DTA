<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Requests\UserStoreCrudRequest as StoreRequest;
use Backpack\PermissionManager\app\Http\Requests\UserUpdateCrudRequest as UpdateRequest;
use Illuminate\Support\Facades\Hash;


/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation{ store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation{ update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */



    public function setup()
    {
        CRUD::setModel(\App\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');

    }


    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */



    protected function setupListOperation()
    {
        CRUD::setFromDb(); // columns

//        CRUD::column('title');
//        CRUD::column('image');
//        CRUD::column('content');
//        CRUD::column('slug');

//        $this->crud->setColumns([
//            [
//                'name'  => 'name',
//                'label' => trans('backpack::permissionmanager.name'),
//                'type'  => 'text',
//            ],
//            [
//                'name'  => 'email',
//                'label' => trans('backpack::permissionmanager.email'),
//                'type'  => 'email',
//            ],
//            [ // n-n relationship (with pivot table)
//                'label'     => trans('backpack::permissionmanager.roles'), // Table column heading
//                'type'      => 'select_multiple',
//                'name'      => 'roles', // the method that defines the relationship in your Model
//                'entity'    => 'roles', // the method that defines the relationship in your Model
//                'attribute' => 'name', // foreign key attribute that is shown to user
//                'model'     => config('permission.models.role'), // foreign key model
//            ],
//            [ // n-n relationship (with pivot table)
//                'label'     => trans('backpack::permissionmanager.extra_permissions'), // Table column heading
//                'type'      => 'select_multiple',
//                'name'      => 'permissions', // the method that defines the relationship in your Model
//                'entity'    => 'permissions', // the method that defines the relationship in your Model
//                'attribute' => 'name', // foreign key attribute that is shown to user
//                'model'     => config('permission.models.permission'), // foreign key model
//            ],
//        ]);
//
//        // Role Filter
//        $this->crud->addFilter(
//            [
//                'name'  => 'role',
//                'type'  => 'dropdown',
//                'label' => trans('backpack::permissionmanager.role'),
//            ],
//            config('permission.models.role')::all()->pluck('name', 'id')->toArray(),
//            function ($value) { // if the filter is active
//                $this->crud->addClause('whereHas', 'roles', function ($query) use ($value) {
//                    $query->where('role_id', '=', $value);
//                });
//            }
//        );
//
//        // Extra Permission Filter
//        $this->crud->addFilter(
//            [
//                'name'  => 'permissions',
//                'type'  => 'select2',
//                'label' => trans('backpack::permissionmanager.extra_permissions'),
//            ],
//            config('permission.models.permission')::all()->pluck('name', 'id')->toArray(),
//            function ($value) { // if the filter is active
//                $this->crud->addClause('whereHas', 'permissions', function ($query) use ($value) {
//                    $query->where('permission_id', '=', $value);
//                });
//            }
//        );
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

    protected function setupCreateOperation()
    {
        $this->addUserFields();
        $this->crud->setValidation(StoreRequest::class);
//        CRUD::setValidation(UserRequest::class);
//
//        CRUD::setFromDb(); // fields

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
//        $this->setupCreateOperation();
        $this->addUserFields();
        $this->crud->setValidation(UpdateRequest::class);
    }

    public function index()
    {
        if(backpack_user()->hasPermissionTo('manager user','backpack')){
            $this->crud->hasAccessOrFail('list');

            $this->data['crud'] = $this->crud;
            $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

            // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
            return view($this->crud->getListView(), $this->data);
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    public function create()
    {
        if(backpack_user()->hasPermissionTo('manager user','backpack')){
            $this->crud->hasAccessOrFail('create');

            // prepare the fields you need to show
            $this->data['crud'] = $this->crud;
            $this->data['saveAction'] = $this->crud->getSaveAction();
            $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

            // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
            return view($this->crud->getCreateView(), $this->data);
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    public function store()
    {
        if(backpack_user()->hasPermissionTo('manager user','backpack')){
            $this->crud->setRequest($this->crud->validateRequest());
            $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
            $this->crud->unsetValidation(); // validation has already been run

            return $this->traitStore();
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    public function edit($id)
    {
        if(backpack_user()->hasPermissionTo('manager user','backpack')){
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
        if(backpack_user()->hasPermissionTo('manager user','backpack')){
            $this->crud->setRequest($this->crud->validateRequest());
            $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
            $this->crud->unsetValidation(); // validation has already been run

            return $this->traitUpdate();
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }
    public function destroy($id)
    {
        if(backpack_user()->hasPermissionTo('manager user','backpack')){
            $this->crud->hasAccessOrFail('delete');

            // get entry ID from Request (makes sure its the last ID for nested resources)
            $id = $this->crud->getCurrentEntryId() ?? $id;

            return $this->crud->delete($id);
        }else{
            $this->crud->hasAccessOrFail('something');
        }

    }

    protected function handlePasswordInput($request)
    {
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');
        $request->request->remove('roles_show');
        $request->request->remove('permissions_show');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        return $request;
    }

    protected function addUserFields()
    {
        $this->crud->addFields([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
            ],
            [
                'name'  => 'password',
                'label' => trans('backpack::permissionmanager.password'),
                'type'  => 'password',
            ],
            [
                'name'  => 'password_confirmation',
                'label' => trans('backpack::permissionmanager.password_confirmation'),
                'type'  => 'password',
            ],
            [
                // two interconnected entities
                'label'             => trans('backpack::permissionmanager.user_role_permission'),
                'field_unique_name' => 'user_role_permission',
                'type'              => 'checklist_dependency',
                'name'              => ['roles', 'permissions'],
                'subfields'         => [
                    'primary' => [
                        'label'            => trans('backpack::permissionmanager.roles'),
                        'name'             => 'roles', // the method that defines the relationship in your Model
                        'entity'           => 'roles', // the method that defines the relationship in your Model
                        'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                        'attribute'        => 'name', // foreign key attribute that is shown to user
                        'model'            => config('permission.models.role'), // foreign key model
                        'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns'   => 3, //can be 1,2,3,4,6
                    ],
                    'secondary' => [
                        'label'          => ucfirst(trans('backpack::permissionmanager.permission_singular')),
                        'name'           => 'permissions', // the method that defines the relationship in your Model
                        'entity'         => 'permissions', // the method that defines the relationship in your Model
                        'entity_primary' => 'roles', // the method that defines the relationship in your Model
                        'attribute'      => 'name', // foreign key attribute that is shown to user
                        'model'          => config('permission.models.permission'), // foreign key model
                        'pivot'          => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns' => 3, //can be 1,2,3,4,6
                    ],
                ],
            ],
        ]);
    }

}
