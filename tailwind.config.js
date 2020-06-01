module.exports = {
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
        background: '#16171B',
        card: '#202125',
        muted: {
          light: '#505155',
          dark: '#38393D',
        },
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
  variants: {},
  plugins: [],
}
