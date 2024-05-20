<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'publication_year',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_books')
            ->withTimestamps()->whereNull('author_books.deleted_at');
    }

    public function loans()
    {
        return $this->belongsToMany(Loan::class, 'loaned_books');
    }

    public function loanedBooks()
    {
        return $this->hasMany(LoanedBook::class);
    }
}
