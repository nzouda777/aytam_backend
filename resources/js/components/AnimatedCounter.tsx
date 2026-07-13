import { useEffect, useRef, useState } from "react";
import { useInView } from "framer-motion";

interface AnimatedCounterProps {
  end: number;
  duration?: number;
  prefix?: string;
  suffix?: string;
  decimals?: number;
}

const AnimatedCounter = ({ end, duration = 2000, prefix = "", suffix = "", decimals = 0 }: AnimatedCounterProps) => {
  const [count, setCount] = useState(0);
  const ref = useRef<HTMLSpanElement>(null);
  const isInView = useInView(ref, { margin: "-50px" });
  const prevEnd = useRef(end);

  useEffect(() => {
    if (!isInView) return;

    const startValue = prevEnd.current !== end ? 0 : 0;
    prevEnd.current = end;

    const startTime = Date.now();
    const tick = () => {
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // ease-out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      setCount(Math.round(startValue + eased * (end - startValue)));
      if (progress < 1) requestAnimationFrame(tick);
      else setCount(end);
    };
    requestAnimationFrame(tick);
  }, [isInView, end, duration]);

  const formatted = decimals > 0
    ? (count / Math.pow(10, decimals)).toFixed(decimals)
    : count.toLocaleString();

  return (
    <span ref={ref}>
      {prefix}{formatted}{suffix}
    </span>
  );
};

export default AnimatedCounter;
