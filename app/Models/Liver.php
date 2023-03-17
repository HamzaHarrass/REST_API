<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liver extends Model
{
    use HasFactory;
    public function collections(){
        return $this->belongsTo(collection::class);
    }
    public function genres(){
        return $this->belongsTo(Genre::class);
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }


}
