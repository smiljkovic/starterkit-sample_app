<script setup>
const isPricingPlanDialogVisible = ref(false)

const props = defineProps({
  currentPlan: {
    type: Object,
    required: true,
    /* default: () => ({
      gatewayName: 'GW Name',
      planName: 'Plan Name',
      startDate: '05-12-2023',
      payment: 'Yearly',
    }), */
  },
})


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

</script>

<template>
  <VRow>
    <!-- 👉 Current Plan -->
    <VCol cols="12">
      <VCard title="Subscription Overview">
        <!-- <VCard :title="'Smart home hub: '+currentPlan.gatewayName" > -->
        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <div>
                <div class="mb-6">
                  <h3 class="text-base font-weight-medium mb-1">
                    Your current plan for smart home hub "{{ currentPlan.plan.gatewayName }}" is <span
                      class="font-weight-bold">{{ currentPlan.plan.planName }} </span>
                  </h3>
                  <p class="text-base">
                    A simple start for everyone
                  </p>
                </div>

                <div class="mb-6">
                  <h3 class="text-base font-weight-medium mb-1">
                    Active until {{ new Date(currentPlan.plan.startDate).toLocaleDateString('en-GB', {
                      day: '2-digit', // "01"
                      month: 'short', // "Jun"
                      year: 'numeric' // "2019"
                    }) }}
                  </h3>
                  <p class="text-base">
                    We will send you a notification upon subscription expiration
                  </p>
                </div>

                <div class="mb-6">
                  <h3 class="text-base font-weight-medium mb-1">
                    The plan is FREE.
                  </h3>
                  <p class="text-base">
                    To unlock full potential of your smart home we recomend an upgrade to one of our top-selling plans:
                  </p>
                </div>

                <div v-for="(plan, index) in    currentPlan.services   " :key="id" class="mb-6"
                  v-show="plan.cf_sub_id > currentPlanId">
                  <h2 class="text-base font-weight-medium mb-1">
                    <span class="me-3">{{ plan.servicename }}</span>
                    <!-- <VChip color="primary" size="small" label v-show="plan.cf_badge == 'Popular'">
                      Popular
                    </VChip>
                    <VChip color="primary" size="small" label v-show="plan.cf_badge == 'Premium'">
                      Popular
                    </VChip> -->

                    <VChip v-if="plan.cf_badge == 'Popular'" label color="primary" size="small">
                      {{ plan.cf_badge }}
                    </VChip>
                    <VChip v-if="plan.cf_badge == 'Premium'" label color="success" size="small">
                      <VIcon start size="16" icon="tabler-star" />
                      {{ plan.cf_badge }}
                    </VChip>
                  </h2>
                  <p class="text-base mb-0">
                    {{ plan.cf_shortdescription }}
                  </p>
                </div>

                <!-- <div v-for="(plan, index) in    currentPlan.services   " :key="id" class="mb-6 d-flex column flex-grow">
                  <h3 class="text-base font-weight-medium mb-1">
                    <span class="me-3">{{ plan.servicename }}</span>
                    <p class="text-base">
                  {{ plan.cf_shortdescription }}
                </p>
                    <VChip color="primary" size="small" label>
                      Popular
                    </VChip>
                  </h3>
                  
                </div> -->

                <!-- <VCol v-for="(plan, index) in    currentPlan.services   " :key="id" sm="6" md="4" lg="4" cols="12"
                  class="d-flex column flex-grow">
                  <h3 class="text-h3 text-center">
                    {{ plan.servicename }}
                  </h3>

                  <VCard flat border
                    :style="plan.cf_badge == 'Popular' ? 'border:1px solid rgb(var(--v-theme-primary))' : ''">
                    <VCardText style="block-size: 3rem;" class="text-end">
                      
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

                        
                        <span v-show="annualMonthlyPlanPriceToggler"
                          class="annual-price-text position-absolute text-sm text-disabled mb-0">
                          {{ Math.floor(plan.unit_price) == 0 ? 'FREE' : `CHF
                          ${Math.floor(plan.unit_price * 12 * 0.9)} /Year` }}
                        </span>
                      </div>
                      <VList class="card-list pt-4 mb-4">
                        <VListItem v-for="(item, i) in plan.features" :key="i">
                          <template #prepend>
                            <VAvatar size="16" :variant="!plan.current ? 'tonal' : 'elevated'" color="primary"
                              class="me-4">
                              <VIcon icon="tabler-check" size="12" :color="!plan.current ? 'primary' : 'white'" />
                            </VAvatar>
                            <div class="text-body-1 font-weight-medium text-high-emphasis">
                              {{ item }}
                            </div>
                          </template>
                        </VListItem>
                      </VList>
                    </VCardText>




                  </VCard>

                </VCol> -->


              </div>
            </VCol>

            <VCol cols="12" md="6">
              <VAlert color="warning" variant="tonal">
                <VAlertTitle class="mb-1">
                  We need your attention!
                </VAlertTitle>

                <span>Your plan requires update</span>
              </VAlert>

              <!-- progress -->
              <h6 class="d-flex font-weight-medium text-base mt-4 mb-2">
                <span>Days</span>
                <VSpacer />
                <span>24 of 30 Days</span>
              </h6>

              <VProgressLinear color="primary" rounded height="12" model-value="75" />

              <p class="text-base mt-2 mb-0">
                6 days remaining until your plan requires update
              </p>
            </VCol>

            <VCol cols="12">
              <div class="d-flex flex-wrap gap-y-4">
                <VBtn class="me-3" @click="isPricingPlanDialogVisible = true"
                  :to="{ name: 'apps-services-list', params: { id: currentPlanId } }">
                  upgrade plan
                </VBtn>

                <VBtn color=" secondary" variant="tonal" @click="isConfirmDialogVisible = true"
                  v-show="currentPlanId > 1">
                  Cancel Subscription
                </VBtn>
              </div>
            </VCol>
          </VRow>

          <!-- 👉 Confirm Dialog -->
          <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible"
            confirmation-question="Are you sure to cancel your subscription?" cancel-msg="Unsubscription Cancelled!!"
            cancel-title="Cancelled" confirm-msg="Your subscription cancelled successfully."
            confirm-title="Unsubscribed!" />

          <!-- 👉 plan and pricing dialog -->
          <PricingPlanDialog v-model:is-dialog-visible="isPricingPlanDialogVisible" />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
