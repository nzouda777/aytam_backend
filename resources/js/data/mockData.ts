// Types des données servies par le backoffice (via props Inertia).

export interface BlogPost {
  id: number | string;
  slug?: string;
  title: string;
  excerpt: string;
  content: string;
  author: string;
  date: string;
  category: string;
  image: string;
  readTime: string;
}

export interface DonationCause {
  id: string;
  title: string;
  description: string;
  raised: number;
  goal_amount: number;
  icon: string;
  current_amount: number;
}

export interface Testimonial {
  id: number;
  name: string;
  role: string;
  quote: string;
  avatar: string | null;
  rating: number;
}

export interface ImpactStatsData {
  orphansSponsored: number;
  regionsServed: number;
  donationsRaised: number;
  activeDonors: number;
}

export interface BeneficiaryCard {
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
}
