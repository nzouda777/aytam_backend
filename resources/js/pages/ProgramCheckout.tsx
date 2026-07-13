import { useState } from "react";
import { Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
  CreditCard,
  CheckCircle,
  ArrowLeft,
  Loader2,
  LucideIcon,
} from "lucide-react";
import Layout from "@/components/Layout";
import { toast } from "sonner";
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

const presetAmounts = [500, 1000, 2500, 5000, 10000, 25000];

interface ProgramData {
  id: number;
  slug: string;
  title: string;
  excerpt: string;
  icon: string | null;
  cta_type: "donate" | "sponsor";
}

interface ProgramCheckoutProps {
  program: ProgramData;
}

const ProgramCheckout = ({ program }: ProgramCheckoutProps) => {
  const { t } = useTranslation();
  const Icon = icons[program.icon || "Heart"] || Heart;

  const [step, setStep] = useState(1);
  const [amount, setAmount] = useState("");
  const [customAmount, setCustomAmount] = useState("");
  const [formData, setFormData] = useState({ firstName: "", lastName: "", email: "", phone: "" });
  const [paymentData, setPaymentData] = useState({ phoneNumber: "" });
  const [submitting, setSubmitting] = useState(false);

  const handleAmountChange = (val: string, isCustom: boolean) => {
    if (isCustom) {
      setCustomAmount(val);
      setAmount("");
    } else {
      setAmount(val);
      setCustomAmount("");
    }
  };

  const finalAmount = amount || customAmount;

  const isFieldValid = (val: string) => val.trim().length > 0;
  const isEmailValid = (val: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);

  const handleContinue = () => {
    if (!finalAmount || parseFloat(finalAmount) < 100) {
      toast.error(t("checkout.error.min_amount"));
      return;
    }
    setStep(2);
    window.scrollTo(0, 0);
  };

  const handlePayment = () => {
    if (!formData.firstName || !formData.email) {
      toast.error(t("checkout.error.required_fields"));
      return;
    }
    setStep(3);
    window.scrollTo(0, 0);
  };

  const handleConfirm = async () => {
    if (!paymentData.phoneNumber) {
      toast.error(t("checkout.error.phone_required"));
      return;
    }

    setSubmitting(true);

    try {
      const response = await fetch("/api/donations", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          donor_phone: paymentData.phoneNumber,
          donor_name: `${formData.firstName} ${formData.lastName}`.trim(),
          donor_email: formData.email,
          amount: finalAmount,
          program_id: program.id,
          payment_type: "program_donation",
          payment_method: "mobile_money",
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        toast.error(data.message || data.error || t("checkout.error.generic"));
        setSubmitting(false);
        return;
      }

      if (data.authorization_url) {
        toast.success(t("checkout.redirecting"));
        window.location.href = data.authorization_url;
      } else {
        toast.success(t("program_checkout.created"));
        setStep(4);
        setSubmitting(false);
      }
    } catch (error) {
      console.error("Program donation error:", error);
      toast.error(t("checkout.error.generic"));
      setSubmitting(false);
    }
  };

  return (
    <Layout>
      <div className="gradient-hero pattern-islamic py-16">
        <div className="container mx-auto px-4 text-center">
          <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="font-display text-3xl md:text-4xl font-bold text-foreground mb-2">
            {t("program_checkout.title")}
          </motion.h1>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12 max-w-2xl">
        {/* Bandeau programme */}
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-secondary rounded-xl p-4 mb-8 flex items-center gap-4 border border-border"
        >
          <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
            <Icon className="w-7 h-7 text-primary" />
          </div>
          <div className="flex-1">
            <p className="font-semibold text-foreground">{program.title}</p>
            <p className="text-sm text-muted-foreground line-clamp-2">{program.excerpt}</p>
          </div>
        </motion.div>

        {/* Étapes */}
        <div className="flex items-center justify-center gap-2 mb-10">
          {[1, 2, 3].map((s) => (
            <div key={s} className="flex items-center gap-2">
              <motion.div
                animate={step >= s ? { scale: [1, 1.2, 1] } : {}}
                className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors ${
                  step >= s ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
                }`}
              >
                {step > s ? <CheckCircle className="w-5 h-5" /> : s}
              </motion.div>
              {s < 3 && <div className={`w-12 h-0.5 transition-colors ${step > s ? "bg-primary" : "bg-muted"}`} />}
            </div>
          ))}
        </div>

        {/* Étape 1 : Montant */}
        {step === 1 && (
          <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-8">
            <div>
              <h2 className="font-display text-2xl font-bold text-foreground mb-4">{t("program_checkout.amount_title")}</h2>
              <div className="grid grid-cols-3 gap-3 mb-4">
                {presetAmounts.map((a) => (
                  <motion.button
                    key={a}
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={() => handleAmountChange(a.toString(), false)}
                    className={`py-3 rounded-lg border text-center font-semibold transition-all ${
                      amount === a.toString()
                        ? "border-primary bg-primary text-primary-foreground"
                        : "border-border bg-card text-card-foreground hover:border-primary/50"
                    }`}
                  >
                    {a.toLocaleString()} FCFA
                  </motion.button>
                ))}
              </div>
              <div>
                <Label className="text-sm text-muted-foreground">{t("checkout.custom_amount")}</Label>
                <div className="relative mt-1">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">FCFA</span>
                  <Input
                    type="number"
                    placeholder="0"
                    className="pl-14"
                    value={customAmount}
                    onChange={(e) => handleAmountChange(e.target.value, true)}
                  />
                </div>
              </div>
            </div>

            <Button onClick={handleContinue} size="lg" className="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-semibold">
              {t("checkout.continue")} — {finalAmount ? `${parseInt(finalAmount).toLocaleString()} FCFA` : "0 FCFA"}
            </Button>
          </motion.div>
        )}

        {/* Étape 2 : Informations */}
        {step === 2 && (
          <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-6">
            <button onClick={() => setStep(1)} className="text-sm text-primary flex items-center gap-1 hover:underline">
              <ArrowLeft className="w-4 h-4" /> {t("checkout.back")}
            </button>
            <h2 className="font-display text-2xl font-bold text-foreground">{t("checkout.your_info")}</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label>{t("checkout.first_name")}</Label>
                <div className="relative">
                  <Input value={formData.firstName} onChange={(e) => setFormData({ ...formData, firstName: e.target.value })} placeholder={t("checkout.first_name_placeholder")} />
                  {isFieldValid(formData.firstName) && <CheckCircle className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary" />}
                </div>
              </div>
              <div>
                <Label>{t("checkout.last_name")}</Label>
                <Input value={formData.lastName} onChange={(e) => setFormData({ ...formData, lastName: e.target.value })} placeholder={t("checkout.last_name_placeholder")} />
              </div>
            </div>
            <div>
              <Label>{t("checkout.email")}</Label>
              <div className="relative">
                <Input type="email" value={formData.email} onChange={(e) => setFormData({ ...formData, email: e.target.value })} placeholder={t("checkout.email_placeholder")} />
                {isEmailValid(formData.email) && <CheckCircle className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary" />}
              </div>
            </div>
            <div>
              <Label>{t("checkout.phone_optional")}</Label>
              <Input value={formData.phone} onChange={(e) => setFormData({ ...formData, phone: e.target.value })} placeholder="6 XX XXX XXXX" />
            </div>
            <Button onClick={handlePayment} size="lg" className="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-semibold">
              {t("checkout.continue_payment")}
            </Button>
          </motion.div>
        )}

        {/* Étape 3 : Paiement */}
        {step === 3 && (
          <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-6">
            <button onClick={() => setStep(2)} className="text-sm text-primary flex items-center gap-1 hover:underline">
              <ArrowLeft className="w-4 h-4" /> {t("checkout.back")}
            </button>
            <h2 className="font-display text-2xl font-bold text-foreground">{t("checkout.payment_details")}</h2>

            <div className="bg-secondary rounded-lg p-4 mb-4">
              <div className="flex justify-between text-sm mb-1">
                <span className="text-muted-foreground">{t("program_checkout.program_label")}</span>
                <span className="font-medium text-foreground">{program.title}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">{t("checkout.amount_label")}</span>
                <span className="font-bold text-primary text-lg">{parseInt(finalAmount).toLocaleString()} FCFA</span>
              </div>
            </div>

            <div>
              <Label>{t("checkout.momo_number")}</Label>
              <div className="relative">
                <CreditCard className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  className="pl-10 pr-10"
                  value={paymentData.phoneNumber}
                  onChange={(e) => setPaymentData({ ...paymentData, phoneNumber: e.target.value })}
                  placeholder="6 XX XXX XXX"
                  maxLength={9}
                />
                {paymentData.phoneNumber.length >= 9 && <CheckCircle className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary" />}
              </div>
            </div>

            <Button
              onClick={handleConfirm}
              disabled={submitting}
              size="lg"
              className="w-full bg-accent text-accent-foreground hover:bg-accent/90 font-semibold"
            >
              {submitting ? (
                <>
                  <Loader2 className="w-5 h-5 mr-2 animate-spin" /> {t("checkout.processing")}
                </>
              ) : (
                <>
                  <Heart className="w-5 h-5 mr-2" /> {t("program_checkout.confirm")} — {parseInt(finalAmount).toLocaleString()} FCFA
                </>
              )}
            </Button>
            <p className="text-xs text-muted-foreground text-center">{t("checkout.secure_notchpay")}</p>
          </motion.div>
        )}

        {/* Étape 4 : Confirmation (si pas de redirection paiement) */}
        {step === 4 && (
          <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ type: "spring", duration: 0.6 }} className="text-center space-y-6 py-8">
            <motion.div
              initial={{ scale: 0 }}
              animate={{ scale: 1 }}
              transition={{ type: "spring", delay: 0.2 }}
              className="w-20 h-20 rounded-full bg-emerald-light mx-auto flex items-center justify-center"
            >
              <CheckCircle className="w-10 h-10 text-primary" />
            </motion.div>

            <h2 className="font-display text-3xl font-bold text-foreground">{t("checkout.thank_you")}</h2>
            <p className="text-muted-foreground max-w-md mx-auto">
              {t("program_checkout.received_before")} <strong className="text-primary">{parseInt(finalAmount).toLocaleString()} FCFA</strong>{" "}
              {t("program_checkout.received_middle")} <strong>{program.title}</strong> {t("program_checkout.received_after")}
            </p>
            <div className="flex flex-wrap justify-center gap-3 pt-4">
              <Link href={`/programs/${program.slug}`}>
                <Button variant="outline" className="border-primary text-primary hover:bg-primary hover:text-primary-foreground">
                  {t("program_checkout.back_to_program")}
                </Button>
              </Link>
              <Link href="/">
                <Button variant="outline" className="border-primary text-primary hover:bg-primary hover:text-primary-foreground">
                  {t("checkout.back_home")}
                </Button>
              </Link>
            </div>
          </motion.div>
        )}
      </div>
    </Layout>
  );
};

export default ProgramCheckout;
