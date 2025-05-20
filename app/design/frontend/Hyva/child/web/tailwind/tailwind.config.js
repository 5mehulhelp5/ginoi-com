const {
  spacing
} = require('tailwindcss/defaultTheme');

const colors = require('tailwindcss/colors');

const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
  theme: {
    extend: {
      screens: {
        'xs': '425px',
        // => @media (min-width: 425px) { ... }
        'sm': '640px',
        // => @media (min-width: 640px) { ... }
        'md': '768px',
        // => @media (min-width: 768px) { ... }
        'lg': '1024px',
        // => @media (min-width: 1024px) { ... }
        'xl': '1280px',
        // => @media (min-width: 1280px) { ... }
        '2xl': '1536px' // => @media (min-width: 1536px) { ... }

      },
      fontFamily: {
        sans: ["Segoe UI", "Helvetica Neue", "Arial", "sans-serif"]
      },
      colors: {
        primary: {
          lighter: colors.violet['300'],
          "DEFAULT": colors.violet['800'],
          darker: colors.violet['900']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        background: {
          lighter: colors.violet['100'],
          "DEFAULT": colors.violet['200'],
          darker: colors.violet['300']
        },
        violet: {
            '50': '#fcf5fe',
            '100': '#f9eafd',
            '200': '#f3d4fa',
            '300': '#ecb2f5',
            '400': '#e184ee',
            '500': '#d154e1',
            '600': '#b734c5',
            '700': '#9928a3',
            '800': '#8b2692',
            '900': '#6a216e',
            '950': '#460a48',
        },
      },
      textColor: {
        primary: {
          lighter: colors.gray['700'],
          "DEFAULT": colors.gray['800'],
          darker: colors.gray['900']
        },
        secondary: {
          lighter: colors.gray['400'],
          "DEFAULT": colors.gray['600'],
          darker: colors.gray['800']
        },
          green: '#22c55e',
          purple: '#802E8D',
          gdpr:'#58595b',
      },
      backgroundColor: {
        primary: {
          lighter: colors.violet['600'],
          "DEFAULT": colors.violet['700'],
          darker: colors.violet['800']
        },
        container: {
          lighter: '#ffffff',
          "DEFAULT": '#fafafa',
          darker: '#f5f5f5'
        },
          toggle: {
              lighter: '#e5dbea',
              "DEFAULT": '#763487',
              darker: '#57585a'
          },
      },
      borderColor: {
        primary: {
          lighter: colors.violet['600'],
          "DEFAULT": colors.violet['700'],
          darker: colors.violet['800']
        },
          violet: {
          lighter: colors.violet['600'],
          "DEFAULT": '#7b189f',
          darker: colors.violet['800']
        },
        container: {
          lighter: '#f5f5f5',
          "DEFAULT": '#e7e7e7',
          darker: '#b6b6b6'
        },
          toggle: {
              lighter: '#e5dbea',
              "DEFAULT": '#763487',
              darker: '#57585a'
          },
      },
      minWidth: {
        8: spacing["8"],
        20: spacing["20"],
        40: spacing["40"],
        48: spacing["48"]
      },
      minHeight: {
        14: spacing["14"],
        a11y: '44px',
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      maxHeight: {
        '0': '0',
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh',
        'screen-85': '85vh'
      },
      container: {
        center: true,
        padding: '1.5rem'
      }
    }
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  // Examples for excluding patterns from purge
  content: [
    // this theme's phtml and layout XML files
    '../../**/*.phtml',
    '../../*/layout/*.xml',
    '../../*/page_layout/override/base/*.xml',
    // parent theme in Vendor (if this is a child-theme)
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml',
    // app/code phtml files (if need tailwind classes from app/code modules)
    '../../../../../../../app/code/**/*.phtml',
  ],
});
