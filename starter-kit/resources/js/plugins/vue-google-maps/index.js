import VueGoogleMaps from '@fawmi/vue-google-maps'


export default function (app) {
  app.use(VueGoogleMaps, {
    load: {
      key: 'AIzaSyCMjvkHTt8t5bT4gxdXYOqf6CoO8YB-P0A',
      // language: 'de',
    },
  })
}

