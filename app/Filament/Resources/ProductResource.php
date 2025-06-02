<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $slug = 'productos';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Información Básica')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre del Producto')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    if (($get('slug') ?? '') !== Str::slug($old)) {
                                        return;
                                    }

                                    $set('slug', Str::slug($state));

                                    // Generar SKU con los primeros 6 caracteres del nombre y un número aleatorio. Ej: NOMBRE-1234
                                    if (($get('sku') ?? '') !== Str::slug($old)) {
                                        return;
                                    }

                                    $sku = strtoupper(Str::slug(substr($state, 0, 6))) . '-' . rand(1000, 9999);
                                    $set('sku', $sku);
                                }),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->unique(Product::class, 'slug', ignorable: fn(?Product $record) => $record),
                            TextInput::make('sku')
                                ->label('SKU')
                                ->required()
                                ->unique(Product::class, 'sku', ignorable: fn(?Product $record) => $record),
                            Grid::make(4)
                                ->schema([
                                    Select::make('category_id')
                                        ->label('Categoría')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->required(),
                                    TextInput::make('brand')
                                        ->label('Marca')
                                        ->maxLength(100),
                                    TextInput::make('model')
                                        ->label('Modelo')
                                        ->maxLength(100),
                                    Toggle::make('has_variants')
                                        ->label('¿Tiene Variantes?')
                                        ->inline(false)
                                        ->default(false)
                                        ->reactive()
                                        ->afterStateUpdated(fn(Get $get, Set $set) => $set('variants', [])),
                                ]),
                            RichEditor::make('description')
                                ->label('Descripción del Producto')
                                ->toolbarButtons([
                                    'bold',
                                    'bulletList',
                                    'h2',
                                    'h3',
                                    'italic',
                                    'link',
                                    'orderedList',
                                    'redo',
                                    'strike',
                                    'underline',
                                    'undo',
                                ])
                                ->columnSpanFull(),
                        ])->columns(3),
                    Step::make('Detalles del Producto')
                        ->schema([
                            TextInput::make('price')
                                ->label(fn(Get $get) => $get('has_variants') ? 'Precio Base' : 'Precio') // Cambia el label según si tiene variantes
                                ->integer()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(9999999)
                                ->required(),
                            TextInput::make('stock')
                                ->label('Stock')
                                ->integer()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(9999)
                                ->required()
                                ->visible(fn(Get $get) => !$get('has_variants')), // Mostrar solo si no tiene variantes
                            FileUpload::make('image')
                                ->label('Imagen Principal')
                                ->image()
                                ->imageEditor()
                                ->disk('public')
                                ->directory('products')
                                ->visibility('public'),
                            /* FileUpload::make('gallery_images')
                                ->label('Galería de Imágenes')
                                ->multiple()
                                ->image()
                                ->maxFiles(5)
                                ->visible(fn(Get $get) => !$get('has_variants')), */
                            Repeater::make('variants')
                                ->label('Variantes del Producto')
                                ->visible(fn(Get $get) => $get('has_variants'))
                                ->relationship()
                                ->required()
                                ->addActionLabel('Añadir')
                                ->collapsible()
                                ->collapsed()
                                ->cloneable()
                                ->defaultItems(1)
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Select::make('color')
                                                ->label('Color')
                                                ->options([
                                                    'rojo' => 'Rojo',
                                                    'verde' => 'Verde',
                                                    'azul' => 'Azul',
                                                    'amarillo' => 'Amarillo',
                                                    'negro' => 'Negro',
                                                    'blanco' => 'Blanco',
                                                    'rosa' => 'Rosa',
                                                    'gris' => 'Gris',
                                                    'morado' => 'Morado',
                                                    'naranja' => 'Naranja',
                                                ])
                                                ->native(false)
                                                ->required(),
                                            Select::make('size')
                                                ->label('Talla')
                                                ->options([
                                                    'XS' => 'Extra Pequeño',
                                                    'S' => 'Pequeño',
                                                    'M' => 'Mediano',
                                                    'L' => 'Grande',
                                                    'XL' => 'Extra Grande',
                                                    'XXL' => 'Doble Extra Grande',
                                                    'XXXL' => 'Triple Extra Grande',
                                                ])
                                                ->native(false)
                                                ->required(),
                                            TextInput::make('sku')
                                                ->label('SKU')
                                                ->required()
                                                ->unique(Product::class, 'sku', ignorable: fn(?Product $record) => $record),
                                            TextInput::make('stock')
                                                ->label('Stock')
                                                ->integer()
                                                ->default(0)
                                                ->minValue(0)
                                                ->maxValue(9999)
                                                ->required(),
                                            TextInput::make('price_override')
                                                ->label('Precio Específico')
                                                ->integer()
                                                ->minValue(0)
                                                ->maxValue(9999999)
                                                ->helperText('Dejar en blanco para usar el precio del producto'),
                                            Toggle::make('is_active')
                                                ->label('¿Está Activo?')
                                                ->inline(false)
                                                ->default(true)
                                        ]),
                                    FileUpload::make('image')
                                        ->label('Imagen de la Variante')
                                        ->image()
                                        ->imageEditor()
                                        ->disk('public')
                                        ->directory('product-variants')
                                        ->visibility('public'),
                                ]),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Imagen')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
