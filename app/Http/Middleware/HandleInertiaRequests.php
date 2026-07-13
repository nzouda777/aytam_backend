<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'locale' => $locale,
            'translations' => $this->translations($locale),
            // Liens "Programmes" du footer, gérés dans le backoffice
            'programs' => fn () => \App\Models\Program::active()
                ->get(['id', 'slug', 'title'])
                ->map(fn ($program) => [
                    'slug' => $program->slug,
                    'title' => $program->title,
                ]),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }

    /**
     * Charge le dictionnaire JSON de la locale courante pour le frontend.
     *
     * @return array<string, string>
     */
    protected function translations(string $locale): array
    {
        $path = lang_path("{$locale}.json");

        if (! File::exists($path)) {
            $path = lang_path('fr.json');
        }

        return File::exists($path)
            ? json_decode(File::get($path), true)
            : [];
    }
}
