<script setup>
import { ref, onMounted } from "vue";
import { loadStripe } from "@stripe/stripe-js";

import SrMessages from "./SrMessages.vue";

const isLoading = ref(false);
const messages = ref([]);

let stripe;
let elements;

onMounted(async () => {
  stripe = await loadStripe("pk_test_51OKJS7L9id96rr8Dlu0FFWeV7yy1NBJpN567P12x6sW1fi3aaohzIx6OkNp4DvbUifHrGrT8FN6bMEwgXxtMns5m001WaEfNe3");

  const options = {
  mode: 'setup',
  currency: 'usd',
  // Fully customizable with appearance API.
  appearance: {/*...*/},
};
  elements = stripe.elements(options);
  const paymentElement = elements.create('payment');
  paymentElement.mount("#payment-element");
  const linkAuthenticationElement = elements.create("linkAuthentication");
  linkAuthenticationElement.mount("#link-authentication-element");
  isLoading.value = false;
});

const handleSubmit = async () => {
  if (isLoading.value) {
    return;
  }

  isLoading.value = true;

  const { error } = await stripe.confirmPayment({
    elements,
    confirmParams: {
      return_url: `${window.location.origin}/return`
    }
  });

  if (error.type === "card_error" || error.type === "validation_error") {
    messages.value.push(error.message);
  } else {
    messages.value.push("An unexpected error occured.");
  }

  isLoading.value = false;
}
</script>
<template>
  <main>
    
    <form
      id="payment-form"
      @submit.prevent="handleSubmit"
    >
      <div id="link-authentication-element" />
      <div id="payment-element" />
      <button
        id="submit"
        :disabled="isLoading"
      >
        Pay now
      </button>
      <sr-messages :messages="messages" />
    </form>
  </main>
</template>
