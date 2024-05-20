<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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

    // Sobrescrever métodos de exclusão para usar 'return_date'
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('notReturned', function (Builder $builder) {
            $builder->whereNull('return_date');
        });
    }

    // Método para "deletar" o registro (marcar como retornado)
    public function markAsReturned()
    {
        $this->return_date = now();
        $this->save();
    }

    // Método para "restaurar" o registro
    public function unmarkAsReturned()
    {
        $this->return_date = null;
        $this->save();
    }

    // Método para forçar a exclusão do registro
    public function forceDelete()
    {
        return parent::delete();
    }

    // Sobrescrever o método padrão de exclusão para usar 'return_date'
    public function delete()
    {
        if ($this->exists) {
            $this->markAsReturned();
            return true;
        }

        return false;
    }

    // Scope para obter registros "retornados"
    public function scopeReturned($query)
    {
        return $query->withoutGlobalScope('notReturned')->whereNotNull('return_date');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'loaned_books');
    }

    public function loanedBooks()
    {
        return $this->hasMany(LoanedBook::class);
    }
}
