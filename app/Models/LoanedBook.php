<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanedBook extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loan_id', 'book_id'
    ];

    /**
     * The books that belong to the author.
     */
    public function loans()
    {
        return $this->belongsToMany(Loan::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * The books that belong to the author.
     */
    public function books()
    {
        return $this->belongsTo(Book::class);
    }

    // Definir a relação com o modelo Book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
