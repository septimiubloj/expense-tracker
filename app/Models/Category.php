<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'budget', 'book_id'];

    public const TYPES = ['income', 'expense', 'transfer'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
