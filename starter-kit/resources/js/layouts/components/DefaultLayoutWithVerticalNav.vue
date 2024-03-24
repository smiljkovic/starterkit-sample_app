<script setup>
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'
import { watch } from 'vue'

const router = useRouter()


/*TODO - This should be moved to js file as in Horizontal case */

// TODO: Get type from backend
const moduleIcons = [
  { title: 'Billing', icon: 'tabler-credit-card' },
  { title: 'Products', icon: 'tabler-trolley' },
  { title: 'HelpDesk', icon: 'tabler-headset' },
  { title: 'Invoice', icon: 'tabler-file-euro' },
  { title: 'Services', icon: 'tabler-diamond' },

]

const updateIcons = arr => {

  try {
    Object.entries(arr).forEach(entry => {
      const [index, value] = entry
      let newEl = ref(0)
      if (newEl = moduleIcons.find(element => element.title === value.id)) {
        value.icon.icon = newEl.icon
      }
    })

    return arr
  }
  catch {
    // Redirect to login page
    router.push('/login')
  }
}

const navItems = updateIcons(useCookie('userModulesSorted').value)


// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
  isFallbackStateActive,
  refLoadingIndicator,
], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()
  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })
// !SECTION
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            size="26"
            icon="tabler-menu-2"
          />
        </IconBtn>

        <NavbarThemeSwitcher />

        <VSpacer />

        <NavBarI18n
          v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
        />
        <UserProfile />
      </div>
    </template>

    <AppLoadingIndicator ref="refLoadingIndicator" />

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Suspense
        :timeout="0"
        @fallback="isFallbackStateActive = true"
        @resolve="isFallbackStateActive = false"
      >
        <Component :is="Component" />
      </Suspense>
    </RouterView>
    <VBottomNavigation
      v-model="value"
      grow
    >
      <VBtn
        value="recent"
        :color="success"
        variant="tonal"
      >
        <VIcon
          :color="success"
          :variant="tonal"
          icon="tabler-user"
        />

        <span>Recent</span>
      </VBtn>

      <VBtn value="favorites">
        <VIcon>mdi-heart</VIcon>

        <span>Favorites</span>
      </VBtn>

      <VBtn value="nearby">
        <VIcon>mdi-map-marker</VIcon>

        <span>Nearby</span>
      </VBtn>
    </VBottomNavigation>
    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>
