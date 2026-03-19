<?php

namespace App\Filament\Resources\Configs;

use App\Filament\Resources\Configs\Pages\ManageConfigs;
use App\Models\Config;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class ConfigResource extends Resource
{
    protected static ?string $model = Config::class;

    protected static ?string $navigationLabel = 'Configuraciones';
    protected static ?string $pluralLabel = 'Configuraciones';
    protected static ?string $modelLabel = 'Configuración';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(fn (?Config $record) => [
                Section::make('Detalle de Configuración')
                    ->schema([
                        TextInput::make('id')
                            ->label('Clave')
                            ->disabled()
                            ->required(),
                        
                        TextInput::make('description')
                            ->label('Descripción')
                            ->disabled(),

                        Select::make('type')
                            ->label('Tipo de Dato')
                            ->options([
                                'str' => 'Texto',
                                'int' => 'Número Entero',
                                'bool' => 'Booleano',
                                'date' => 'Fecha',
                            ])
                            ->default('str')
                            ->required(),

                        // Renderizamos solo el componente que corresponde al registro actual
                        ...($record ? [
                            (($record->type ?? 'str') === 'bool'
                                ? Toggle::make('value')
                                    ->label('Habilitado')
                                    ->afterStateHydrated(fn ($component, $state) => $component->state((bool)$state))
                                    ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0')
                                : TextInput::make('value')
                                    ->label('Valor')
                                    ->required())
                        ] : [
                            // Fallback para creación si fuera necesario
                            TextInput::make('value')->label('Valor')->required()
                        ]),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Configuración')
                    ->weight('bold')
                    ->size('lg')
                    ->searchable()
                    ->description(fn (Config $record): string => $record->id),
                TextColumn::make('value')
                    ->label('Valor Actual')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'bool' ? ($state ? 'Habilitado' : 'Desactivado') : $state)
                    ->color(fn ($state, $record) => $record->type === 'bool' ? ($state ? 'success' : 'danger') : null),
            ])
            ->defaultSort('description', 'asc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConfigs::route('/'),
        ];
    }
}
