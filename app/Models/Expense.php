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
        'split_amount',
        'user_id',
        'category_id'
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
        'category_id' => 'int'
    ];

    public function user()
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
