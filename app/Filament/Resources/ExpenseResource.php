<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Expense;
use App\Models\Category;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
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
                Forms\Components\TextInput::make('amount')
                    ->prefixIcon('heroicon-o-currency-pound')
                    ->required(),

                Forms\Components\DatePicker::make('expense_date')
                    ->required(),

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
                    ]),

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
                        Forms\Components\TextInput::make('split_amount')
                            ->prefixIcon('heroicon-o-currency-pound')
                            ->hidden(fn (Closure $get) => $get('split_percentage') !== 'Other')
                            ->required(),
                    ])->hidden(fn (Closure $get, $record) => $record ? $record->payee_id == auth()->user()->id : false),

                Forms\Components\Fieldset::make('Payments')
                    ->schema([
                        Forms\Components\Toggle::make('payee_paid')
                            ->label(function (Closure $get, $record) {
                                if ($record && $record->user_id !== auth()->user()->id) {
                                    return 'I have paid this expense';
                                }

                                return 'Paid';
                            })
                            ->disabled(fn (Closure $get, $record) => $record && $record->user_id !== auth()->user()->id)
                    ])->hidden(fn (Closure $get) => $get('split') === false),

                Forms\Components\Fieldset::make('Categorise')
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

                //Forms\Components\Hidden::make('user_id')
                //    ->dehydrateStateUsing(fn ($state) => auth()->user()->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Expense')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->prefix('£'),
                Tables\Columns\IconColumn::make('recurring')
                    ->boolean(),
                Tables\Columns\IconColumn::make('split')
                    ->boolean(),
                Tables\Columns\IconColumn::make('payee_paid')
                    ->label('Paid')
                    ->boolean(),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\TextColumn::make('expense_date')
                    ->date('jS F Y'),
                Tables\Columns\TextColumn::make('tags.name')
                    ->searchable()
                    ->wrap(),
                //Tables\Columns\TextColumn::make('created_at')
                //    ->dateTime('jS F Y'),
            ])
            ->defaultSort('expense_date')
            ->filters([
                // TODO
                // Date by year
                // Date by month
                // Date by this week
                // Category
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
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
