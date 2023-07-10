<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\ExpenseResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ExpenseResource\RelationManagers;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('original_amount')
                    ->label('Full Amount')
                    ->prefixIcon('heroicon-o-currency-pound')
                    ->required()
                    ->hidden(fn (Closure $get, $record) => !$record || !$record->original_amount || !$record->split),
                Forms\Components\TextInput::make('amount')
                    ->label(fn (Closure $get, $record) => $record && $record->split ? 'Your current amount' : 'Amount')
                    ->prefixIcon('heroicon-o-currency-pound')
                    ->required()
                    ->disabled(fn (Closure $get, $record) => $record ? $record->payee_id == auth()->user()->id || $record->split : false),

                Forms\Components\DatePicker::make('expense_date')
                    ->displayFormat('j F Y')
                    ->required()
                    ->disabled(fn (Closure $get, $record) => $record ? $record->payee_id == auth()->user()->id : false),

                Forms\Components\Fieldset::make('Reassign this payment')
                    ->schema([
                        Forms\Components\Toggle::make('reassign')
                            ->reactive(),
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->hidden(fn (Closure $get) => !$get('reassign'))
                            ->options(User::whereNot('id', auth()->user()->id)->pluck('name', 'id'))
                            ->default(0),
                    ])
                    ->hiddenOn('edit'),

                Forms\Components\Fieldset::make('Recurring payments')
                    ->schema([
                        Forms\Components\Toggle::make('recurring')
                            ->reactive(),
                        Forms\Components\Select::make('frequency')
                            ->options([
                                'Weekly' => 'Weekly',
                                'Bi-weekly' => 'Bi-weekly',
                                'Monthly' => 'Monthly',
                                'Quarterly' => 'Quarterly',
                            ])
                            ->hidden(fn (Closure $get) => !$get('recurring'))
                            ->required(),
                    ])
                    ->hidden(fn (Closure $get) => $get('reassign'))
                    ->disabled(fn (Closure $get, $record) => $record ? $record->payee_id == auth()->user()->id : false),

                Forms\Components\Fieldset::make('Split payments')
                    ->schema([
                        Forms\Components\Toggle::make('split')
                            ->reactive(),
                        Forms\Components\Select::make('split_percentage')
                            ->options([
                                '50' => '50/50',
                                //'Other' => 'Other'
                            ])
                            ->hidden(fn (Closure $get) => !$get('split'))
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('payee_id')
                            ->relationship('payee', 'name')
                            ->label('Split with')
                            ->hidden(fn (Closure $get) => !$get('split'))
                            ->options(User::whereNot('id', auth()->user()->id)->pluck('name', 'id'))
                            ->default(0),
                    ])->hidden(function (Closure $get, $record) {
                        if ($get('reassign')) {
                            return true;
                        }

                        return $record ? $record->payee_id == auth()->user()->id : false;
                    }),

                Forms\Components\Fieldset::make('Payments')
                    ->schema([
                        Forms\Components\Toggle::make('paid_at')
                            ->label('Mark as paid')
                            ->onIcon('heroicon-s-shield-check')
                            ->offIcon('heroicon-s-x-circle')
                            ->hidden(fn (Closure $get, $record) => $record && $record->user_id !== auth()->user()->id),
                        Forms\Components\TextInput::make('split_amount')
                            ->prefixIcon('heroicon-o-currency-pound')
                            ->disabled()
                            ->hidden(fn (Closure $get) => $get('split') === false),
                        Forms\Components\Toggle::make('payee_paid')
                            ->label(function (Closure $get, $record) {
                                if ($record && $record->user_id !== auth()->user()->id) {
                                    return 'I have paid this expense';
                                }

                                return 'I have received this expense';
                            })->hidden(fn (Closure $get) => $get('split') === false)
                    ]),

                Forms\Components\Fieldset::make('Categories')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Category')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->preload()
                            ->multiple(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //Tables\Columns\Layout\View::make('expenses.table.collapsible')->hidden(),
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Date')
                    ->date('d/m/y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Expense')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable()
                    ->prefix('£'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Added by')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('recurring')
                    ->boolean(),
                Tables\Columns\IconColumn::make('split')
                    ->boolean(),
                Tables\Columns\IconColumn::make('paid_at')
                    ->sortable()
                    ->label('Paid')
                    ->boolean(),
                Tables\Columns\TextColumn::make('frequency')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('tags.name')
                    ->searchable()
                    ->wrap(),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                /**
                 * Category
                 */
                SelectFilter::make('category')->relationship('category', 'name'),

                /**
                 * Tags
                 */
                SelectFilter::make('tags')->relationship('tags', 'name'),

                /**
                 * Week
                 */
                Filter::make('week')
                    ->label('Past 7 days')
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->where('expense_date', '>=', now()->subWeek())
                            ->where('expense_date', '<=', now())
                    )
                    ->toggle(),

                /**
                 * Month
                 */
                Filter::make('month')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->options(
                                Expense::all()
                                    ->groupBy(function ($record) {
                                        return Carbon::parse($record['expense_date'])->format('F Y');
                                    })->mapWithKeys(function ($records, $month) {
                                        return [
                                            Carbon::parse($month)->format('n') . ' ' . Carbon::parse($month)->format('Y') => $month
                                        ];
                                    })
                            )

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['month'],
                            fn (Builder $query, $month): Builder =>
                            $query->whereMonth('expense_date', $month)
                                ->whereYear('expense_date', Str::after($month, ' ')),
                        );
                    }),

                /**
                 * Year
                 */
                Filter::make('year')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->options(
                                Expense::all()
                                    ->groupBy(function ($record) {
                                        return Carbon::parse($record['expense_date'])->format('Y');
                                    })->mapWithKeys(function ($records, $year) {
                                        return [$year => $year];
                                    })
                            )

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['year'],
                            fn (Builder $query, $year): Builder =>
                            $query->whereYear('expense_date', $year),
                        );
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Model $record) => $record->payee_id === auth()->user()->id)
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_as_paid')
                    ->icon('heroicon-s-shield-check')
                    ->action(fn (Collection $records) => $records->where('paid_at', null)
                        ->each(function ($record) {
                            $record->update([
                                'paid_at' => now()
                            ]);
                        })),
                Tables\Actions\BulkAction::make('mark_as_unpaid')
                    ->icon('heroicon-s-x-circle')
                    ->action(fn (Collection $records) => $records
                        ->each(function ($record) {
                            $record->update([
                                'paid_at' => null
                            ]);
                        })),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->where('payee_id', auth()->user()->id)
                    ->orWhere('user_id', auth()->user()->id);
            });
    }
}
