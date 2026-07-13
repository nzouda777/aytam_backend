import "../css/app.css";

import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";

const queryClient = new QueryClient();

const pages = import.meta.glob("./pages/*.tsx");

createInertiaApp({
    resolve: (name) => {
        const importPage = pages[`./pages/${name}.tsx`];
        if (!importPage) {
            throw new Error(`Page introuvable : ${name}`);
        }
        return importPage();
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <QueryClientProvider client={queryClient}>
                <TooltipProvider>
                    <Toaster />
                    <Sonner />
                    <App {...props} />
                </TooltipProvider>
            </QueryClientProvider>
        );
    },
    progress: {
        color: "#2e7d5b",
    },
});
