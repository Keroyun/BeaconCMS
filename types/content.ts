export type Specialty = {
  name: string;
  slug: string;
  description: string;
  icon: string;
};

export type Doctor = {
  name: string;
  slug: string;
  specialty: string;
  qualifications: string;
  photo?: string;
};

export type Promotion = {
  title: string;
  slug: string;
  description: string;
  endDate: string;
};

export type BlogPost = {
  title: string;
  slug: string;
  excerpt: string;
  date: string;
};
