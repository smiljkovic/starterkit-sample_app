
<script setup>
import { ref, onMounted } from "vue";
import { loadStripe } from "@stripe/stripe-js";

//import SrMessages from "./SrMessages.vue";

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
    default: false,
  },
})
const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const cardDetails = ref(structuredClone(toRaw(props.cardDetails)))

const isLoading = ref(false);
let messages = ref(null);

let elements;
const refElement = ref()
const publishableKey = await useApi(createUrl('/vtiger/getstripekey')).then((res) => res.data.value);
let stripe = await loadStripe(publishableKey);

onMounted(async () => {
  messages = ref(null)

});



const domLoaded = async () => {
  messages = ref(null)
  
  // New card
      const clientSecret = await useApi(createUrl('/vtiger/createsetupintent', 
          {
            query: {
              'stripeCustomerId' : useCookie('userData').value['cf_stripe_id'],
            },
          }
          )).then(
        (res) => res.data.value);





      const options = {
        clientSecret: clientSecret,
        // mode: 'setup',
        currency: 'usd',
        theme: 'flat',
        /* layout: {
          type: 'accordion',
          defaultCollapsed: false,
          radios: true,
          spacedAccordionItems: false
        }, */
        // Fully customizable with appearance API.
        appearance: {/*...*/ },
      };

      const appearance = { /* appearance */ };
      elements = stripe.elements(options);
      const paymentElement = elements.create('payment', options);
  
  paymentElement.mount("#payment-element");
  //const linkAuthenticationElement = elements.create("linkAuthentication");
  //linkAuthenticationElement.mount("#link-authentication-element");
  isLoading.value = false;
}

watch(props, () => {
  if (props.isDialogVisible) {
    // Here you would put something to happen when dialog opens up
    console.log("Dialog was opened!")
    messages = ref(null)

  } else {
    console.log("Dialog was closed!")
  }
}
)

const saveCard = async (pm) => {

  try {
    const resModules = await $api('/vtiger/add-stripe-card', {
      method: 'POST',
      headers: { "Content-Type": "application/json", 'Authorization': 'Basic' },
      body: {
        'username': useCookie('email').value,
        'password': useCookie('pwd').value,
        'stripePaymentMethodId': pm,
        'stripeCustomerId' : useCookie('userData').value['cf_stripe_id'],
        'recordId': cardDetails.id,
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
    messages = err
  }
}


const handleSubmit = async (e) => {
  if (isLoading.value) {
    return;
  }
  messages = ref(null)
  isLoading.value = true;

  await stripe.confirmSetup({
    //`Elements` instance that was used to create the Payment Element
    elements,
    confirmParams: {
      return_url: 'https://oblotech.com',
      //return_url: 'https:192.168.56.101',
    },
    redirect: 'if_required',
  }).then(function (result) {
    if (result.error) {
      // Inform the customer that there was an error.
      messages = result.error.type
    }
    else {
      console.log(result.setupIntent)
      const setupIntent = result.setupIntent
      if (setupIntent.status === 'succeeded') {
        console.log(setupIntent.payment_method)
        saveCard(setupIntent.payment_method)
      } else {
        messages = 'Setup intent not successful!'
      }
    }
  });
  isLoading.value = false;
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
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
        <p class="mb-3">
          {{ props.cardDetails.cardholdername ? 'Edit your saved card details' : 'Add your saved card details' }}
        </p>
        <form id="payment-form" @submit.prevent="handleSubmit" @vnodeMounted="domLoaded">
          <div id="link-authentication-element" />
          <div id="payment-element" ref="refElement">

          </div>
          <!-- 👉 Card actions -->
          <VBtn id="submit" class="me-3 mt-5" type="submit" :disabled="isLoading" :error-messages="messages">
            Submit
          </VBtn>
          <VBtn color="secondary" class="me-3 mt-5" variant="tonal" @click="$emit('update:isDialogVisible', false)"
            :disabled="isLoading">
            Cancel
          </VBtn>
          <!-- <button id="submit" :disabled="isLoading">
                Pay now
              </button> -->
          <!-- <sr-messages :messages="messages" /> -->
        </form>
        <VCardText class="pt-6" v-if="messages" style="color: rgb(var(--v-theme-error))">
          {{ messages }}
        </VCardText>
      </VCardItem>
    </VCard>
  </VDialog>
</template>
