import { motion } from "framer-motion";
import { Heart, Shield, Globe, Users } from "lucide-react";
import Layout from "@/components/Layout";
import ImpactStats from "@/components/ImpactStats";
import { Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { useTranslation } from "@/lib/i18n";

const About = () => {
  const { t } = useTranslation();

  const values = [
    { icon: Heart, title: t("about.value1_title"), description: t("about.value1_text") },
    { icon: Shield, title: t("about.value2_title"), description: t("about.value2_text") },
    { icon: Globe, title: t("about.value3_title"), description: t("about.value3_text") },
    { icon: Users, title: t("about.value4_title"), description: t("about.value4_text") },
  ];

  return (
    <Layout>
      <div className="gradient-hero pattern-islamic py-20 relative overflow-hidden">
        <motion.div
          animate={{ y: [-10, 10, -10] }}
          transition={{ duration: 6, repeat: Infinity }}
          className="absolute top-10 right-20 w-20 h-20 border-2 border-primary-foreground/10 rounded-full"
        />
        <div className="container mx-auto px-4 text-center max-w-3xl relative z-10">
          <motion.h1
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="font-display text-3xl md:text-5xl font-bold text-foreground mb-4"
          >
            {t("about.title")}
          </motion.h1>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2, duration: 0.6 }}
            className="text-foreground/80 text-lg leading-relaxed"
          >
            {t("about.intro")}
          </motion.p>
        </div>
      </div>

      <section className="py-20 bg-background">
        <div className="container mx-auto px-4 max-w-4xl">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <motion.div initial={{ opacity: 0, x: -30 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} transition={{ duration: 0.6 }}>
              <h2 className="font-display text-3xl font-bold text-foreground mb-4">{t("about.mission_title")}</h2>
              <p className="text-muted-foreground leading-relaxed mb-4">
                {t("about.mission_p1")}
              </p>
              <p className="text-muted-foreground leading-relaxed">
                {t("about.mission_p2")}
              </p>
            </motion.div>
            <motion.div initial={{ opacity: 0, x: 30 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} transition={{ duration: 0.6 }}>
              <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600" alt={t("about.mission_alt")} className="rounded-xl shadow-lg w-full" />
            </motion.div>
          </div>
        </div>
      </section>

      <section className="py-20 bg-secondary">
        <div className="container mx-auto px-4">
          <motion.h2 initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="font-display text-3xl font-bold text-foreground text-center mb-12">{t("about.values_title")}</motion.h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {values.map((v, i) => (
              <motion.div
                key={v.title}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.15, duration: 0.5 }}
                whileHover={{ y: -5, scale: 1.02 }}
                className="bg-card rounded-xl p-6 text-center border border-border hover:shadow-lg transition-shadow"
              >
                <motion.div whileHover={{ rotate: 10 }} className="w-14 h-14 rounded-full bg-emerald-light mx-auto mb-4 flex items-center justify-center">
                  <v.icon className="w-7 h-7 text-primary" />
                </motion.div>
                <h3 className="font-display text-lg font-semibold text-card-foreground mb-2">{v.title}</h3>
                <p className="text-sm text-muted-foreground">{v.description}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      <ImpactStats />

      <section className="py-20 bg-background">
        <div className="container mx-auto px-4 text-center">
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}>
            <h2 className="font-display text-3xl font-bold text-foreground mb-4">{t("about.join_title")}</h2>
            <p className="text-muted-foreground max-w-xl mx-auto mb-8">
              {t("about.join_text")}
            </p>
            <div className="flex justify-center gap-4 flex-wrap">
              <Link href="/donate">
                <Button size="lg" className="bg-primary text-primary-foreground hover:bg-primary/90 font-semibold">
                  <Heart className="w-5 h-5 mr-2" /> {t("about.start_giving")}
                </Button>
              </Link>
              <Link href="/sponsorship">
                <Button size="lg" variant="outline" className="border-primary text-primary hover:bg-primary hover:text-primary-foreground font-semibold">
                  {t("about.browse_sponsorships")}
                </Button>
              </Link>
            </div>
          </motion.div>
        </div>
      </section>
    </Layout>
  );
};

export default About;
