<?php

namespace App\Filament\Resources\FamilyResource\Pages;

use App\Filament\Resources\FamilyResource;
use App\Models\Family;
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

class ListFamilies extends ListRecords
{
    protected static string $resource = FamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Importer (Direct)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
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
                                if (str_contains($h, 'nom') && str_contains($h, 'veuve')) $mappedData['widow_name'] = $value;
                                elseif (str_contains($h, 'phone') || str_contains($h, 'téléphone') || str_contains($h, 'tel')) $mappedData['widow_phone'] = $value;
                                elseif (str_contains($h, 'email')) $mappedData['widow_email'] = $value;
                                elseif (str_contains($h, 'adresse') || str_contains($h, 'quartier')) $mappedData['address'] = $value;
                                elseif (str_contains($h, 'ville') || str_contains($h, 'city')) $mappedData['city'] = $value;
                            }

                            // Fallback for widow_name if matching header not found
                            if (!isset($mappedData['widow_name'])) {
                                if (isset($rowData['widow_name'])) $mappedData['widow_name'] = $rowData['widow_name'];
                                elseif (count($rowData) > 1) $mappedData['widow_name'] = array_values($rowData)[1]; // Guess 2nd column
                            }

                            if (empty($mappedData['widow_name'])) continue;

                            Family::updateOrCreate(
                                ['widow_name' => $mappedData['widow_name']],
                                array_merge([
                                    'status' => 'active',
                                    'city' => 'Inconnu',
                                    'address' => 'Inconnu',
                                    'registration_date' => now(),
                                ], array_filter($mappedData))
                            );
                            $count++;
                        }

                        if (Storage::disk('local')->exists($file)) {
                            Storage::disk('local')->delete($file);
                        }

                        Notification::make()
                            ->title('Importation terminée')
                            ->success()
                            ->body("$count familles ont été importées avec succès.")
                            ->send();

                    } catch (\Exception $e) {
                        Log::error("Direct Import Error: " . $e->getMessage());
                        Notification::make()->title("Erreur d'importation")->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
