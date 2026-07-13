import { useState, useMemo } from "react";
import { motion } from "framer-motion";
import { Search, Users } from "lucide-react";
import { Input } from "@/components/ui/input";
import Layout from "@/components/Layout";
import SponsorCard from "@/components/SponsorCard";
import BeneficiaryModal from "@/components/BeneficiaryModal";
import AnimatedCounter from "@/components/AnimatedCounter";
import { useTranslation } from "@/lib/i18n";

type Tab = "orphans" | "widows" | "families";

interface SponsorshipProps {
  beneficiaries: any[];
}

const calculateAge = (dateOfBirth: string): number => {
  const today = new Date();
  const birth = new Date(dateOfBirth);
  let age = today.getFullYear() - birth.getFullYear();
  const m = today.getMonth() - birth.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
  return age;
};

const Sponsorship = ({ beneficiaries }: SponsorshipProps) => {
  const { t } = useTranslation();
  const [activeTab, setActiveTab] = useState<Tab>("orphans");
  const [search, setSearch] = useState("");
  const [selectedBeneficiary, setSelectedBeneficiary] = useState<any | null>(null);
  const apiData = beneficiaries || [];

  // Transform API data into orphans, widows, and families lists
  const { orphans, widows, families } = useMemo(() => {
    const orphanMap = new Map<number, any>();
    const widowMap = new Map<number, any>();
    const familyMap = new Map<number, any>();

    for (const item of apiData) {
      const { orphan, family } = item;

      // --- Orphans (deduplicated by orphan.id) ---
      if (orphan && !orphanMap.has(orphan.id)) {
        orphanMap.set(orphan.id, {
          id: String(orphan.id),
          name: `${orphan.first_name} ${orphan.last_name}`,
          age: orphan.date_of_birth ? calculateAge(orphan.date_of_birth) : undefined,
          location: family?.city || t("sponsorship.not_specified"),
          story: [
            orphan.health_status && orphan.health_status !== "Bon état de santé"
              ? t("sponsorship.health", { status: orphan.health_status })
              : "",
            orphan.special_needs ? t("sponsorship.needs", { needs: orphan.special_needs }) : "",
            orphan.school_name ? t("sponsorship.schooled_at", { school: orphan.school_name, level: orphan.school_level }) : t("sponsorship.not_schooled"),
          ]
            .filter(Boolean)
            .join("\n") || t("sponsorship.awaiting_sponsorship"),
          monthlyNeed: parseFloat(item.monthly_amount) || 0,
          image: orphan.photo || "https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=400",
          sponsored: orphan.is_sponsored || false,
        });
      }

      // --- Widows (deduplicated by family.id, using widow info) ---
      if (family && !widowMap.has(family.id)) {
        widowMap.set(family.id, {
          id: `w-${family.id}`,
          name: family.widow_name,
          age: family.widow_date_of_birth ? calculateAge(family.widow_date_of_birth) : undefined,
          location: `${family.city}${family.region ? `, ${family.region}` : ""}`,
          story: [
            family.needs ? t("sponsorship.needs", { needs: family.needs }) + " " : "",
            family.notes || "",
          ]
            .filter(Boolean)
            .join("\n") || t("sponsorship.awaiting_support"),
          monthlyNeed: parseFloat(family.total_needs) - parseFloat(family.total_received) > 0
            ? Math.round((parseFloat(family.total_needs)) / 12)
            : 0,
          children: family.orphans_count || 0,
          image: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400",
          sponsored: family.status === "active",
        });
      }

      // --- Families (deduplicated by family.id) ---
      if (family && !familyMap.has(family.id)) {
        familyMap.set(family.id, {
          id: `f-${family.id}`,
          name: `${t("sponsorship.family_prefix")} ${family.widow_name.split(" ").pop()}`,
          location: `${family.city}${family.region ? `, ${family.region}` : ""}`,
          members: (family.orphans_count || 0) + 1, // orphans + widow
          story: [
            family.needs ? t("sponsorship.needs", { needs: family.needs }) + "  " : "",
            family.notes || "",
            family.address || "",
          ]
            .filter(Boolean)
            .join("\n") || t("sponsorship.family_awaiting"),
          monthlyNeed: parseFloat(family.total_needs) - parseFloat(family.total_received) > 0
            ? Math.round((parseFloat(family.total_needs) / 12))
            : 0,
          image: "https://images.unsplash.com/photo-1609220136736-443140cffec6?w=400",
          sponsored: family.status === "active",
        });
      }
    }

    return {
      orphans: Array.from(orphanMap.values()),
      widows: Array.from(widowMap.values()),
      families: Array.from(familyMap.values()),
    };
  }, [apiData, t]);

  const tabs: { key: Tab; label: string; count: number }[] = [
    { key: "orphans", label: t("sponsorship.tab.orphans"), count: orphans.length },
    { key: "widows", label: t("sponsorship.tab.widows"), count: widows.length },
    { key: "families", label: t("sponsorship.tab.families"), count: families.length },
  ];

  const getFiltered = () => {
    let data: any[] = [];
    if (activeTab === "orphans") data = orphans;
    else if (activeTab === "widows") data = widows;
    else data = families;

    if (search) {
      const q = search.toLowerCase();
      data = data.filter(
        (d: any) =>
          d.name.toLowerCase().includes(q) || d.location.toLowerCase().includes(q)
      );
    }
    return data;
  };

  const filtered = getFiltered();
  // Count unsponsored beneficiaries (reactive via useMemo)
  const totalUnsponsored = useMemo(() =>
    orphans.filter((o) => !o.sponsored).length +
    widows.filter((w) => !w.sponsored).length +
    families.filter((f) => !f.sponsored).length,
    [orphans, widows, families]
  );

  return (
    <Layout>
      {/* Enhanced Hero */}
      <div className="gradient-hero pattern-islamic py-20">
        <div className="container mx-auto px-4 text-center">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }}>
            <h1 className="font-display text-3xl md:text-5xl font-bold text-foreground mb-4">{t("sponsorship.title")}</h1>
            <p className="text-foreground/80 max-w-xl mx-auto mb-8">
              {t("sponsorship.subtitle")}
            </p>
            <motion.div
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 0.3 }}
              className="inline-flex items-center gap-3 bg-primary-foreground/10 backdrop-blur-sm border border-primary-foreground/20 rounded-full px-6 py-3"
            >
              <Users className="w-5 h-5 text-gold" />
              <span className="text-foreground font-semibold text-lg">
                <AnimatedCounter end={totalUnsponsored} /> {t("sponsorship.waiting")}
              </span>
            </motion.div>
          </motion.div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        {/* Search + Tabs */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
          <div className="flex gap-2">
            {tabs.map((tab) => (
              <button
                key={tab.key}
                onClick={() => setActiveTab(tab.key)}
                className={`px-6 py-2.5 rounded-full text-sm font-semibold transition-all ${activeTab === tab.key
                  ? "bg-primary text-primary-foreground shadow-md"
                  : "bg-secondary text-secondary-foreground hover:bg-muted"
                  }`}
              >
                {tab.label} ({tab.count})
              </button>
            ))}
          </div>
          <div className="relative max-w-xs w-full">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              placeholder={t("sponsorship.search_placeholder")}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Grid */}
        <motion.div
          key={activeTab + search}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
          {filtered.map((item: any) => (
            <SponsorCard
              key={item.id}
              {...item}
              type={activeTab === "orphans" ? "orphan" : activeTab === "widows" ? "widow" : "family"}
              onClick={() => setSelectedBeneficiary({ ...item, _type: activeTab === "orphans" ? "orphan" : activeTab === "widows" ? "widow" : "family" })}
            />
          ))}
          {filtered.length === 0 && (
            <div className="col-span-full text-center py-12 text-muted-foreground">
              {search ? t("sponsorship.no_results_for", { query: search }) : t("sponsorship.no_results")}
            </div>
          )}
        </motion.div>
      </div>

      {/* Modal */}
      <BeneficiaryModal
        open={!!selectedBeneficiary}
        onClose={() => setSelectedBeneficiary(null)}
        beneficiary={selectedBeneficiary}
        type={selectedBeneficiary?._type || "orphan"}
      />
    </Layout>
  );
};

export default Sponsorship;
