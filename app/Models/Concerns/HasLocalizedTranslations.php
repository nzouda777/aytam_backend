<?php

namespace App\Models\Concerns;

use Spatie\Translatable\HasTranslations;

/**
 * Variante de HasTranslations qui sérialise les attributs traduisibles
 * dans la locale courante (avec repli) au lieu du tableau complet de
 * traductions — les props Inertia et les réponses API restent donc des
 * chaînes simples pour le frontend.
 */
trait HasLocalizedTranslations
{
    use HasTranslations;

    public function toArray(): array
    {
        $attributes = parent::toArray();

        foreach ($this->getTranslatableAttributes() as $field) {
            $attributes[$field] = $this->getTranslation($field, app()->getLocale(), true);
        }

        return $attributes;
    }
}
