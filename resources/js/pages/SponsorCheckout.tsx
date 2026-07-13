import { useState, useMemo } from "react";
import { router } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { motion } from "framer-motion";
import { Heart, CreditCard, CheckCircle, ArrowLeft, MapPin, Star, Download, Loader2 } from "lucide-react";
import Layout from "@/components/Layout";
import { toast } from "sonner";
import { useTranslation } from "@/lib/i18n";

const presetAmounts = [500, 1000, 2000, 5000, 10000, 25000];

const calculateAge = (dateOfBirth: string): number => {
    const today = new Date();
    const birth = new Date(dateOfBirth);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    return age;
};

interface SponsorCheckoutProps {
    beneficiaryData: any | null;
    category: string | null; // "orphan" | "widow" | "family"
}

const SponsorCheckout = ({ beneficiaryData, category: categoryParam }: SponsorCheckoutProps) => {
    const { t } = useTranslation();

    const [step, setStep] = useState(1);
    const [amount, setAmount] = useState("");
    const [customAmount, setCustomAmount] = useState("");
    const [paymentFrequency, setPaymentFrequency] = useState<"monthly" | "quarterly" | "yearly">("monthly");
    const [formData, setFormData] = useState({ firstName: "", lastName: "", email: "", phone: "" });
    const [paymentData, setPaymentData] = useState({ phoneNumber: "" });
    const [submitting, setSubmitting] = useState(false);

    // Transformation des données brutes du backend en forme affichable
    const beneficiary = useMemo(() => {
        const item = beneficiaryData;
        if (!item || !categoryParam) return null;

        if (categoryParam === "orphan") {
            return {
                id: item.id,
                name: `${item.first_name} ${item.last_name}`,
                age: item.date_of_birth ? calculateAge(item.date_of_birth) : undefined,
                location: item.family?.city || t("sponsorship.not_specified"),
                monthlyNeed: 0,
                image: item.photo || "https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=400",
                sponsored: item.is_sponsored,
                orphan_id: item.id,
                family_id: item.family_id,
                sponsorship_type: item.sponsorship_type,
            };
        }
        return {
            id: item.id,
            name: categoryParam === "widow" ? item.widow_name : `${t("sponsorship.family_prefix")} ${(item.widow_name || "").split(" ").pop()}`,
            location: `${item.city || ""}${item.region ? `, ${item.region}` : ""}`,
            monthlyNeed: item.total_needs ? Math.round(parseFloat(item.total_needs) / 12) : 0,
            image: categoryParam === "widow"
                ? "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400"
                : "https://images.unsplash.com/photo-1609220136736-443140cffec6?w=400",
            sponsored: item.status === "active",
            family_id: item.id,
            orphan_id: null,
            sponsorship_type: item.sponsorship_type,
            children: item.orphans_count,
            members: (item.orphans_count || 0) + 1,
        };
    }, [beneficiaryData, categoryParam, t]);

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

    const handleDonate = () => {
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
            const today = new Date().toISOString().split("T")[0];

            const payload: Record<string, any> = {
                monthly_amount: parseFloat(finalAmount),
                start_date: today,
                payment_frequency: paymentFrequency,
                sponsorship_type: categoryParam,
                donor_name: `${formData.firstName} ${formData.lastName}`.trim(),
                donor_email: formData.email,
                donor_phone: paymentData.phoneNumber,
            };

            // Send the appropriate ID based on category
            if (categoryParam === "orphan" && beneficiary?.orphan_id) {
                payload.orphan_id = beneficiary.orphan_id;
                payload.family_id = beneficiary.family_id;
            } else if (beneficiary?.family_id) {
                payload.family_id = beneficiary.family_id;
            }

            const response = await fetch("/api/sponsorships", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.message || data.error || t("checkout.error.creation");
                toast.error(errorMsg);
                setSubmitting(false);
                return;
            }

            // Redirect to payment gateway
            if (data.authorization_url) {
                toast.success(t("checkout.redirecting"));
                window.location.href = data.authorization_url;
            } else {
                toast.success(t("checkout.created"));
                setStep(4);
                setSubmitting(false);
            }
        } catch (error) {
            console.error("Sponsorship creation error:", error);
            toast.error(t("checkout.error.generic"));
            setSubmitting(false);
        }
    };

    const frequencyLabels: Record<string, string> = {
        monthly: t("checkout.frequency.monthly"),
        quarterly: t("checkout.frequency.quarterly"),
        yearly: t("checkout.frequency.yearly"),
    };

    // Beneficiary not found
    if (!beneficiary) {
        return (
            <Layout>
                <div className="container mx-auto px-4 py-32 text-center">
                    <h2 className="text-2xl font-bold mb-4">{t("checkout.not_found")}</h2>
                    <Button onClick={() => router.visit("/sponsorship")}>{t("checkout.back_to_program")}</Button>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <div className="gradient-hero pattern-islamic py-16">
                <div className="container mx-auto px-4 text-center">
                    <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="font-display text-3xl md:text-4xl font-bold text-foreground mb-2">
                        {t("checkout.title")}
                    </motion.h1>
                </div>
            </div>

            <div className="container mx-auto px-4 py-12 max-w-2xl">
                {/* Beneficiary banner */}
                <motion.div
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-secondary rounded-xl p-4 mb-8 flex items-center gap-4 border border-border"
                >
                    <img src={beneficiary.image} alt={beneficiary.name} className="w-16 h-16 rounded-full object-cover border-2 border-primary" />
                    <div className="flex-1">
                        <p className="font-semibold text-foreground">{beneficiary.name}</p>
                        <div className="flex items-center gap-1 text-sm text-muted-foreground">
                            <MapPin className="w-3 h-3" /> {beneficiary.location}
                        </div>
                    </div>
                    {beneficiary.monthlyNeed > 0 && (
                        <div className="text-right">
                            <p className="text-xs text-muted-foreground">{t("checkout.monthly_need")}</p>
                            <p className="text-lg font-bold text-primary">{beneficiary.monthlyNeed} FCFA</p>
                        </div>
                    )}
                </motion.div>

                {/* Progress Steps */}
                <div className="flex items-center justify-center gap-2 mb-10">
                    {[1, 2, 3].map((s) => (
                        <div key={s} className="flex items-center gap-2">
                            <motion.div
                                animate={step >= s ? { scale: [1, 1.2, 1] } : {}}
                                className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors ${step >= s ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
                                    }`}
                            >
                                {step > s ? <CheckCircle className="w-5 h-5" /> : s}
                            </motion.div>
                            {s < 3 && <div className={`w-12 h-0.5 transition-colors ${step > s ? "bg-primary" : "bg-muted"}`} />}
                        </div>
                    ))}
                </div>

                {/* Step 1: Amount & Frequency */}
                {step === 1 && (
                    <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-8">
                        <div>
                            <h2 className="font-display text-2xl font-bold text-foreground mb-4">{t("checkout.amount_title")}</h2>
                            <div className="grid grid-cols-3 gap-3 mb-4">
                                {presetAmounts.map((a) => (
                                    <motion.button
                                        key={a}
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        onClick={() => handleAmountChange(a.toString(), false)}
                                        className={`py-3 rounded-lg border text-center font-semibold transition-all ${amount === a.toString()
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

                        {/* Payment frequency */}
                        <div>
                            <h2 className="font-display text-2xl font-bold text-foreground mb-4">{t("checkout.frequency_title")}</h2>
                            <div className="grid grid-cols-3 gap-3">
                                {(["monthly", "quarterly", "yearly"] as const).map((freq) => (
                                    <motion.button
                                        key={freq}
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        onClick={() => setPaymentFrequency(freq)}
                                        className={`py-3 rounded-lg border text-center font-semibold transition-all ${paymentFrequency === freq
                                            ? "border-primary bg-primary text-primary-foreground"
                                            : "border-border bg-card text-card-foreground hover:border-primary/50"
                                            }`}
                                    >
                                        {frequencyLabels[freq]}
                                    </motion.button>
                                ))}
                            </div>
                        </div>

                        <Button onClick={handleDonate} size="lg" className="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-semibold">
                            {t("checkout.continue")} — {finalAmount ? `${parseInt(finalAmount).toLocaleString()} FCFA/${frequencyLabels[paymentFrequency].toLowerCase()}` : "0 FCFA"}
                        </Button>
                    </motion.div>
                )}

                {/* Step 2: Information */}
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

                {/* Step 3: Payment */}
                {step === 3 && (
                    <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-6">
                        <button onClick={() => setStep(2)} className="text-sm text-primary flex items-center gap-1 hover:underline">
                            <ArrowLeft className="w-4 h-4" /> {t("checkout.back")}
                        </button>
                        <h2 className="font-display text-2xl font-bold text-foreground">{t("checkout.payment_details")}</h2>

                        <div className="bg-secondary rounded-lg p-4 mb-4">
                            <div className="flex justify-between text-sm mb-1">
                                <span className="text-muted-foreground">{t("checkout.sponsorship_label")}</span>
                                <span className="font-medium text-foreground">{beneficiary.name}</span>
                            </div>
                            <div className="flex justify-between text-sm mb-1">
                                <span className="text-muted-foreground">{t("checkout.frequency_label")}</span>
                                <span className="font-medium text-foreground">{frequencyLabels[paymentFrequency]}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">{t("checkout.amount_label")}</span>
                                <span className="font-bold text-primary text-lg">{parseInt(finalAmount).toLocaleString()} FCFA</span>
                            </div>
                        </div>

                        {/* Orange Money / MTN Money payment */}
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
                                    <Heart className="w-5 h-5 mr-2" /> {t("checkout.confirm")} — {parseInt(finalAmount).toLocaleString()} FCFA
                                </>
                            )}
                        </Button>
                        <p className="text-xs text-muted-foreground text-center">{t("checkout.secure_notchpay")}</p>
                    </motion.div>
                )}

                {/* Step 4: Confirmation (fallback if no redirect) */}
                {step === 4 && (
                    <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ type: "spring", duration: 0.6 }} className="text-center space-y-6 py-8 relative">
                        <div className="absolute inset-0 overflow-hidden pointer-events-none">
                            {Array.from({ length: 12 }).map((_, i) => (
                                <motion.div
                                    key={i}
                                    initial={{ opacity: 0, y: 0, x: 0, scale: 0 }}
                                    animate={{
                                        opacity: [0, 1, 0],
                                        y: [0, -80 - Math.random() * 120],
                                        x: [(Math.random() - 0.5) * 200],
                                        scale: [0, 1, 0.5],
                                        rotate: [0, Math.random() * 360],
                                    }}
                                    transition={{ duration: 1.5, delay: i * 0.1 }}
                                    className="absolute left-1/2 top-1/2"
                                >
                                    <Star className="w-4 h-4 text-gold fill-gold" />
                                </motion.div>
                            ))}
                        </div>

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
                            {t("checkout.recorded_before")} <strong className="text-primary">{parseInt(finalAmount).toLocaleString()} FCFA</strong> ({frequencyLabels[paymentFrequency].toLowerCase()}) {t("checkout.recorded_middle")}{" "}
                            <strong>{beneficiary.name}</strong> {t("checkout.recorded_after")}
                        </p>

                        {/* Sponsoring certificate preview */}
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.5 }}
                            className="bg-card border-2 border-gold/30 rounded-xl p-6 max-w-sm mx-auto"
                        >
                            <div className="text-center space-y-3">
                                <p className="text-xs uppercase tracking-widest text-gold font-semibold">{t("checkout.certificate")}</p>
                                <div className="w-1 h-6 bg-gold mx-auto rounded-full" />
                                <img src={beneficiary.image} alt={beneficiary.name} className="w-20 h-20 rounded-full mx-auto object-cover border-2 border-gold" />
                                <p className="font-display text-lg font-bold text-foreground">{beneficiary.name}</p>
                                <p className="text-sm text-muted-foreground">{beneficiary.location}</p>
                                <p className="text-sm text-muted-foreground">{t("checkout.sponsored_by")} <strong>{formData.firstName} {formData.lastName}</strong></p>
                                <p className="text-primary font-bold">{parseInt(finalAmount).toLocaleString()} FCFA/{frequencyLabels[paymentFrequency].toLowerCase()}</p>
                                <Button variant="outline" size="sm" className="mt-2 border-gold text-gold hover:bg-gold hover:text-white">
                                    <Download className="w-3 h-3 mr-1" /> {t("checkout.download_certificate")}
                                </Button>
                            </div>
                        </motion.div>

                        <p className="text-sm text-muted-foreground">
                            {t("checkout.confirmation_sent")} <strong>{formData.email}</strong>.
                        </p>
                        <div className="flex flex-wrap justify-center gap-3 pt-4">
                            <Button variant="outline" onClick={() => router.visit("/")} className="border-primary text-primary hover:bg-primary hover:text-primary-foreground">
                                {t("checkout.back_home")}
                            </Button>
                        </div>
                    </motion.div>
                )}
            </div>
        </Layout>
    );
};

export default SponsorCheckout;
