<script setup>
import {
  ref,
} from 'vue'


import socketImg from '@images/misc/Feller_sd.png'
import chargerOK from '@images/misc/charger-OK.png'
import chargerX from '@images/misc/charger-X.png'
import { CustomMarker, GoogleMap } from 'vue3-google-map'
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

const refLocations = ref([])

const showPanel = ref([
  false,
  false,
  false,
  false,
])


const activeIndex = ref(0)
let mapOptions = {
  center: {
    lat: 45.2396,
    lng: 19.8227,
  },
  zoom: 12,
  mapId: 'DEMO_MAP_ID',
  streetViewControl: false,
  mapTypeControl: false,
  fullscreenControl: false,
  apiKey: 'AIzaSyCMjvkHTt8t5bT4gxdXYOqf6CoO8YB-P0A',
}

const {
  data: locationsData,
  execute: fetchLocations,
} = await useApi(createUrl('/vtiger/locations', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,
  },
}))

const mapRef = ref(null)
let markers = ref (null)

// Second pattern: watch for "ready" then do something with the API or map instance
watch(() => mapRef.value?.ready, ready => {
  if (!ready) return
  console.log('Map is loaded now' )

  // do something with the api using `mapRef.value.api`
  markers.value = [{
    initial: {
      lat: 46.2397,
      lng: 19.8217,
    },
  }]
  
  markers.value.splice(0, 1)
  for (let index = 0; index < totalLocations.value; index++) {
    markers.value.push({
      'position': {
        'lat': parseFloat(locationsData.value.locations[index].latitude),
        'lng': parseFloat(locationsData.value.locations[index].longitude),
      },
      'text': locationsData.value.locations[index].locationname,
      'src': locationsData.value.locations[index].chargers[0].chargerstate === 'Available' ? chargerOK: chargerX,
    })
  }
  console.log('Markers loaded', markers)
})

const locations = computed(() => locationsData.value.locations)
const totalLocations = computed(() =>  locationsData.value.totalLocations)


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

watch(activeIndex, () => {
  refLocations.value.forEach((location, index) => {
    if (index === activeIndex.value)
      location.classList.add('marker-focus')
    else
      location.classList.remove('marker-focus')
  })
})

let openedMarkerID = ref(0)

const openMarker = (index, lat, lng) => {
  activeIndex.value = index
  showPanel.value.fill(false)
  showPanel.value[index] = !showPanel.value[index]
  if (vuetifyDisplay.mdAndDown.value)
    isLeftSidebarOpen.value = false

  openedMarkerID.value = index
  if (lat){
    mapRef.value.map.zoom = 15
    mapRef.value.map.panTo( { lat: lat, lng: lng })
    
  }

}
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
                @click="openMarker(index, parseFloat(location.latitude), parseFloat(location.longitude) )"
              >
                <div class="d-flex gap-x-4">
                  <VBadge
                    location="bottom right"
                    offset-x="3"
                    offset-y="3"
                    color="success"
                    :content="location.availablechargers"
                    bordered
                  >
                    <VAvatar
                      icon="tabler-plug"
                      color="success"
                      variant="tonal"
                    />
                  </VBadge>
                  <div>
                    <div class="text-body-1 text-high-emphasis font-weight-medium">
                      {{ location.locationname }}
                    </div>
                    <div class="text-body-1 text-disabled">
                      {{ location.locationstreet }}
                    </div>
                    <div class="text-body-1 text-disabled">
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
                    <!--
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
                    -->
                    <!--
                      <div>
                      <VList
                      v-for="(charger, index) in location.chargers"
                      key="index"
                      >
                      <VListItem>
                      <template #prepend>
                      <VIcon
                      v-if="charger.chargerstate === 'Available'" 
                      icon="tabler-circle-check"
                      size="24"
                      color="success"
                      />
                      <VIcon
                      v-else
                      icon="tabler-plug-x"
                      size="24"
                      color="warning"
                      />
                      </template>
                      <VListItemTitle>
                      {{ charger.chargername }}
                      </VListItemTitle>
                      <VListItemSubtitle class="mt-1">
                            
                      <VBadge
                      dot
                      location="start center"
                      offset-x="2"
                      :color="success"
                      class="me-3"
                      >
                      <span class="ms-4">{{ charger.chargerstate }}</span>
                      </VBadge> 
                            

                      <span class="text-xs text-disabled">{{ charger.chargerinfo }}</span>
                      </VListItemSubtitle>

                      <template #append>
                      <VBtn size="small">
                      Add
                      </VBtn>
                      </template>
                      </VListItem>
                      <VDivider v-if="index !== users.length - 1" /> 
                      </VList>
                      </div>   
                    -->

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
                          v-for="(charger, index) in location.chargers"
                          :key="index"
                          icon="tabler-circle-check"
                          dot-color="rgb(var(--v-theme-surface))"
                          :icon-color="charger.chargerstate === 'Available' ? 'success' : 'warning'"
                          fill-dot
                          size="22"
                          :elevation="0"
                        >
                          <div
                            v-if="charger.chargerstate === 'Available'"
                            class="text-sm text-uppercase text-success font-weight-medium"
                          >
                            {{ charger.chargername }}
                          </div>
                          <div
                            v-else
                            class="text-sm text-uppercase text-warning font-weight-medium"
                          >
                            {{ charger.chargername }}
                          </div>
                          <div
                            v-if="charger.chargerstate === 'Available'"
                            class="text-body-2 text-disabled"
                          >
                            Available 
                          </div>
                          <div
                            v-else="charger.chargerstate === 'Available'"
                            class="text-body-2 text-disabled" 
                          >
                            {{ charger.chargerinfo }}
                          </div>
                          <VBtn size="small">
                            Book
                          </VBtn>
                        </VTimelineItem>

                      <!--
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
                      -->
                      </VTimeline>
                    </div>
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
        <GoogleMap
          ref="mapRef"
          :api-key="mapOptions.apiKey"
          class="basemap"
          :center="mapOptions.center"
          :zoom="mapOptions.zoom"
          :map-type-id="mapOptions.mapTypeId"
          :fullscreen-control="mapOptions.fullscreenControl"
          zoom-control
          :map-type-control="mapOptions.mapTypeControl"
          :street-view-control="mapOptions.streetViewControl"
          :rotate-control="false"
        >
          <!-- <Marker :options="markerOptions" /> -->
          <CustomMarker
            v-for="(m, index) in markers"
            :key="index"
            :options="{ position: m.position, anchorPoint: 'BOTTOM_CENTER' }"
            :title="m.text"
            :clickable="true"
            :draggable="false"
            :visible="true"
            @click="openMarker(index,m.position.lat, m.position.lng)"
          >
            <img
              :src="m.src"
              width="50"
              height="50"
              style="margin-block-start: 8px"
            >
          </CustomMarker>
          <!--
            <Marker 
            v-for="(m, index) in markers"
            :key="index"
            :position="m.position"
            :label="L"
            :title="m.text"
            :clickable="true"
            :draggable="false"
            :visible="true"
            @click="openMarker(index,m.position.lat, m.position.lng)"
            >
            <GMapInfoWindow
            :closeclick="true"
            :opened="openedMarkerID === index"
            @closeclick="openMarker(null,null, null)"
            >
            <div>I am in info window {{ m.text }} </div>
            </GMapInfoWindow>
            </Marker> 
          -->
        </GoogleMap>
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
