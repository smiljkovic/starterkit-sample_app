<script setup>
import mastercard from '@images/icons/payments/mastercard.png'
import visa from '@images/icons/payments/visa.png'

import { useRoute } from "vue-router";



definePage({ meta: { layout: 'blank' } })

/* const radioContent = [
  {
    title: 'Credit Card',
    value: 'credit card',
    images: visa.value,
  },
  {
    title: 'PayPal',
    value: 'paypal',
    images: paypal.value,
  },
] */

let radioContent = ref([])

const selectedCountry = ref('USA')
const isPricingPlanDialogVisible = ref(false)

const route = useRoute('apps-payment-input')

/* const planDetails = computed({
  return this.$route.params.planDetails

}) */
const getCardImage = image => {
  if (image === 'mastercard')
    return mastercard

  if (image === 'visa')
    return visa

}

const planDetails = JSON.parse(route.params.input)

const {
  data: creditCardData,
  execute: fetchCreditCards,
} = await useApi(createUrl('/vtiger/creditcards', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,
  },
  onResponse({ response }) {
    if (response.ok) {
      const tst = response._data.cards
      Object.entries(tst).forEach(entry => {
        radioContent.push({ 'title': entry.billingname, 'image': getCardImage(entry.type) })
      })
    }
  },
}))

const creditCards = computed(() => creditCardData.value.cards)

const selectedRadio = ref(creditCards.value[0].value)

</script>

<template>
  <!-- eslint-disable vue/attribute-hyphenation -->

  <div class="payment-page">


    <!-- 👉 Payment card  -->
    <VContainer>
      <div class="d-flex justify-center align-center payment-card">
        <VCard width="100%">
          <VRow>
            <VCol cols="12" md="7" :class="$vuetify.display.mdAndUp ? 'border-e' : 'border-b'">
              <VCardText>
                <!-- Checkout header -->
                <div>
                  <h4 class="text-h4 mb-2">
                    Checkout
                  </h4>
                  <div class="text-body-1">
                    All plans include 40+ advanced tools and features to boost your product. Choose the best plan to fit
                    your needs.
                  </div>
                </div>

                <CustomRadios v-model:selected-radio="selectedRadio" :key="creditCards.value" :value="creditCards.value"
                  :radio-content="creditCards" :grid-column="{ cols: '12', sm: '6' }" class="my-8">
                  <template #default="{ item }">
                    <div class="d-flex align-center gap-x-4">
                      <img :src="getCardImage(item.type)" height="20">
                      <div class="font-weight-medium text-high-emphasis">
                        {{ item.billingname }}
                      </div>
                    </div>
                  </template>
                </CustomRadios>

                <!-- billing Details -->
                <div class="mb-8">
                  <h4 class="text-h4 mb-6">
                    Billing Details
                  </h4>
                  <VForm>
                    <VRow>
                      <VCol cols="12" md="6">
                        <AppTextField label="Email Address" type="email" placeholder="johndoe@email.com" />
                      </VCol>
                      <VCol cols="12" md="6">
                        <AppTextField label="Password" type="password" placeholder="············" autocomplete="on" />
                      </VCol>
                      <VCol cols="12" md="6">
                        <AppSelect v-model="selectedCountry" label="Billing Country"
                          :items="['USA', 'Canada', 'UK', 'AUS']" />
                      </VCol>
                      <VCol cols="12" md="6">
                        <AppTextField label="Biling Zip/Postal Code" type="number" placeholder="129211" />
                      </VCol>
                    </VRow>
                  </VForm>
                </div>

                <!-- Credit card info -->
                <!-- <div class="mb-8" :class="selectedRadio === 'paypal' ? 'd-none' : 'd-block'">
                  <h4 class="text-h4 mb-6">
                    Credit Card Info
                  </h4>
                  <VRow>
                    <VCol cols="12">
                      <AppTextField label="Card Number" placeholder="8787 2345 3458" type="number" />
                    </VCol>

                    <VCol cols="12" md="4">
                      <AppTextField label="Card Holder" placeholder="John Doe" />
                    </VCol>

                    <VCol cols="12" md="4">
                      <AppTextField label="Exp. date" placeholder="05/2020" />
                    </VCol>

                    <VCol cols="12" md="4">
                      <AppTextField label="CVV" type="number" placeholder="784" />
                    </VCol>
                  </VRow>
                </div> -->
              </VCardText>
            </VCol>

            <VCol cols="12" md="5">
              <VCardText>
                <!-- order summary -->
                <div class="mb-8">
                  <h3 class="text-h4 mb-1">
                    Order Summary
                  </h3>
                  <div class="text-body-1">
                    Your new subscription plan
                  </div>
                </div>

                <VCard flat color="rgba(var(--v-theme-on-surface),0.08)">
                  <VCardText>
                    <h3 class="text-h4 mb-1">
                      {{ planDetails.servicename }}
                    </h3>
                    <div class="text-body-1">
                      {{ planDetails.cf_shortdescription }}
                    </div>
                    <div class="my-4">
                      <span class="order-price font-weight-medium text-high-emphasis">CHF {{
                        Math.floor(planDetails.unit_price)
                      }}</span> <span style="color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity))">/
                        month</span>
                    </div>
                    <!-- <VBtn variant="tonal" block @click="isPricingPlanDialogVisible = !isPricingPlanDialogVisible">
                      Change Plan
                    </VBtn> -->
                  </VCardText>
                </VCard>

                <div class="my-5">
                  <div class="d-flex justify-space-between">
                    <span>Subscription</span>
                    <h6 class="text-h6">
                      {{ parseFloat(planDetails.unit_price).toFixed(2) }}
                    </h6>
                  </div>
                  <div class="d-flex justify-space-between">
                    <span>Tax (included)</span>
                    <h6 class="text-h6">
                      {{ parseFloat(planDetails.unit_price * 0.2).toFixed(2) }}
                    </h6>
                  </div>
                  <VDivider class="my-4" />
                  <div class="d-flex justify-space-between">
                    <span>Total</span>
                    <h6 class="text-h6">
                      {{ parseFloat(planDetails.unit_price).toFixed(2) }}
                    </h6>
                  </div>
                </div>
                <div class="text-body-1">
                  By continuing, you accept to our Terms of Services and Privacy Policy. Please note that payments are
                  non-refundable.
                </div>
                <VBtn :append-icon="$vuetify.locale.isRtl ? 'tabler-arrow-left' : 'tabler-arrow-right'" block
                  color="success" class="mt-4 flip-in-rtl">
                  Proceed With Payment
                </VBtn>

                <VBtn block color="secondary" variant="tonal" class="mt-4 flip-in-rtl"
                  :to="{ name: 'apps-services-list' }">
                  Cancel
                </VBtn>

              </VCardText>
            </VCol>
          </VRow>
        </VCard>
      </div>
    </VContainer>


    <PricingPlanDialog v-model:is-dialog-visible="isPricingPlanDialogVisible" />
  </div>
</template>

<style lang="scss" scoped>
.footer {
  position: static !important;
  inline-size: 100%;
  inset-block-end: 0;
}

.payment-card {
  margin-block: 6.25rem;
}

.payment-page {
  @media (min-width: 600px) and (max-width: 960px) {
    .v-container {
      padding-inline: 2rem !important;
    }
  }
}

.order-price {
  font-size: 3rem;
}
</style>

<style lang="scss">
.payment-card {
  .custom-radio {
    .v-radio {
      margin-block-start: 0 !important;
    }
  }
}
</style>
