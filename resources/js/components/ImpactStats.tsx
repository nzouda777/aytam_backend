import { motion } from "framer-motion";
import { usePage } from "@inertiajs/react";
import { Users, Globe, DollarSign, HeartHandshake } from "lucide-react";
import AnimatedCounter from "./AnimatedCounter";
import { useTranslation } from "@/lib/i18n";
import { ImpactStatsData } from "@/data/mockData";

const ImpactStats = () => {
  const { t } = useTranslation();
  const { impactStats } = usePage<{ impactStats?: ImpactStatsData }>().props;

  const data: ImpactStatsData = impactStats || {
    orphansSponsored: 0,
    regionsServed: 0,
    donationsRaised: 0,
    activeDonors: 0,
  };

  // Les montants importants sont affichés en millions de FCFA
  const donationsInMillions = data.donationsRaised >= 1_000_000;
  const donationsValue = donationsInMillions
    ? Math.round(data.donationsRaised / 100_000) // AnimatedCounter divise par 10^decimals
    : Math.round(data.donationsRaised);

  const stats = [
    { label: t("impact.orphans_sponsored"), value: data.orphansSponsored, icon: Users, suffix: "" },
    { label: t("impact.regions_served"), value: data.regionsServed, icon: Globe, suffix: "" },
    {
      label: t("impact.donations_raised"),
      value: donationsValue,
      icon: DollarSign,
      suffix: donationsInMillions ? " M FCFA" : " FCFA",
      decimals: donationsInMillions ? 1 : 0,
    },
    { label: t("impact.active_donors"), value: data.activeDonors, icon: HeartHandshake, suffix: "+" },
  ];

  return (
    <section className="py-16 bg-secondary">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
          {stats.map((stat, i) => (
            <motion.div
              key={stat.label}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.15, duration: 0.6 }}
              className="text-center"
            >
              <motion.div
                className="w-14 h-14 rounded-full bg-primary/10 mx-auto mb-3 flex items-center justify-center animate-pulse-glow"
                whileHover={{ scale: 1.1 }}
              >
                <stat.icon className="w-7 h-7 text-primary" />
              </motion.div>
              <p className="font-display text-3xl md:text-4xl font-bold text-foreground">
                <AnimatedCounter end={stat.value} suffix={stat.suffix} decimals={stat.decimals || 0} />
              </p>
              <p className="text-sm text-muted-foreground mt-1">{stat.label}</p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default ImpactStats;
