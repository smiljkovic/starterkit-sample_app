<script setup>
import paperPlane from '@images/icons/paper-airplane.png'
import plane from '@images/icons/plane.png'
import pricingPlanArrow from '@images/icons/pricing-plans-arrow.png'
import shuttleRocket from '@images/icons/shuttle-rocket.png'

import safeBoxWithGoldenCoin from '@images/misc/3d-safe-box-with-golden-dollar-coins.png'
import spaceRocket from '@images/misc/3d-space-rocket-with-smoke.png'
import dollarCoinPiggyBank from '@images/misc/dollar-coins-flying-pink-piggy-bank.png'


const annualMonthlyPlanPriceToggler = ref(true)

const planImages = [

  dollarCoinPiggyBank,
  safeBoxWithGoldenCoin,
  spaceRocket,
]

const {
  data: servicesData,
  execute: fetchServices,
} = await useApi(createUrl('/vtiger/services', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,
  },
}))

const pricingPlans = computed(() => servicesData.value.services)

const {
  data: planData,
  execute: fetchSubscriptionPlanInfo,
} = await useApi(createUrl('/vtiger/plan-info', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,
  },
}))

const currentPlan = computed(() => planData.value)
const currentPlanId = computed(() => {
  let i = 0;

  while (i < currentPlan.value.services.length) {
    if (currentPlan.value.plan.planName === currentPlan.value.services[i].servicename) {
      break
    }
    i++;
  }
  return i + 1
})

const router = useRouter()

/* const onClick = (plan) => {
  router.push({
    name: 'payment',
    params: {
      planDetails: plan
    }
  })
} */

const isPaymentVisible = ref(true)

const preparePaymentCheckoutInput = (currentPlan) => {
  return JSON.stringify(currentPlan)
}

</script>

