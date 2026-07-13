import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { CheckCircle, Shield, CreditCard, Loader2 } from "lucide-react";
import { useTranslation } from "@/lib/i18n";

interface ProcessingScreenProps {
  onComplete: () => void;
  amount: string;
}

const stepIcons = [CreditCard, Shield, Loader2, CheckCircle];

const ProcessingScreen = ({ onComplete, amount }: ProcessingScreenProps) => {
  const { t } = useTranslation();
  const [currentStep, setCurrentStep] = useState(0);
  const [progress, setProgress] = useState(0);

  const steps = [
    { label: t("processing.verify"), icon: stepIcons[0] },
    { label: t("processing.securing"), icon: stepIcons[1] },
    { label: t("processing.processing"), icon: stepIcons[2] },
    { label: t("processing.confirmed"), icon: stepIcons[3] },
  ];

  useEffect(() => {
    const stepDuration = 800;
    const interval = setInterval(() => {
      setCurrentStep((prev) => {
        if (prev >= steps.length - 1) {
          clearInterval(interval);
          setTimeout(onComplete, 600);
          return prev;
        }
        return prev + 1;
      });
    }, stepDuration);
    return () => clearInterval(interval);
  }, [onComplete]);

  useEffect(() => {
    const target = ((currentStep + 1) / steps.length) * 100;
    const timer = setTimeout(() => setProgress(target), 100);
    return () => clearTimeout(timer);
  }, [currentStep]);

  return (
    <div className="flex flex-col items-center justify-center py-16 space-y-8">
      <motion.div
        animate={{ rotate: currentStep < 3 ? 360 : 0 }}
        transition={{ repeat: currentStep < 3 ? Infinity : 0, duration: 1, ease: "linear" }}
        className="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center"
      >
        {currentStep < 3 ? (
          <Loader2 className="w-10 h-10 text-primary" />
        ) : (
          <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }} transition={{ type: "spring" }}>
            <CheckCircle className="w-10 h-10 text-primary" />
          </motion.div>
        )}
      </motion.div>

      <div className="text-center">
        <h2 className="font-display text-2xl font-bold text-foreground mb-2">
          {currentStep < 3 ? t("processing.title") : t("processing.done")}
        </h2>
        <p className="text-3xl font-bold text-primary">{amount} FCFA</p>
      </div>

      {/* Progress bar */}
      <div className="w-full max-w-sm">
        <div className="w-full bg-muted rounded-full h-2 overflow-hidden">
          <motion.div
            className="h-full rounded-full gradient-gold"
            initial={{ width: "0%" }}
            animate={{ width: `${progress}%` }}
            transition={{ duration: 0.5, ease: "easeOut" }}
          />
        </div>
      </div>

      {/* Steps */}
      <div className="space-y-3 w-full max-w-sm">
        {steps.map((step, i) => (
          <motion.div
            key={step.label}
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: i <= currentStep ? 1 : 0.3, x: 0 }}
            transition={{ delay: i * 0.1 }}
            className="flex items-center gap-3"
          >
            <div className={`w-6 h-6 rounded-full flex items-center justify-center ${
              i <= currentStep ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
            }`}>
              {i < currentStep ? (
                <CheckCircle className="w-4 h-4" />
              ) : (
                <step.icon className={`w-3.5 h-3.5 ${i === currentStep ? "animate-spin" : ""}`} />
              )}
            </div>
            <span className={`text-sm ${i <= currentStep ? "text-foreground font-medium" : "text-muted-foreground"}`}>
              {step.label}
            </span>
          </motion.div>
        ))}
      </div>

      <p className="text-xs text-muted-foreground">{t("processing.secure_note")}</p>
    </div>
  );
};

export default ProcessingScreen;
