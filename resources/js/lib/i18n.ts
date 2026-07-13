import { usePage, router } from "@inertiajs/react";

export type Locale = "fr" | "en";

interface SharedI18nProps {
    locale: Locale;
    translations: Record<string, string>;
    [key: string]: unknown;
}

/**
 * Traductions partagées par le backend Laravel via Inertia (middleware
 * HandleInertiaRequests). `t` remplace les paramètres de la forme :param.
 */
export function useTranslation() {
    const { locale, translations } = usePage<SharedI18nProps>().props;

    const t = (key: string, params: Record<string, string | number> = {}): string => {
        let text = translations?.[key] ?? key;
        for (const [name, value] of Object.entries(params)) {
            text = text.replace(new RegExp(`:${name}`, "g"), String(value));
        }
        return text;
    };

    const setLocale = (newLocale: Locale) => {
        if (newLocale === locale) return;
        router.post(
            "/locale",
            { locale: newLocale },
            { preserveScroll: true, preserveState: false }
        );
    };

    return { t, locale, setLocale };
}
