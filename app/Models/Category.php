<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use CrudTrait;
    use Sluggable,SluggableScopeHelpers;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'categories';
     protected $primaryKey = 'id';
    // public $timestamps = false;
//    protected $guarded = ['id'];
     protected $fillable = ['name','slug','parent_id'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    /**
     * @var mixed
     */

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
    }

//    public function children()
//    {
//        return $this->hasMany('App\Models\Category', 'parent_id');
//    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'slug_or_name',
            ],
        ];
    }

    public function getSlugOrNameAttribute()
    {
        if ($this->slug != '') {
            return $this->slug;
        }

        return $this->name;
    }
}
