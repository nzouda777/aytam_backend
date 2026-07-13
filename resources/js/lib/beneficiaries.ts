import { BeneficiaryCard } from "@/data/mockData";

export const calculateAge = (dateOfBirth: string): number => {
  const today = new Date();
  const birth = new Date(dateOfBirth);
  let age = today.getFullYear() - birth.getFullYear();
  const m = today.getMonth() - birth.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
  return age;
};

const DEFAULT_ORPHAN_IMAGE = "https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=400";

type Translator = (key: string, params?: Record<string, string | number>) => string;

/** Transforme un orphelin brut du backend (avec sa famille) en carte d'affichage. */
export function orphanToCard(orphan: any, t: Translator): BeneficiaryCard {
  const family = orphan.family;
  return {
    id: String(orphan.id),
    name: `${orphan.first_name} ${orphan.last_name}`,
    age: orphan.date_of_birth ? calculateAge(orphan.date_of_birth) : undefined,
    location: family?.city || t("sponsorship.not_specified"),
    story:
      [
        orphan.health_status && orphan.health_status !== "Bon état de santé"
          ? t("sponsorship.health", { status: orphan.health_status })
          : "",
        orphan.special_needs ? t("sponsorship.needs", { needs: orphan.special_needs }) : "",
        orphan.school_name
          ? t("sponsorship.schooled_at", { school: orphan.school_name, level: orphan.school_level })
          : t("sponsorship.not_schooled"),
      ]
        .filter(Boolean)
        .join("\n") || t("sponsorship.awaiting_sponsorship"),
    monthlyNeed: family?.total_needs ? Math.round(parseFloat(family.total_needs) / 12) : 0,
    image: orphan.photo || DEFAULT_ORPHAN_IMAGE,
    sponsored: orphan.is_sponsored || false,
  };
}
