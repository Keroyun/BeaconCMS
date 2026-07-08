import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './lib/**/*.{js,ts,jsx,tsx,mdx}'
  ],
  theme: {
    extend: {
      colors: {
        beacon: {
          navy: '#12355b',
          teal: '#0f766e',
          sky: '#dff6f5',
          mint: '#e9f8f2',
          ink: '#1e293b',
          muted: '#64748b'
        }
      },
      fontFamily: {
        sans: ['Poppins', 'Arial', 'sans-serif']
      },
      boxShadow: {
        soft: '0 14px 40px rgba(15, 23, 42, 0.08)'
      }
    }
  },
  plugins: []
};

export default config;
