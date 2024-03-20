<script setup>
import mastercard from '@images/icons/payments/mastercard.png'
import visa from '@images/icons/payments/visa.png'



const selectedPaymentMethod = ref('credit-debit-atm-card')
const isPricingPlanDialogVisible = ref(false)
const isConfirmDialogVisible = ref(false)
const isCardEditDialogVisible = ref(false)
const isCardDetailSaveBilling = ref(false)
const isCardAddDialogVisible = ref(false)

const currentCardDetails = ref()


const {
  data: creditCardData,
  execute: fetchCreditCards,
} = await useApi(createUrl('/vtiger/creditcards', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,
  },
}))

const creditCards = computed(() => creditCardData.value.cards)

const openEditCardDialog = cardDetails => {
  currentCardDetails.value = cardDetails
  isCardEditDialogVisible.value = true
}

const onDeleteCard = async (cardDetails) => {




  try {
    const resModules = await $api('/vtiger/delete-card', {
      method: 'POST',
      headers: { "Content-Type": "application/json", 'Authorization': 'Basic' },
      body: {
        'username': useCookie('email').value,
        'password': useCookie('pwd').value,
        'id': cardDetails.id,
        'stripePaymentMethodId': cardDetails.cardholdername,
        'stripeCustomerId' : useCookie('userData').value['cf_stripe_id'],
      },
      onResponse({ response }) {
        if (response.ok) {


        } else {
          console.error('ERROR!!!')
        }
      },
    })


  } catch (err) {
    console.error(err)
  }
  fetchCreditCards()
}


const getCardImage = image => {
  if (image === 'mastercard')
    return mastercard

  if (image === 'visa')
    return visa

}

/*
const cardNumber = ref(135632156548789)
const cardName = ref('john Doe')
const cardExpiryDate = ref('05/24')
const cardCvv = ref(420)

 const resetPaymentForm = () => {
  cardNumber.value = 135632156548789
  cardName.value = 'john Doe'
  cardExpiryDate.value = '05/24'
  cardCvv.value = 420
  selectedPaymentMethod.value = 'credit-debit-atm-card'
} */
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Payment Methods">
        <VCardText>
          <VForm @submit.prevent="() => { }">
            <VRow>
              <!-- 👉 Nikola -->
              <VCol v-for="card in  creditCards " :key="card.cardholdername" cols="12" md="6" lg="6">
                <VCard flat color="rgba(var(--v-theme-on-surface),0.04)" class="fill-height">
                  <!--
                    <VCardItem>
                    <VCardTitle class="text-white">
                    {{ data.title }}
                    </VCardTitle>
                    </VCardItem> 
                  -->

                  <VCardText class="d-flex flex-sm-row flex-column pa-4">
                    <div class="text-no-wrap">
                      <VImg :src="getCardImage(card.type)" width="
                        46" height="20" />
                      <h4 class="my-3 text-body-1">
                        <span class="me-3">
                          
                          {{useCookie('userData').value['name']}} {{useCookie('userData').value['lastname']}} 
                        </span>
                        <VChip v-if="card.isPrimary" label color="primary" size="small">
                          Primary
                        </VChip>
                      </h4>
                      <div class="text-body-1">
                        {{ card.billingname }}
                      </div>
                    </div>

                    <VSpacer />

                    <div class="d-flex flex-column text-sm-end">
                      <div class="d-flex row justify-end flex-wrap gap-4 order-sm-0 order-1">
                      <!--   <VBtn variant="tonal" @click="openEditCardDialog(card)">
                          Edit
                        </VBtn> -->
                        <VBtn color="secondary" variant="tonal" @click="onDeleteCard(card)">
                          Delete
                        </VBtn>
                      </div>

                      <span class="text-body-2 mt-sm-auto mb-sm-0 my-5 order-sm-1 order-0"
                        v-if="isExpirationDateValid(card.cardexpirationdate)">Card expires at
                        {{
                          card.cardexpirationdate }}</span>
                      <span class="text-body-2 mt-sm-auto mb-sm-0 my-5 order-sm-1 order-0 text-error" v-else>Card
                        expired at {{
                          card.cardexpirationdate }}</span>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
              <!-- 👉 Saved Cards -->
            </VRow>
          </VForm>
          <!-- Title, Subtitle & Action Button -->
          <div class="d-flex justify-space-between flex-wrap pt-8">
            <!-- NOTE - this is empty div to make buttor right aligned -->
            <div class="me-2 mb-2">

            </div>
            <VBtn prepend-icon="tabler-plus" @click="isCardAddDialogVisible = !isCardAddDialogVisible">
              Add payment method
            </VBtn>
             <StripeCardAddEditDialog v-model:isDialogVisible="isCardAddDialogVisible" @submit="fetchCreditCards" /> 
            
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  <!-- 👉 Edit Card Dialog -->
  <StripeCardAddEditDialog v-model:isDialogVisible="isCardEditDialogVisible" :card-details="currentCardDetails"
    @submit="fetchCreditCards" />
</template>

<style lang="scss">
.pricing-dialog {
  .pricing-title {
    font-size: 1.5rem !important;
  }

  .v-card {
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    box-shadow: none;
  }
}
</style>
