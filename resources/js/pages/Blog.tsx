import { useState } from "react";
import { Link } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";
import Layout from "@/components/Layout";
import { BlogPost } from "@/data/mockData";
import { Clock, ArrowRight, Search, Grid3X3, LayoutList } from "lucide-react";
import { useTranslation } from "@/lib/i18n";

interface BlogProps {
  posts: BlogPost[];
}

const Blog = ({ posts }: BlogProps) => {
  const { t, locale } = useTranslation();
  const blogPosts = posts || [];
  const allLabel = t("blog.all");
  const categories = [allLabel, ...Array.from(new Set(blogPosts.map((p) => p.category).filter(Boolean)))];
  const dateLocale = locale === "fr" ? "fr-FR" : "en-US";

  const [activeCategory, setActiveCategory] = useState(allLabel);
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [search, setSearch] = useState("");

  const filtered = blogPosts.filter((post) => {
    const matchCat = activeCategory === allLabel || post.category === activeCategory;
    const matchSearch = post.title.toLowerCase().includes(search.toLowerCase()) || (post.excerpt || "").toLowerCase().includes(search.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <Layout>
      {/* Hero */}
      <div className="gradient-hero pattern-islamic py-20 relative overflow-hidden">
        <motion.div animate={{ y: [-15, 15, -15] }} transition={{ duration: 5, repeat: Infinity }} className="absolute top-8 left-20 w-14 h-14 border border-primary-foreground/10 rounded-full" />
        <motion.div animate={{ y: [10, -20, 10], x: [-5, 5, -5] }} transition={{ duration: 7, repeat: Infinity }} className="absolute bottom-10 right-16 w-10 h-10 border border-primary-foreground/10 rotate-45" />
        <div className="container mx-auto px-4 text-center relative z-10">
          <motion.span initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-xs font-semibold text-gold uppercase tracking-widest mb-3 block">
            {t("blog.label")}
          </motion.span>
          <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="font-display text-3xl md:text-5xl font-bold text-foreground mb-4">{t("blog.title")}</motion.h1>
          <motion.p initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }} className="text-foreground/80 max-w-xl mx-auto text-lg">
            {t("blog.subtitle")}
          </motion.p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        {/* Toolbar */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }} className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
          {/* Categories */}
          <div className="flex flex-wrap gap-2">
            {categories.map((cat) => (
              <motion.button
                key={cat}
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                onClick={() => setActiveCategory(cat)}
                className={`px-4 py-2 rounded-full text-sm font-medium transition-all ${
                  activeCategory === cat
                    ? "bg-primary text-primary-foreground shadow-md"
                    : "bg-card border border-border text-muted-foreground hover:border-primary/30 hover:text-primary"
                }`}
              >
                {cat}
              </motion.button>
            ))}
          </div>
          <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="relative flex-1 md:w-64">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder={t("blog.search_placeholder")}
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm"
              />
            </div>
            <div className="flex border border-border rounded-lg overflow-hidden">
              <button onClick={() => setViewMode("grid")} className={`p-2 ${viewMode === "grid" ? "bg-primary text-primary-foreground" : "bg-card text-muted-foreground hover:text-primary"} transition-colors`}>
                <Grid3X3 className="w-4 h-4" />
              </button>
              <button onClick={() => setViewMode("list")} className={`p-2 ${viewMode === "list" ? "bg-primary text-primary-foreground" : "bg-card text-muted-foreground hover:text-primary"} transition-colors`}>
                <LayoutList className="w-4 h-4" />
              </button>
            </div>
          </div>
        </motion.div>

        {/* Featured post */}
        {activeCategory === allLabel && !search && blogPosts.length > 0 && (
          <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }} className="mb-12">
            <Link href={`/blog/${blogPosts[0].id}`} className="group block bg-card border border-border rounded-2xl overflow-hidden md:flex hover:shadow-2xl transition-all duration-500">
              <div className="md:w-1/2 h-64 md:h-auto overflow-hidden relative">
                <img src={blogPosts[0].image} alt={blogPosts[0].title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                <div className="absolute inset-0 bg-gradient-to-t from-foreground/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                <span className="absolute top-4 left-4 text-xs font-semibold bg-accent text-accent-foreground px-3 py-1 rounded-full">{t("blog.featured")}</span>
              </div>
              <div className="md:w-1/2 p-8 md:p-10 flex flex-col justify-center">
                <div className="flex items-center gap-2 mb-3">
                  <span className="text-xs font-semibold text-primary bg-emerald-light px-2.5 py-1 rounded-full">{blogPosts[0].category}</span>
                  <span className="text-xs text-muted-foreground flex items-center gap-1"><Clock className="w-3 h-3" />{blogPosts[0].readTime}</span>
                </div>
                <h2 className="font-display text-2xl md:text-3xl font-bold text-card-foreground group-hover:text-primary transition-colors mb-3">{blogPosts[0].title}</h2>
                <p className="text-muted-foreground mb-5 leading-relaxed">{blogPosts[0].excerpt}</p>
                <div className="flex items-center gap-2 text-sm text-muted-foreground mb-4">
                  <span className="font-medium">{t("blog.by", { author: blogPosts[0].author })}</span> · <span>{new Date(blogPosts[0].date).toLocaleDateString(dateLocale, { month: "long", day: "numeric", year: "numeric" })}</span>
                </div>
                <span className="text-primary font-semibold text-sm flex items-center gap-1 group-hover:gap-3 transition-all">
                  {t("blog.read_article")} <ArrowRight className="w-4 h-4" />
                </span>
              </div>
            </Link>
          </motion.div>
        )}

        {/* Posts */}
        <AnimatePresence mode="wait">
          {viewMode === "grid" ? (
            <motion.div key="grid" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
              {filtered.slice(activeCategory === allLabel && !search ? 1 : 0).map((post, i) => (
                <motion.div
                  key={post.id}
                  initial={{ opacity: 0, y: 30 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: i * 0.1, duration: 0.5 }}
                  whileHover={{ y: -6 }}
                  className="group"
                >
                  <Link href={`/blog/${post.id}`} className="block bg-card border border-border rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div className="h-52 overflow-hidden relative">
                      <img src={post.image} alt={post.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                      <div className="absolute inset-0 bg-gradient-to-t from-foreground/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                    </div>
                    <div className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <span className="text-xs font-semibold text-primary bg-emerald-light px-2.5 py-1 rounded-full">{post.category}</span>
                        <span className="text-xs text-muted-foreground flex items-center gap-1"><Clock className="w-3 h-3" />{post.readTime}</span>
                      </div>
                      <h3 className="font-display text-lg font-bold text-card-foreground group-hover:text-primary transition-colors mb-2">{post.title}</h3>
                      <p className="text-sm text-muted-foreground line-clamp-2 leading-relaxed">{post.excerpt}</p>
                      <div className="flex items-center justify-between mt-4 pt-4 border-t border-border">
                        <p className="text-xs text-muted-foreground">{t("blog.by", { author: post.author })}</p>
                        <p className="text-xs text-muted-foreground">{new Date(post.date).toLocaleDateString(dateLocale, { month: "short", day: "numeric" })}</p>
                      </div>
                    </div>
                  </Link>
                </motion.div>
              ))}
            </motion.div>
          ) : (
            <motion.div key="list" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="flex flex-col gap-5">
              {filtered.slice(activeCategory === allLabel && !search ? 1 : 0).map((post, i) => (
                <motion.div
                  key={post.id}
                  initial={{ opacity: 0, x: -20 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: i * 0.08 }}
                  whileHover={{ x: 5 }}
                >
                  <Link href={`/blog/${post.id}`} className="group flex flex-col sm:flex-row bg-card border border-border rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300">
                    <div className="sm:w-48 md:w-64 h-48 sm:h-auto overflow-hidden shrink-0">
                      <img src={post.image} alt={post.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                    </div>
                    <div className="p-6 flex flex-col justify-center flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-xs font-semibold text-primary bg-emerald-light px-2.5 py-1 rounded-full">{post.category}</span>
                        <span className="text-xs text-muted-foreground flex items-center gap-1"><Clock className="w-3 h-3" />{post.readTime}</span>
                      </div>
                      <h3 className="font-display text-xl font-bold text-card-foreground group-hover:text-primary transition-colors mb-2">{post.title}</h3>
                      <p className="text-sm text-muted-foreground line-clamp-2 leading-relaxed mb-3">{post.excerpt}</p>
                      <div className="flex items-center gap-3 text-xs text-muted-foreground">
                        <span>{t("blog.by", { author: post.author })}</span> · <span>{new Date(post.date).toLocaleDateString(dateLocale, { month: "long", day: "numeric", year: "numeric" })}</span>
                      </div>
                    </div>
                  </Link>
                </motion.div>
              ))}
            </motion.div>
          )}
        </AnimatePresence>

        {filtered.length === 0 && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-center py-20">
            <p className="text-muted-foreground text-lg">{t("blog.no_articles")}</p>
          </motion.div>
        )}
      </div>
    </Layout>
  );
};

export default Blog;
