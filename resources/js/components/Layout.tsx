import { useState, useEffect } from "react";
import { Link, usePage } from "@inertiajs/react";
import { Heart, Menu, X, Facebook, Instagram, Youtube, Mail, Phone, MapPin, ArrowUp } from "lucide-react";
import { Button } from "@/components/ui/button";
import { motion, AnimatePresence } from "framer-motion";
import { useTranslation } from "@/lib/i18n";
import LanguageToggle from "@/components/LanguageToggle";

const Layout = ({ children }: { children: React.ReactNode }) => {
  const { url, props } = usePage<{ programs?: { slug: string; title: string }[] }>();
  const { t } = useTranslation();
  const pathname = url.split("?")[0];
  const [mobileOpen, setMobileOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [showScrollTop, setShowScrollTop] = useState(false);

  const navItems = [
    { label: t("nav.home"), path: "/" },
    { label: t("nav.donate"), path: "/donate" },
    { label: t("nav.sponsorship"), path: "/sponsorship" },
    { label: t("nav.blog"), path: "/blog" },
    { label: t("nav.about"), path: "/about" },
  ];

  // Programmes gérés dans le backoffice (partagés via Inertia)
  const programs = props.programs || [];

  useEffect(() => {
    const onScroll = () => {
      setScrolled(window.scrollY > 50);
      setShowScrollTop(window.scrollY > 500);
    };
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const scrollToTop = () => window.scrollTo({ top: 0, behavior: "smooth" });

  return (
    <div className="min-h-screen flex flex-col bg-background">
      {/* Navbar */}
      <motion.header
        initial={{ y: -80 }}
        animate={{ y: 0 }}
        transition={{ duration: 0.5, ease: "easeOut" }}
        className={`sticky top-0 z-50 border-b transition-all duration-300 ${
          scrolled ? "bg-card/95 backdrop-blur-lg shadow-md border-border" : "bg-card/60 backdrop-blur-md border-transparent"
        }`}
      >
        <div className="container mx-auto flex items-center justify-between py-4 px-4">
          <Link href="/" className="flex items-center gap-2">
            <motion.div whileHover={{ rotate: 10, scale: 1.1 }} className="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
              <Heart className="w-5 h-5 text-primary-foreground" />
            </motion.div>
            <span className="font-display text-xl font-bold text-foreground">Al-<span className="text-gradient-gold">Aytaam</span></span>
          </Link>
          <nav className="hidden md:flex items-center gap-8">
            {navItems.map((item) => (
              <Link key={item.path} href={item.path} className={`text-sm font-medium transition-colors relative hover:text-primary ${pathname === item.path ? "text-primary" : "text-muted-foreground"}`}>
                {item.label}
                {pathname === item.path && (
                  <motion.div layoutId="nav-underline" className="absolute -bottom-1 left-0 right-0 h-0.5 bg-primary rounded-full" />
                )}
              </Link>
            ))}
          </nav>
          <div className="hidden md:flex items-center gap-3">
            <LanguageToggle />
            <Link href="/donate">
              <Button className="bg-primary text-primary-foreground hover:bg-primary/90 font-semibold">{t("nav.donate_button")}</Button>
            </Link>
          </div>
          <div className="md:hidden flex items-center gap-3">
            <LanguageToggle />
            <button className="text-foreground" onClick={() => setMobileOpen(!mobileOpen)}>
              {mobileOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>
        <AnimatePresence>
          {mobileOpen && (
            <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} className="md:hidden overflow-hidden border-t border-border bg-card">
              <nav className="flex flex-col p-4 gap-3">
                {navItems.map((item) => (
                  <Link key={item.path} href={item.path} onClick={() => setMobileOpen(false)} className={`text-sm font-medium py-2 ${pathname === item.path ? "text-primary" : "text-muted-foreground"}`}>
                    {item.label}
                  </Link>
                ))}
                <Link href="/donate" onClick={() => setMobileOpen(false)}>
                  <Button className="w-full bg-primary text-primary-foreground mt-2">{t("nav.donate_button")}</Button>
                </Link>
              </nav>
            </motion.div>
          )}
        </AnimatePresence>
      </motion.header>

      <main className="flex-1">{children}</main>

      {/* Footer */}
      <footer className="bg-foreground text-background relative overflow-hidden">
        <div className="absolute top-0 left-0 right-0 h-1 gradient-gold" />

        <div className="container mx-auto px-4 pt-16 pb-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            {/* Brand */}
            <div>
              <div className="flex items-center gap-2 mb-5">
                <div className="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                  <Heart className="w-5 h-5 text-primary-foreground" />
                </div>
                <span className="font-display text-xl font-bold">Al-<span className="text-accent">Aytaam</span></span>
              </div>
              <p className="text-background/60 text-sm leading-relaxed mb-6">
                {t("footer.tagline")}
              </p>
              <div className="flex gap-3">
                {[Facebook, Instagram, Youtube].map((Icon, i) => (
                  <motion.a
                    key={i}
                    href="#"
                    whileHover={{ scale: 1.15, y: -2 }}
                    className="w-9 h-9 rounded-full bg-background/10 flex items-center justify-center hover:bg-primary transition-colors"
                  >
                    <Icon className="w-4 h-4" />
                  </motion.a>
                ))}
              </div>
            </div>

            {/* Liens rapides */}
            <div>
              <h4 className="font-display font-semibold mb-5 text-sm uppercase tracking-wider">{t("footer.quick_links")}</h4>
              <div className="flex flex-col gap-3">
                {navItems.map((item) => (
                  <Link key={item.path} href={item.path} className="text-sm text-background/60 hover:text-accent transition-colors flex items-center gap-1 group">
                    <span className="w-0 group-hover:w-2 h-0.5 bg-accent transition-all duration-200" />
                    {item.label}
                  </Link>
                ))}
              </div>
            </div>

            {/* Programmes */}
            <div>
              <h4 className="font-display font-semibold mb-5 text-sm uppercase tracking-wider">{t("footer.programs")}</h4>
              <div className="flex flex-col gap-3 text-sm text-background/60">
                {programs.map((p) => (
                  <Link key={p.slug} href={`/programs/${p.slug}`} className="hover:text-accent transition-colors flex items-center gap-1 group">
                    <span className="w-0 group-hover:w-2 h-0.5 bg-accent transition-all duration-200" />
                    {p.title}
                  </Link>
                ))}
              </div>
            </div>

            {/* Contact */}
            <div>
              <h4 className="font-display font-semibold mb-5 text-sm uppercase tracking-wider">{t("footer.contact_us")}</h4>
              <div className="flex flex-col gap-4 text-sm text-background/60">
                <a href="mailto:info@al-aytaam.org" className="flex items-center gap-3 hover:text-accent transition-colors">
                  <Mail className="w-4 h-4 text-accent" /> info@al-aytaam.org
                </a>
                <a href="tel:+15551234567" className="flex items-center gap-3 hover:text-accent transition-colors">
                  <Phone className="w-4 h-4 text-accent" /> +237 6 000 000 000
                </a>
                <span className="flex items-start gap-3">
                  <MapPin className="w-4 h-4 text-accent mt-0.5 shrink-0" /> Mosque AL-Rawda face UCB, Douala, Cameroun
                </span>
              </div>

              {/* Mini newsletter */}
              <div className="mt-6">
                <p className="text-xs text-background/40 mb-2">{t("footer.newsletter_hint")}</p>
                <div className="flex gap-2">
                  <input type="email" placeholder={t("footer.email_placeholder")} className="flex-1 px-3 py-2 rounded-lg bg-background/10 border border-background/10 text-sm placeholder:text-background/30 focus:outline-none focus:border-accent/50 transition-colors" />
                  <Button size="sm" className="bg-accent text-accent-foreground hover:bg-accent/90 rounded-lg px-3">
                    <Mail className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </div>
          </div>

          {/* Bottom bar */}
          <div className="border-t border-background/10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <p className="text-xs text-background/40">{t("footer.rights")}</p>
            <div className="flex gap-6 text-xs text-background/40">
              <span className="hover:text-accent transition-colors cursor-pointer">{t("footer.privacy")}</span>
              <span className="hover:text-accent transition-colors cursor-pointer">{t("footer.terms")}</span>
              <span className="hover:text-accent transition-colors cursor-pointer">{t("footer.annual_report")}</span>
            </div>
          </div>
        </div>
      </footer>

      {/* Scroll to top */}
      <AnimatePresence>
        {showScrollTop && (
          <motion.button
            initial={{ opacity: 0, scale: 0.5 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.5 }}
            onClick={scrollToTop}
            className="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-full bg-primary text-primary-foreground shadow-lg flex items-center justify-center hover:bg-primary/90 transition-colors"
          >
            <ArrowUp className="w-5 h-5" />
          </motion.button>
        )}
      </AnimatePresence>
    </div>
  );
};

export default Layout;
