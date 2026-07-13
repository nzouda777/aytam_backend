import { useEffect } from "react";
import { Link } from "@inertiajs/react";
import { useTranslation } from "@/lib/i18n";

const NotFound = () => {
  const { t } = useTranslation();

  useEffect(() => {
    console.error("404 Error: User attempted to access non-existent route:", window.location.pathname);
  }, []);

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted">
      <div className="text-center">
        <h1 className="mb-4 text-4xl font-bold">404</h1>
        <p className="mb-4 text-xl text-muted-foreground">{t("notfound.message")}</p>
        <Link href="/" className="text-primary underline hover:text-primary/90">
          {t("notfound.back_home")}
        </Link>
      </div>
    </div>
  );
};

export default NotFound;
