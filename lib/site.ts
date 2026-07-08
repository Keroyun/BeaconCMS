import type { BlogPost, Doctor, Promotion, Specialty } from '@/types/content';

export const siteConfig = {
  name: 'Beacon Hospital',
  url: process.env.NEXT_PUBLIC_SITE_URL || 'https://www.beaconhospital.com.my',
  description:
    'Patient-focused hospital website experience for specialties, doctors, health packages, and healthcare education.',
  phone: '+603-7787 2999',
  email: 'info@beaconhospital.com.my',
  address: '1 Jalan 215, Section 51, 46050 Petaling Jaya, Selangor, Malaysia'
};

export const specialties: Specialty[] = [
  {
    name: 'Cancer Care',
    slug: 'cancer-care',
    description: 'Coordinated care across screening, diagnosis support, treatment planning, and follow-up.',
    icon: 'Hospital'
  },
  {
    name: 'Cardiology',
    slug: 'cardiology',
    description: 'Heart health services supported by specialist consultation and diagnostic pathways.',
    icon: 'HeartPulse'
  },
  {
    name: 'Health Screening',
    slug: 'health-screening',
    description: 'Preventive screening options for people who want a clearer picture of their health.',
    icon: 'ClipboardCheck'
  },
  {
    name: 'Orthopaedics',
    slug: 'orthopaedics',
    description: 'Specialist care for bone, joint, spine, and mobility-related concerns.',
    icon: 'Activity'
  }
];

export const doctors: Doctor[] = [
  {
    name: 'Consultant Name',
    slug: 'consultant-name',
    specialty: 'Clinical Specialty',
    qualifications: 'Qualifications and credentials to be confirmed before production.'
  },
  {
    name: 'Consultant Name',
    slug: 'consultant-name-2',
    specialty: 'Clinical Specialty',
    qualifications: 'Qualifications and credentials to be confirmed before production.'
  },
  {
    name: 'Consultant Name',
    slug: 'consultant-name-3',
    specialty: 'Clinical Specialty',
    qualifications: 'Qualifications and credentials to be confirmed before production.'
  }
];

export const promotions: Promotion[] = [
  {
    title: 'Health Screening Package',
    slug: 'health-screening-package',
    description: 'Package details, pricing, terms, and eligibility must be verified before publishing.',
    endDate: '2026-12-31'
  },
  {
    title: 'Preventive Care Offer',
    slug: 'preventive-care-offer',
    description: 'Use balanced, compliant copy and avoid guaranteed clinical outcomes.',
    endDate: '2026-12-31'
  }
];

export const posts: BlogPost[] = [
  {
    title: 'Preparing for a Specialist Appointment',
    slug: 'preparing-for-a-specialist-appointment',
    excerpt: 'Practical steps patients can take before visiting a specialist clinic.',
    date: '2026-07-08'
  },
  {
    title: 'Screening Is Not the Same as Diagnosis',
    slug: 'screening-vs-diagnosis',
    excerpt: 'A simple explanation of how screening supports earlier medical conversations.',
    date: '2026-07-08'
  }
];
