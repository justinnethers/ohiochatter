/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    safelist: [
        // Forum tag colors
        'bg-blue-500', 'bg-blue-600',
        'bg-red-500', 'bg-red-600',
        'bg-green-500', 'bg-green-600',
        'bg-orange-500', 'bg-orange-600',
    ],
  theme: {
    extend: {
        fontFamily: {
            display: ['Bitter', 'serif'],
            body: ['Bitter', 'serif'],
            sans: ['Plus Jakarta Sans', 'sans-serif'],
            serif: ['Bitter', 'serif'],
            headline: ['Plus Jakarta Sans', 'sans-serif'],
        },
        colors: {
            // Primary blue accent
            'accent': {
                50: '#eff6ff',
                100: '#dbeafe',
                200: '#bfdbfe',
                300: '#93c5fd',
                400: '#60a5fa',
                500: '#3b82f6',
                600: '#2563eb',
                700: '#1d4ed8',
                800: '#1e40af',
                900: '#1e3a8a',
                950: '#172554',
            },
            'steel': {
                50: '#f8fafc',
                100: '#f1f5f9',
                200: '#e2e8f0',
                300: '#cbd5e1',
                400: '#94a3b8',
                500: '#64748b',
                600: '#475569',
                700: '#334155',
                750: '#293548',
                800: '#1e293b',
                850: '#172033',
                900: '#0f172a',
                950: '#020617',
            },
        },
        backgroundImage: {
            'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            'hero-pattern': 'linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%)',
        },
        animation: {
            'glow': 'glow 2s ease-in-out infinite alternate',
            'slide-up': 'slideUp 0.3s ease-out',
        },
        keyframes: {
            glow: {
                '0%': { boxShadow: '0 0 5px rgb(59 130 246 / 0.3), 0 0 10px rgb(59 130 246 / 0.2)' },
                '100%': { boxShadow: '0 0 10px rgb(59 130 246 / 0.4), 0 0 20px rgb(59 130 246 / 0.3)' },
            },
            slideUp: {
                '0%': { opacity: '0', transform: 'translateY(10px)' },
                '100%': { opacity: '1', transform: 'translateY(0)' },
            },
        },
    },
  },
  plugins: [require('@tailwindcss/typography')],
}

