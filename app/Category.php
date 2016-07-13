<?php

namespace App;

use Baum\Node;

/**
* Category
*/
class Category extends Node {

  /**
   * Table name.
   *
   * @var string
   */
  protected $table = 'categories';

  //////////////////////////////////////////////////////////////////////////////

  //
  // Below come the default values for Baum's own Nested Set implementation
  // column names.
  //
  // You may uncomment and modify the following fields at your own will, provided
  // they match *exactly* those provided in the migration.
  //
  // If you don't plan on modifying any of these you can safely remove them.
  //

  // /**
  //  * Column name which stores reference to parent's node.
  //  *
  //  * @var string
  //  */
  // protected $parentColumn = 'parent_id';

  // /**
  //  * Column name for the left index.
  //  *
  //  * @var string
  //  */
  // protected $leftColumn = 'lft';

  // /**
  //  * Column name for the right index.
  //  *
  //  * @var string
  //  */
  // protected $rightColumn = 'rgt';

  // /**
  //  * Column name for the depth field.
  //  *
  //  * @var string
  //  */
  // protected $depthColumn = 'depth';

  // /**
  //  * Column to perform the default sorting
  //  *
  //  * @var string
  //  */
  // protected $orderColumn = null;

  // /**
  // * With Baum, all NestedSet-related fields are guarded from mass-assignment
  // * by default.
  // *
  // * @var array
  // */
  // protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

  //
  // This is to support "scoping" which may allow to have multiple nested
  // set trees in the same database table.
  //
  // You should provide here the column names which should restrict Nested
  // Set queries. f.ex: company_id, etc.
  //

  // /**
  //  * Columns which restrict what we consider our Nested Set list
  //  *
  //  * @var array
  //  */
  // protected $scoped = array();

  //////////////////////////////////////////////////////////////////////////////

  //
  // Baum makes available two model events to application developers:
  //
  // 1. `moving`: fired *before* the a node movement operation is performed.
  //
  // 2. `moved`: fired *after* a node movement operation has been performed.
  //
  // In the same way as Eloquent's model events, returning false from the
  // `moving` event handler will halt the operation.
  //
  // Please refer the Laravel documentation for further instructions on how
  // to hook your own callbacks/observers into this events:
  // http://laravel.com/docs/5.0/eloquent#model-events

  protected $fillable = ["name", "slug", "icon", "status"];

  /**
   * Scopes
   */
  
  public function scopePublished($query)
  {
    return $query->where("status", "published");
  }

  public function scopeDraft($query)
  {
    return $query->where("status", "draft");
  }

  public function scopePrivate($query)
  {
    return $query->where("status", "private");
  }

  public function scopeTrashed($query)
  {
    return $query->where("status", "trashed");
  }

  public function scopeArchived($query)
  {
    return $query->where("status", "archived");
  }

  protected static $statuses = [
    "draft" => "Черновик",
    "published" => "Опубликовано",
    "private" => "Частное",
    "trashed" => "В корзине",
    "archived" => "Архивировано"
  ];

  public static function statuses($status)
  {
    return isset(self::$statuses[$status]) ? self::$statuses[$status] : "Неизвестно";
  }

  public static function statusesDropdown()
  {
    return self::$statuses;
  }

  public static function dropdown($all = false)
  {
    $dropdown = [];
    $roots = Category::roots()->orderBy("name", "ASC")->get();

    foreach ($roots as $root)
    {
      $categories = $root->descendants()->limitDepth(1)->get();
      $dropdown[$root->name] = [];

      foreach ($categories as $category)
      {
        $dropdown[$root->name][$category->id] = $category->name;
      }
    }

    return $dropdown;
  }
}
