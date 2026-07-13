import { Globe } from "lucide-react";
import { motion } from "framer-motion";
import { useTranslation, Locale } from "@/lib/i18n";

const locales: { code: Locale; label: string }[] = [
    { code: "fr", label: "FR" },
    { code: "en", label: "EN" },
];

const LanguageToggle = () => {
    const { locale, setLocale } = useTranslation();

    return (
        <div className="flex items-center gap-1 rounded-full border border-border bg-card/60 p-1">
            <Globe className="w-4 h-4 text-muted-foreground ml-1.5" />
            {locales.map(({ code, label }) => (
                <button
                    key={code}
                    onClick={() => setLocale(code)}
                    className={`relative px-2.5 py-1 rounded-full text-xs font-semibold transition-colors ${
                        locale === code
                            ? "text-primary-foreground"
                            : "text-muted-foreground hover:text-primary"
                    }`}
                    aria-pressed={locale === code}
                >
                    {locale === code && (
                        <motion.span
                            layoutId="locale-pill"
                            className="absolute inset-0 rounded-full bg-primary"
                            transition={{ duration: 0.2 }}
                        />
                    )}
                    <span className="relative z-10">{label}</span>
                </button>
            ))}
        </div>
    );
};

export default LanguageToggle;
