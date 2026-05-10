<?php

namespace App\Filament\Resources\OrphanResource\Pages;

use App\Filament\Resources\OrphanResource;
use App\Models\Family;
use App\Models\Orphan;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Component;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\Common\Creator\ReaderFactory;

class ListOrphans extends ListRecords
{
    protected static string $resource = OrphanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Importer (Direct)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->form([
                    FileUpload::make('file')
                        ->label('Fichier CSV ou Excel')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $file = $data['file'];
                    if (is_array($file)) {
                        $file = array_key_first($file) ?: (array_values($file)[0] ?? null);
                    }

                    if (!$file || !Storage::disk('local')->exists($file)) {
                        Notification::make()->title('Fichier introuvable.')->danger()->send();
                        return;
                    }

                    try {
                        $path = Storage::disk('local')->path($file);
                        $isCsv = str_ends_with(strtolower($file), '.csv');

                        if ($isCsv) {
                            $reader = new \OpenSpout\Reader\CSV\Reader();
                            $firstLine = file_exists($path) ? fgets(fopen($path, 'r')) : '';
                            if (str_contains($firstLine, ';') && !str_contains($firstLine, ',')) {
                                $reader->setFieldDelimiter(';');
                            }
                        } else {
                            $reader = new \OpenSpout\Reader\XLSX\Reader();
                        }

                        $reader->open($path);
                        $headers = [];
                        $rows = [];
                        foreach ($reader->getSheetIterator() as $sheet) {
                            foreach ($sheet->getRowIterator() as $index => $row) {
                                $rowData = $row->toArray();
                                if ($index === 1) {
                                    $headers = $rowData;
                                    continue;
                                }
                                if (count(array_filter($rowData)) > 0) {
                                    $rows[] = array_combine($headers, $rowData);
                                }
                            }
                            break;
                        }
                        $reader->close();

                        $count = 0;
                        foreach ($rows as $rowData) {
                            $mappedData = [];
                            foreach ($rowData as $header => $value) {
                                $h = strtolower(trim($header));
                                if (str_contains($h, 'prénom') || str_contains($h, 'first')) $mappedData['first_name'] = $value;
                                elseif (str_contains($h, 'nom') || str_contains($h, 'last')) $mappedData['last_name'] = $value;
                                elseif (str_contains($h, 'date') && str_contains($h, 'naiss')) $mappedData['date_of_birth'] = $value;
                                elseif (str_contains($h, 'sexe') || str_contains($h, 'gender')) $mappedData['gender'] = (str_contains(strtolower($value), 'f') || str_contains(strtolower($value), 'm')) ? ($value ? strtolower(substr($value, 0, 1)) : 'm') : 'm';
                                elseif (str_contains($h, 'santé')) $mappedData['health_status'] = $value;
                                elseif (str_contains($h, 'éducation') || str_contains($h, 'scolaire')) $mappedData['education_level'] = $value;
                                elseif (str_contains($h, 'famille') || str_contains($h, 'veuve') || str_contains($h, 'mère')) $mappedData['widow_name'] = $value;
                            }

                            if (!isset($mappedData['first_name'])) {
                                if (isset($rowData['first_name'])) $mappedData['first_name'] = $rowData['first_name'];
                                elseif (count($rowData) > 1) $mappedData['first_name'] = array_values($rowData)[1];
                            }
                            
                            if (!isset($mappedData['last_name'])) {
                                if (isset($rowData['last_name'])) $mappedData['last_name'] = $rowData['last_name'];
                                elseif (count($rowData) > 2) $mappedData['last_name'] = array_values($rowData)[2];
                                else $mappedData['last_name'] = 'Inconnu';
                            }

                            if (empty($mappedData['first_name'])) continue;

                            $familyId = null;
                            if (!empty($mappedData['widow_name'])) {
                                $familyId = Family::where('widow_name', 'like', '%' . $mappedData['widow_name'] . '%')->value('id');
                            }

                            Orphan::updateOrCreate(
                                [
                                    'first_name' => $mappedData['first_name'],
                                    'last_name' => $mappedData['last_name'],
                                ],
                                array_merge([
                                    'gender' => 'm',
                                    'status' => 'active',
                                    'family_id' => $familyId,
                                ], array_filter($mappedData, fn($k) => $k !== 'widow_name', ARRAY_FILTER_USE_KEY))
                            );
                            $count++;
                        }

                        if (Storage::disk('local')->exists($file)) {
                            Storage::disk('local')->delete($file);
                        }

                        Notification::make()
                            ->title('Importation terminée')
                            ->success()
                            ->body("$count orphelins ont été importés avec succès.")
                            ->send();

                    } catch (\Exception $e) {
                        Log::error("Direct Orphan Import Error: " . $e->getMessage());
                        Notification::make()->title("Erreur d'importation")->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
