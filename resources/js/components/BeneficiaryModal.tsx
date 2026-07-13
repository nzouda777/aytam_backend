import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { MapPin, Heart, Users } from "lucide-react";
import { router } from "@inertiajs/react";
import { useTranslation } from "@/lib/i18n";

interface BeneficiaryModalProps {
  open: boolean;
  onClose: () => void;
  beneficiary: {
    id: string;
    name: string;
    age?: number;
    location: string;
    story: string;
    monthlyNeed: number;
    image: string;
    sponsored: boolean;
    children?: number;
    members?: number;
  } | null;
  type: "orphan" | "widow" | "family";
}

const BeneficiaryModal = ({ open, onClose, beneficiary, type }: BeneficiaryModalProps) => {
  const { t } = useTranslation();

  if (!beneficiary) return null;

  const handleSponsor = () => {
    onClose();
    router.visit(`/sponsor-checkout?category=${type}&id=${beneficiary.id}`);
  };

  return (
    <Dialog open={open} onOpenChange={onClose}>
      <DialogContent className="max-w-lg max-h-[90vh] overflow-y-auto p-0">
        <div className="relative h-64 overflow-hidden rounded-t-lg">
          <img src={beneficiary.image} alt={beneficiary.name} className="w-full h-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
          <div className="absolute bottom-4 left-4 right-4">
            <span className="text-xs font-semibold bg-primary text-primary-foreground px-3 py-1 rounded-full">
              {t(`card.type.${type}`)}
            </span>
            <h2 className="text-2xl font-bold text-white mt-2">
              {beneficiary.name}{beneficiary.age ? `, ${beneficiary.age}` : ""}
            </h2>
            <div className="flex items-center gap-1 text-white/80 text-sm mt-1">
              <MapPin className="w-3.5 h-3.5" />
              <span>{beneficiary.location}</span>
            </div>
          </div>
        </div>

        <div className="p-6 space-y-5">
          {beneficiary.children !== undefined && (
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
              <Users className="w-4 h-4" />
              <span>{t("modal.children_dependent", { count: beneficiary.children })}</span>
            </div>
          )}
          {beneficiary.members !== undefined && (
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
              <Users className="w-4 h-4" />
              <span>{t("modal.family_members", { count: beneficiary.members })}</span>
            </div>
          )}

          <div>
            <h3 className="font-semibold text-foreground mb-2">{t("modal.their_story")}</h3>
            <p className="text-muted-foreground text-sm leading-relaxed whitespace-pre-line">{beneficiary.story}</p>
          </div>

          <div className="bg-secondary rounded-xl p-4 flex items-center justify-between">
            <div>
              <p className="text-xs text-muted-foreground">{t("modal.monthly_need")}</p>
              <p className="text-2xl font-bold text-primary">{beneficiary.monthlyNeed} FCFA<span className="text-sm font-normal text-muted-foreground">{t("modal.per_month")}</span></p>
            </div>
            {beneficiary.sponsored ? (
              <span className="text-accent font-semibold text-sm">{t("modal.already_sponsored")}</span>
            ) : (
              <Button onClick={handleSponsor} className="bg-accent text-accent-foreground hover:bg-accent/90 font-semibold px-6">
                <Heart className="w-4 h-4 mr-2" /> {t("modal.sponsor")}
              </Button>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default BeneficiaryModal;
