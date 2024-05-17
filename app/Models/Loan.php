<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'loan_date', 'return_date', 'expected_return_date'
    ];

    /**
     * Define o relacionamento com os livros emprestados.
     */
    public function loanedBooks()
    {
        return $this->hasMany(LoanedBook::class);
    }

    public function books()
    {
        return $this->hasManyThrough(Book::class, LoanedBook::class, 'loan_id', 'id', 'id', 'book_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