<template>
  <div id="pricing-plan">
    <VContainer>
      <div class="pricing-plans">
        <!-- 👉 Headers  -->
        <div class="headers d-flex justify-center flex-column align-center mb-8 flex-wrap">
          <!-- <VChip label color="primary" class="mb-4">
            Pricing Plans
          </VChip> -->
          <div class="d-flex align-center text-h3 mb-1 flex-wrap justify-center">
            <div class="position-relative me-2">
              <h4 class="section-title">
                Tailored subscription plans for your perfect smart home
              </h4>
            </div>

          </div>
          <div class="text-center">
            <!-- <p class="mb-0">
              For your perfect smart home.
            </p> -->
            <p class="mb-0">
              Choose the best plan to fit your needs.
            </p>
          </div>
        </div>
        <!-- 👉 Annual and monthly price toggler -->
        <div class="d-flex align-center justify-center mx-auto my-10">

          <VLabel for="pricing-plan-toggle" class="me-2 font-weight-medium">
            Pay Monthly
          </VLabel>
          <div class="position-relative">
            <VSwitch id="pricing-plan-toggle" v-model="annualMonthlyPlanPriceToggler" label="Pay Annually" />
            <div class="position-absolute pricing-plan-arrow d-md-flex d-none">
              <VImg :src="pricingPlanArrow" class="flip-in-rtl" width="60" height="42" />
              <div class="text-no-wrap font-weight-medium">
                Save 25%
              </div>
            </div>
          </div>
        </div>
        <VRow>
          <VCol v-for="(plan, index) in     pricingPlans    " :key="id" sm="6" md="4" lg="4" cols="12"
            class="d-flex column flex-grow">
            <VCard flat border :style="plan.cf_badge == 'Popular' ? 'border:1px solid rgb(var(--v-theme-primary))' : ''">
              <VCardText style="block-size: 3rem;" class="text-end">
                <!-- 👉 Popular -->
                <VChip v-if="plan.cf_badge == 'Popular'" label color="primary" size="small">
                  {{ plan.cf_badge }}
                </VChip>
                <VChip v-if="plan.cf_badge == 'Premium'" label color="success" size="small">
                  <VIcon start size="16" icon="tabler-star" />
                  {{ plan.cf_badge }}
                </VChip>
              </VCardText>
              <VCardText class="pa-8">

                <VImg :src="index < 2 ? planImages[index] : planImages[2]" width="120" height="120"
                  class="mx-auto mb-4" />
                <h3 class="text-h3 text-center">
                  {{ plan.servicename }}
                </h3>
                <p class="mb-0 text-center">
                  {{ plan.cf_shortdescription }}
                </p>


                <!-- 👉 Plan price  -->
                <div class="d-flex justify-center position-relative">
                  <div class="d-flex justify-center align-center py-6">
                    <sup class="text-sm text-primary me-2">CHF</sup>
                    <h1 class="text-5xl font-weight-medium text-primary">
                      <span class="text-primary">{{ annualMonthlyPlanPriceToggler ?
                        Math.floor(plan.unit_price * 0.9) :
                        Math.floor(plan.unit_price) }}</span>
                    </h1>
                    <sub class="text-sm text-disabled me-2">/month</sub>
                  </div>

                  <!-- 👉 Annual Price -->
                  <span v-show="annualMonthlyPlanPriceToggler"
                    class="annual-price-text position-absolute text-sm text-disabled mb-0">
                    {{ Math.floor(plan.unit_price) == 0 ? 'FREE' : `CHF
                    ${Math.floor(plan.unit_price * 12 * 0.9)} /Year` }}
                  </span>
                </div>
                <VList class="card-list pt-4 mb-4">
                  <VListItem v-for="(item, i) in plan.features" :key="i">
                    <template #prepend>
                      <VAvatar size="16" :variant="!plan.current ? 'tonal' : 'elevated'" color="primary" class="me-4">
                        <VIcon icon="tabler-check" size="12" :color="!plan.current ? 'primary' : 'white'" />
                      </VAvatar>
                      <div class="text-body-1 font-weight-medium text-high-emphasis">
                        {{ item }}
                      </div>
                    </template>
                  </VListItem>
                </VList>
              </VCardText>
              <!-- <VCardText class="pa-4" >
                a
              </VCardText>  -->
              <VCardText class="pa-4">
                <!-- <RouterLink v-show="plan.cf_sub_id == currentPlanId">
                  <VBtn class="" block :variant="'tonal'">
                    Your current plan
                  </VBtn>
                </RouterLink>
                <RouterLink v-show="plan.cf_sub_id != currentPlanId">
                  :to="{ name: 'apps-payment-input', params: { input: preparePaymentCheckoutInput(plan) }, }">
                  <VBtn class="" block :variant="plan.current ? 'elevated' : 'tonal'">
                    Upgrade Plan
                  </VBtn>
                </RouterLink> -->

                <VBtn v-if="plan.cf_sub_id == currentPlanId" color="success" @click="" block :variant="'tonal'">
                  Your current plan
                  <VIcon icon="tabler-check" />
                </VBtn>

                <VBtn v-else :to="{ name: 'apps-payment-input', params: { input: preparePaymentCheckoutInput(plan) }, }"
                  block :variant="'elevated'">
                  Upgrade Plan

                  <VIcon icon="tabler-arrow-right" />
                </VBtn>



              </VCardText>




            </VCard>

          </VCol>
        </VRow>
      </div>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 8px;
}

#pricing-plan {
  border-radius: 3.75rem;

  //  background-color: rgb(var(--v-theme-background))
}

.pricing-plans {
  // margin-block: 5.25rem;

  // margin-block: 2rem;
}

@media (max-width: 600px) {
  .pricing-plans {
    // margin-block: 4rem;

    // margin-block: 1rem;
    text-align: center;
  }
}

.save-upto-chip {
  inset-block-start: -1.5rem;
  inset-inline-end: -7rem;
}

.pricing-plan-arrow {
  inset-block-start: -1.5rem;
  inset-inline-end: -8rem
}

.section-title::after {
  position: absolute;
  background: url('../../../assets/images/icons/section-title-icon.png') no-repeat left bottom;
  background-size: contain;
  block-size: 100%;
  content: '';
  font-weight: 700;
  inline-size: 120%;
  inset-block-end: 0;
  inset-inline-start: -12%;
}

.annual-price-text {
  inset-block-end: 0%;
}
</style>
