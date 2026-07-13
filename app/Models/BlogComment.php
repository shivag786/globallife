<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['blog_post_id', 'name', 'email', 'content'])]
class BlogComment extends Model
{
    //
}
