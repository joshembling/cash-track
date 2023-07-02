<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'recurring',
        'frequency',
        'copied',
        'expense_date',
        'split',
        'split_percentage',
        'split_amount',
        'payee_id',
        'user_id',
        'category_id',
        'payee_paid'
    ];

    protected $casts = [
        'name' => 'string',
        'amount' => 'decimal:2',
        'recurring' => 'bool',
        'copied' => 'bool',
        'split' => 'bool',
        'frequency' => 'string',
        'expense_date' => 'datetime',
        'user_id' => 'int',
        'category_id' => 'int',
        'payee_paid' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payee()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'expenses_tags');
    }
}
