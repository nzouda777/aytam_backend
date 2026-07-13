import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import {
  Heart,
  HandHeart,
  Home as HomeIcon,
  GraduationCap,
  Siren,
  Coins,
  Users,
  Shield,
  ArrowRight,
  LucideIcon,
} from "lucide-react";
import Layout from "@/components/Layout";
import { Button } from "@/components/ui/button";
import { DonationCause } from "@/data/mockData";
import { useTranslation } from "@/lib/i18n";

const icons: Record<string, LucideIcon> = {
  Heart,
  HandHeart,
  Home: HomeIcon,
  GraduationCap,
  Siren,
  Coins,
  Users,
  Shield,
};

interface ProgramData {
  id: number;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  icon: string | null;
  image: string | null;
  cta_type: "donate" | "sponsor";
}

interface ProgramProps {
  program: ProgramData;
  campaigns: DonationCause[];
}

const Program = ({ program, campaigns }: ProgramProps) => {
  const { t } = useTranslation();
  const Icon = icons[program.icon || "Heart"] || Heart;
  const ctaHref = `/programs/${program.slug}/participate`;
  const ctaLabel = program.cta_type === "sponsor" ? t("program.cta_sponsor") : t("program.cta_donate");

  return (
    <Layout>
      {/* Hero */}
      <div className="gradient-hero pattern-islamic py-20 relative overflow-hidden">
        <motion.div
          animate={{ y: [-10, 10, -10] }}
          transition={{ duration: 6, repeat: Infinity }}
          className="absolute top-10 right-20 w-20 h-20 border-2 border-primary-foreground/10 rounded-full"
        />
        <div className="container mx-auto px-4 text-center max-w-3xl relative z-10">
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ type: "spring", duration: 0.6 }}
            className="w-20 h-20 rounded-full bg-primary-foreground/10 backdrop-blur-sm border border-primary-foreground/20 mx-auto mb-6 flex items-center justify-center"
          >
            <Icon className="w-10 h-10 text-gold" />
          </motion.div>
          <motion.h1
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="font-display text-3xl md:text-5xl font-bold text-foreground mb-4"
          >
            {program.title}
          </motion.h1>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2, duration: 0.6 }}
            className="text-foreground/80 text-lg leading-relaxed"
          >
            {program.excerpt}
          </motion.p>
        </div>
      </div>

      {/* Image de couverture */}
      {program.image && (
        <div className="container mx-auto px-4 max-w-4xl">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="rounded-2xl overflow-hidden -mt-10 relative z-10 shadow-2xl"
          >
            <img src={program.image} alt={program.title} className="w-full h-72 md:h-96 object-cover" />
          </motion.div>
        </div>
      )}

      {/* Contenu */}
      <section className="py-16 bg-background">
        <div className="container mx-auto px-4 max-w-3xl">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-foreground/80 leading-relaxed text-base md:text-lg [&_p]:mb-5 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:mb-4 [&_h2]:text-foreground [&_h3]:font-display [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:mb-3 [&_h3]:text-foreground [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:mb-5"
            dangerouslySetInnerHTML={{ __html: program.content }}
          />
        </div>
      </section>

      {/* Campagnes liées */}
      {campaigns.length > 0 && (
        <section className="py-16 bg-secondary">
          <div className="container mx-auto px-4">
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-12">
              <h2 className="font-display text-3xl font-bold text-foreground mb-3">{t("program.related_campaigns")}</h2>
              <p className="text-muted-foreground">{t("program.related_campaigns_subtitle")}</p>
            </motion.div>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {campaigns.map((cause, i) => (
                <motion.div
                  key={cause.id}
                  initial={{ opacity: 0, y: 30 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: i * 0.1 }}
                  whileHover={{ y: -5 }}
                  className="bg-card border border-border rounded-xl p-6 transition-all hover:shadow-lg"
                >
                  <h3 className="font-display text-xl font-semibold text-card-foreground mb-2">{cause.title}</h3>
                  <p className="text-sm text-muted-foreground mb-4 line-clamp-3">{cause.description}</p>
                  <div className="w-full bg-muted rounded-full h-2.5 mb-2 overflow-hidden">
                    <motion.div
                      className="h-full rounded-full gradient-gold"
                      initial={{ width: "0%" }}
                      whileInView={{ width: `${(cause.current_amount / cause.goal_amount) * 100}%` }}
                      viewport={{ once: true }}
                      transition={{ duration: 1.2, ease: "easeOut" }}
                    />
                  </div>
                  <div className="flex justify-between text-sm mb-4">
                    <span className="text-primary font-semibold">{t("home.raised", { amount: cause.current_amount })}</span>
                    <span className="text-muted-foreground">{t("home.of_goal", { amount: cause.goal_amount })}</span>
                  </div>
                  <Link href={`/donate?cause=${cause.id}`}>
                    <Button className="w-full bg-primary text-primary-foreground hover:bg-primary/90" size="sm">
                      {t("home.donate_cause")}
                    </Button>
                  </Link>
                </motion.div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      <section className="gradient-hero pattern-islamic py-16 relative overflow-hidden">
        <div className="container mx-auto px-4 text-center relative z-10">
          <motion.div initial={{ opacity: 0, y: 30 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}>
            <h2 className="font-display text-2xl md:text-3xl font-bold text-foreground mb-3">{t("program.cta_title")}</h2>
            <p className="text-foreground/80 max-w-xl mx-auto mb-8">{t("program.cta_text")}</p>
            <Link href={ctaHref}>
              <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }} className="inline-block">
                <Button size="lg" className="bg-accent text-accent-foreground hover:bg-accent/90 font-semibold px-8 shadow-xl">
                  <Heart className="w-5 h-5 mr-2" /> {ctaLabel} <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
              </motion.div>
            </Link>
          </motion.div>
        </div>
      </section>
    </Layout>
  );
};

export default Program;
