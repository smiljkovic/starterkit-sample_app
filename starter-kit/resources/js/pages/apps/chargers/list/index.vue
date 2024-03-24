<script setup>
import {
  ref,
} from 'vue'


import socketImg from '@images/misc/Feller_sd.png'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'


const { isLeftSidebarOpen } = useResponsiveLeftSidebar()
const vuetifyDisplay = useDisplay()

definePage({ meta: { layoutWrapperClasses: 'layout-content-height-fixed' } })

const carImgs = ref([
  socketImg,
  socketImg,
  socketImg,
  socketImg,
])

const refCars = ref([])

const showPanel = ref([
  true,
  false,
  false,
  false,
])


const activeIndex = ref(0)



const mapOptions = {
  center: {
    lat: 45.2396,
    lng: 19.8227,
  },
  zoom: 12,
  mapId: 'DEMO_MAP_ID',
  streetViewControl: false,
  mapTypeControl: false,
  fullscreenControl: false,
}

const {
  data: locationsData,
  execute: fetchLocations,
} = await useApi(createUrl('/vtiger/locations', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,

    // 'pageLimit': itemsPerPage,
    // 'page': page,
    // 'orderBy': headerssMapping(sortBy.value),
    // 'order': orderBy,
    // 'q': searchQuery,
    // 'status': selectedStatus,
    // 'selectedRange': selectedDateRange,
  },
}))

const locations = computed(() => locationsData.value.locations)
const totalLocations = computed(() =>  locationsData.value.totalLocations)
let markers = computed(() => {
  let locationsArray = [{
    initial: {
      lat: 45.2397,
      lng: 19.8217,
    },
  }]
  
  locationsArray.splice(0, 1)
  for (let index = 0; index < totalLocations.value; index++) {
    locationsArray.push({
      'position': {
        'lat': parseFloat(locationsData.value.locations[index].latitude),
        'lng': parseFloat(locationsData.value.locations[index].longitude),
      },
      'text': locationsData.value.locations[index].locationname,
    })
  }
  
  return  locationsArray 
})

const center = { lat: 45.2396, lng: 19.8227 }


const vehicleTrackingData = [
  {
    name: 'VOL-342808',
    location: 'Chelsea, NY, USA',
    progress: 88,
    driverName: 'Veronica Herman',
  },
  {
    name: 'VOL-954784',
    location: 'Lincoln Harbor, NY, USA',
    progress: 100,
    driverName: 'Myrtle Ullrich',
  },
  {
    name: 'VOL-342808',
    location: 'Midtown East, NY, USA',
    progress: 60,
    driverName: 'Barry Schowalter',
  },
  {
    name: 'VOL-343908',
    location: 'Hoboken, NY, USA',
    progress: 28,
    driverName: 'Helen Jacobs',
  },
]

const flyToLocation = (lat, lon, index) => {
  activeIndex.value = index
  showPanel.value.fill(false)
  showPanel.value[index] = !showPanel.value[index]
  if (vuetifyDisplay.mdAndDown.value)
    isLeftSidebarOpen.value = false

  // map.value.flyTo({
  //   center: geolocation,
  //   zoom: 16,
  // })
  // eslint-disable-next-line vue/no-ref-as-operand
  gMap.panTo({
    lat: lat,
    lng: lon,
  })
  // eslint-disable-next-line vue/no-ref-as-operand
  gMap.setZoom(16)

}

watch(activeIndex, () => {
  refCars.value.forEach((car, index) => {
    if (index === activeIndex.value)
      car.classList.add('marker-focus')
    else
      car.classList.remove('marker-focus')
  })
})
</script>

