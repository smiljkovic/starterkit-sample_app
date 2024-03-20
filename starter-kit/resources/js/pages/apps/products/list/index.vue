<script setup>
import eCommerce2 from '@images/pages/iphone-11.png'
/*const products = [
{
  id: '0',
  name: 'IKEA Symfonisk',
  url: eCommerce2,
  description: 'Control IKEA Symfonisk speaker from your smart home application. Easy create a rule that wakes you up with your favorite radio station. And much more...',
  price: '99.00 CHF',
},
{
  name: 'Mildred Wagner',
  number: '4851234567895896',
  expiry: '10/27',
  isPrimary: false,
  type: 'mastercard',
  cvv: '123',
  image: visa,
}, 
]*/

const {
  data: productsData,
  execute: fetchProducts,
} = await useApi(createUrl('/vtiger/products', {
  query: {
    'username': useCookie('email').value, 'password': useCookie('pwd').value,
  },
}))

const products = computed(() => productsData.value.products)

</script>
<template>
  <VRow>
    <!-- 👉 Apple iPhone 11 Pro -->
    <VCol v-for="product in                 products                " :key="product.id" sm="6" cols="12">
      <VCard>
        <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
          <div class="ma-auto pa-5">
            <VImg width="137" height="176" :src="product.url" />
          </div>

          <VDivider :vertical="$vuetify.display.mdAndUp" />

          <div>
            <VCardItem>
              <VCardTitle>{{ product.productname }}</VCardTitle>
            </VCardItem>

            <VCardText>
              <span v-html="product.description"></span>

            </VCardText>

            <VCardText class="text-subtitle-1">
              <span>Price :</span> <span class="font-weight-medium">{{ parseFloat(product.unit_price).toFixed(2) }}
                CHF</span>
            </VCardText>

            <VCardActions class="justify-space-between">
              <VBtn>
                <VIcon icon="tabler-shopping-cart-plus" />
                <span class="ms-2">Add to cart</span>
              </VBtn>

              <IconBtn color="secondary" icon="tabler-share" />
            </VCardActions>
          </div>
        </div>
      </VCard>
    </VCol>
  </VRow>
</template>
