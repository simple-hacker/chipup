module.exports = {
  purge: [
      './resources/**/*.blade.php',
      './resources/**/*.vue',
      './resources/**/*.js',
  ],
  theme: {
    screens: {
      'sm': '640px',
      'md': '768px',
      'lg': '1024px',
      'xl': '1280px',
      'xxl': '1930px',
    },
    inset: {
      '0': 0,
      auto: 'auto',
      5: '5px',
      10: '10px',
      20: '20px',
    },
    extend: {
      spacing: {
        '72': '18rem',
        '84': '21rem',
        '96': '24rem',
        '128': '36rem',
      },
      colors: {
        gray: {
          '0': 'hsl(225, 7%, 95%)',
          '50' : 'hsl(225, 7%, 85%)',
          '100': 'hsl(225, 7%, 75%)',
          '200': 'hsl(225, 7%, 65%)',
          '300': 'hsl(225, 6%, 55%)',
          '400': 'hsl(225, 7%, 35%)',
          '450': 'hsl(225, 7%, 25%)',
          '500': 'hsl(225, 7%, 18%)',
          '550': 'hsl(225, 7%, 15%)',
          '600': 'hsl(225, 7%, 13%)',
          '700': 'hsl(225, 6%, 10%)',
          '800': 'hsl(225, 6%, 7%)',
          '900': 'hsl(225, 0%, 5%)',
        },
        green: {
          100: 'hsl(159, 100%, 80%)',
          200: 'hsl(159, 100%, 70%)',
          300: 'hsl(159, 100%, 50%)',
          400: 'hsl(159, 100%, 42%)',
          500: 'hsl(159, 100%, 34%)',
          600: 'hsl(159, 100%, 26%)',
          700: 'hsl(159, 100%, 15%)',
          800: 'hsl(159, 100%, 10%)',
          900: 'hsl(147, 100%, 5%)',
        },
        red: {
          100: 'hsl(0, 88%, 95%)',
          200: 'hsl(0, 88%, 90%)',
          300: 'hsl(0, 88%, 80%)',
          400: 'hsl(0, 88%, 70%)',
          500: 'hsl(0, 88%, 65%)',
          600: 'hsl(0, 88%, 55%)',
          700: 'hsl(0, 88%, 30%)',
          800: 'hsl(0, 90%, 12%)',
          900: 'hsl(0, 100%, 8%)',
        },
        // background: '#16171B',
        // card: '#202125',
        // muted: {
        //   light: '#505155',
        //   dark: '#38393D',
        // },
        facebook: {
          default: '#4267B2',
          light: '#4270b2',
          dark: '#425eb2'
        },
        twitter: {
          default: '#1DA1F2',
          light: '#1db3f2',
          dark: '#1d8ff2',
        }
      },
      maxWidth: {
        'nav': '230px'
      },
    },
  },
  variants: {
    backgroundColor: ['responsive', 'hover', 'focus', 'active', 'disabled'],
    borderColor: ['disabled'],
  },
  plugins: [],
}