<template>
  <VLayout class="fleet-app-layout">
    <VNavigationDrawer
      v-model="isLeftSidebarOpen"
      width="320"
      absolute
      touchless
      location="start"
    >
      <VCard
        class="h-100 fleet-navigation-drawer"
        flat
      >
        <VCardItem>
          <VCardTitle>
            Chargers locations
          </VCardTitle>

          <template #append>
            <IconBtn
              class="d-lg-none navigation-close-btn"
              @click="isLeftSidebarOpen = !isLeftSidebarOpen"
            >
              <VIcon icon="tabler-x" />
            </IconBtn>
          </template>
        </VCardItem>

        <!-- 👉 Perfect Scrollbar -->
        <PerfectScrollbar
          :options="{ wheelPropagation: false, suppressScrollX: true }"
          style="block-size: calc(100% - 60px);"
        >
          <VCardText class="pt-0">
            <div
              v-for="(location, index) in locations"
              :key="index"
              class="mb-6"
            >
              <div
                class="d-flex align-center justify-space-between cursor-pointer"
                @click="flyToLocation(location.latitude, location.longitude, index)"
              >
                <div class="d-flex gap-x-4">
                  <VAvatar
                    icon="tabler-plug"
                    color="success"
                    variant="tonal"
                  />
                  <div>
                    <div class="text-body-1 text-high-emphasis font-weight-medium">
                      {{ location.locationname }}
                    </div>
                    <div class="text-body-2 text-disabled">
                      {{ location.locationstreet }}
                    </div>
                    <div class="text-body-2 text-disabled">
                      {{ location.locationcity }}
                    </div>
                  </div>
                </div>
                <IconBtn density="comfortable">
                  <VIcon :icon="showPanel[index] ? 'tabler-chevron-down' : $vuetify.locale.isRtl ? 'tabler-chevron-left' : 'tabler-chevron-right'" />
                </IconBtn>
              </div>
              <VExpandTransition mode="out-in">
                <div v-show="showPanel[index]">
                  <div class="py-4 mb-4">
                    <div class="d-flex justify-space-between mb-2">
                      <span class="text-body-1 text-high-emphasis ">Delivery Process</span>
                      <span class="text-body-2">95%</span>
                    </div>
                    <VProgressLinear
                      :model-value="95"
                      color="primary"
                      rounded
                      height="6"
                    />
                  </div>
                  <div>
                    <VTimeline
                      align="start"
                      truncate-line="both"
                      side="end"
                      density="compact"
                      line-thickness="1"
                      class="ps-2"
                    >
                      <VTimelineItem
                        icon="tabler-circle-check"
                        dot-color="rgb(var(--v-theme-surface))"
                        icon-color="success"
                        fill-dot
                        size="22"
                        :elevation="0"
                      >
                        <div class="text-sm text-uppercase text-success font-weight-medium">
                          TRACKING NUMBER CREATED
                        </div>
                        <div class="app-timeline-title">
                          {{ location.locationname }}
                        </div>
                        <div class="app-timeline-text">
                          Sep 01, 7:53 AM
                        </div>
                      </VTimelineItem>
                      <VTimelineItem
                        icon="tabler-circle-check"
                        dot-color="rgb(var(--v-theme-surface))"
                        icon-color="success"
                        fill-dot
                        size="22"
                        :elevation="0"
                      >
                        <div class="text-caption text-uppercase text-success font-weight-medium">
                          OUT FOR DELIVERY
                        </div>
                        <div class="app-timeline-title">
                          Veronica Herman
                        </div>
                        <div class="app-timeline-text">
                          Sep 03, 8:02 AM
                        </div>
                      </VTimelineItem>
                      <VTimelineItem
                        icon="tabler-map-pin"
                        dot-color="rgb(var(--v-theme-surface))"
                        icon-color="primary"
                        fill-dot
                        size="22"
                        :elevation="0"
                      >
                        <div class="text-caption text-uppercase text-success font-weight-medium">
                          ARRIVED
                        </div>
                        <div class="app-timeline-title">
                          Veronica Herman
                        </div>
                        <div class="app-timeline-text">
                          Sep 04, 8:18 AM
                        </div>
                      </VTimelineItem>
                    </VTimeline>
                  </div>
                </div>
              </VExpandTransition>
            </div>
          </VCardText>
        </PerfectScrollbar>
      </VCard>
    </VNavigationDrawer>

    <VMain>
      <div class="h-100">
        <IconBtn
          class="d-lg-none navigation-toggle-btn rounded-sm"
          variant="elevated"
          @click="isLeftSidebarOpen = true"
        >
          <VIcon icon="tabler-menu-2" />
        </IconBtn>
        <!-- 👉 Fleet map  -->
        <!--
          
          
        -->
        <GMapMap
          class="basemap"
          :center="mapOptions.center"
          :zoom="mapOptions.zoom"
          :map-type-id="mapOptions.mapTypeId"
          :options="{
            zoomControl: true,
            mapTypeControl: mapOptions.mapTypeControl,
            scaleControl: true,
            streetViewControl: mapOptions.streetViewControl,
            rotateControl: false,
            fullscreenControl: mapOptions.fullscreenControl,
          }"
        >
          <GMapMarker
            v-for="(m, index) in markers"
            :key="index"
            :position="m.position"
            :icon="tabler-plug"
            :clickable="true"
            :draggable="false"
            :visible="true"
            @click="center = m.position"
          />
        </GMapMap>
        
        <img
          v-for="(car, index) in carImgs"
          :key="index"
          ref="refCars"
          :src="car"
          alt="car Img marker"
          height="21"
          width="10"
        >
      </div>
    </VMain>
  </VLayout>
</template>

<style lang="scss">
@use "@styles/variables/_vuetify.scss";
@use "@core-scss/base/_mixins.scss";
@import "mapbox-gl/dist/mapbox-gl.css";

.fleet-app-layout {
  border-radius: vuetify.$card-border-radius;

  @include mixins.elevation(vuetify.$card-elevation);

  $sel-fleet-app-layout: &;

  @at-root {
    .skin--bordered {
      @include mixins.bordered-skin($sel-fleet-app-layout);
    }
  }
}

.navigation-toggle-btn{
  position: absolute;
  z-index: 1;
  inset-block-start: 1rem;
  inset-inline-start: 1rem;
}

.navigation-close-btn{
  position: absolute;
  z-index: 1;
  inset-block-start: 1rem;
  inset-inline-end: 1rem;
}

.basemap {
  block-size: 100%;
  inline-size: 100%;
}

.marker-focus {
  filter: drop-shadow(0 0 7px rgb(var(--v-theme-primary)));
}

.mapboxgl-ctrl-bottom-left,
.mapboxgl-ctrl-bottom-right {
  display: none;
}

.fleet-navigation-drawer{
  .v-timeline .v-timeline-divider__dot .v-timeline-divider__inner-dot{
    box-shadow: none;
  }
}

#mapContainer {
  block-size: 100vh !important;
}
</style>
