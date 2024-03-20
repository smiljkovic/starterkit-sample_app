
<script setup>
import mastercard from '@images/icons/payments/mastercard.png';
import visa from '@images/icons/payments/visa.png'
import unknown from '@images/icons/payments/unknown.png'

const props = defineProps({
  cardDetails: {
    type: Object,
    required: false,
    default: () => ({
      cardnumber: '',
      cardholdername: '',
      cardexpirationdate: '',
      cardcvc: '',
      isPrimary: false,
      type: 'unknown',
      id: '',
    }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const refVForm = ref()

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const cardDetails = ref(structuredClone(toRaw(props.cardDetails)))

watch(props, () => {
  cardDetails.value = structuredClone(toRaw(props.cardDetails))


})

onMounted(() => {
  // we can implement any method here like
  refVForm.value?.validate()

})

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const formSubmit = async () => {
  refVForm.value?.validate().then(async ({ valid: isValid }) => {
    if (isValid) {
      try {
        const resModules = await $api('/vtiger/add-card', {
          method: 'POST',
          headers: { "Content-Type": "application/json", 'Authorization': 'Basic' },
          body: {
            'username': useCookie('email').value,
            'password': useCookie('pwd').value,
            'recordId': cardDetails.value.id,
            'record': {
              'billingname': '**** **** **** ' + cardDetails.value.cardnumber.substring(cardDetails.value.cardnumber.length - 4),
              'payment': 'Credit or debit card',
              'cardnumber': cardDetails.value.cardnumber,
              'cardexpirationdate': cardDetails.value.cardexpirationdate,
              'cardcvc': cardDetails.value.cardcvc,
              'cardholdername': cardDetails.value.cardholdername,
              'type': cardDetails.value.type,

            },
          },
          onResponse({ response }) {
            if (response.ok) {
              emit('submit', cardDetails.value)
              dialogModelValueUpdate(false)

            } else {
              console.error('ERROR!!!')
            }
          },
        })


      } catch (err) {
        console.error(err)
      }
    }
  })
}


const onCardNumberUpdate = cardNumber => {
  cardDetails.value.cardnumber = cardNumber.replace(/ /g, '')
}

const formatCardNumber = () => {
  return cardDetails.value.cardnumber ? cardDetails.value.cardnumber.match(/.{1,4}/g).join(' ') : ''
}

const onExpiryDateUpdate = expiryDate => {
  let input = expiryDate;

  // Remove any non-digit characters
  input = input.replace(/\D/g, '');

  // Add slash after the first two characters if necessary
  if (input.length > 2) {
    input = input.slice(0, 2) + '/' + input.slice(2);
  }

  // Update the input field value
  cardDetails.value.cardexpirationdate = input;
}



const getCardImage = image => {
  cardDetails.value.type = image

  if (image === 'mastercard')
    return mastercard

  if (image === 'visa')
    return visa

  if (image === 'unknown')
    return unknown

}

</script>

<template>
  <VDialog :width="$vuetify.display.smAndDown ? 'auto' : 580" :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-5 pa-sm-8">
      <!-- 👉 Title -->
      <VCardItem class="text-center">
        <VCardTitle class="text-h3 font-weight-medium mb-3">
          {{ props.cardDetails.cardholdername ? 'Edit Card' : 'Add New Card' }}
        </VCardTitle>
        <p class="mb-0">
          {{ props.cardDetails.cardholdername ? 'Edit your saved card details' : 'Add your saved card details' }}
        </p>
      </VCardItem>

      <VCardText class="pt-6">
        <VForm @submit.prevent="() => { }" ref="refVForm">
          <VRow>
            <!-- 👉 Card Number -->
            <!-- <VCol cols="10" md="10">
              <AppTextField :value="formatCardNumber(cardDetails.cardnumber)" label="Card Number"
                placeholder="1234 1234 1234 1234" type="text" @update:model-value="onCardNumberUpdate"
                :rules="[requiredValidator, isValid]" />
            </VCol> -->
            <VCol cols="10" md="10">
              <AppTextField v-model="cardDetails.cardnumber" label="Card Number" placeholder="1234 1234 1234 1234"
                type="text" @update:model-value="onCardNumberUpdate" :rules="[requiredValidator, isValid]" />
            </VCol>

            <VCol cols="2" md="2" class="mt-8">
              <VImg :src="getCardImage(getCreditCardNameByNumber(cardDetails.cardnumber).toLowerCase())" width="46"
                height="25" />
            </VCol>


            <!-- 👉 Card Name -->
            <VCol cols="12" md="6">
              <AppTextField v-model="cardDetails.cardholdername" label="Name" placeholder="John Doe"
                :rules="[requiredValidator]" />
            </VCol>

            <!-- 👉 Card Expiry -->
            <VCol cols="6" md="3">
              <AppTextField v-model="cardDetails.cardexpirationdate" label="Expiry Date" placeholder="MM/YY"
                @update:model-value="onExpiryDateUpdate" type="text"
                :rules="[requiredValidator, isExpirationDateValid]" />
            </VCol>

            <!-- 👉 Card CVV -->
            <VCol cols="6" md="3">
              <AppTextField v-model="cardDetails.cardcvc" type="number" label="CVV Code" placeholder="123"
                :rules="[requiredValidator, isSecurityCodeValid(cardDetails.cardnumber, cardDetails.cardcvc)]" />
            </VCol>

            <!-- 👉 Card Primary Set -->
            <VCol cols="12">
              <VSwitch v-model="cardDetails.isPrimary" label="Set as primary card" />
            </VCol>

            <!-- 👉 Card actions -->
            <VCol cols="12" class="text-center">
              <VBtn class="me-3" type="submit" @click="formSubmit">
                Submit
              </VBtn>
              <VBtn color="secondary" variant="tonal" @click="$emit('update:isDialogVisible', false)">
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
