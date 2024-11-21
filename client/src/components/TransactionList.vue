<template>
  <v-container class="d-flex justify-center">
    <v-card title="Transactions" flat style="min-width:90vw; min-height:90vh;">

      <template v-slot:text>
        <v-row>
          <!-- Search Field -->
          <v-col cols="12" md="3">
            <v-text-field density="compact" v-model="transactionStore.search" label="Search"
              prepend-inner-icon="mdi-magnify" variant="outlined" hide-details single-line @input="debounceSearch" />
          </v-col>

          <!-- Type -->
          <v-col cols="12" md="2">
            <v-select density="compact" v-model="transactionStore.selectedType" :items="transactionStore.types"
              label="Type" :clearable="true" @update:modelValue="debounceSearch" />
          </v-col>



        </v-row>
        <v-row>

          <v-col cols="12" md="3">
            <v-date-input density="compact" v-model="transactionStore.dateRange" label="Dates" prepend-icon=""
              persistent-placeholder multiple="range" :min="minStartDate"
              @update:modelValue="debounceSearch"></v-date-input>
          </v-col>


          <!-- Reset Button -->
          <v-col cols="12" md="1" class="d-flex align-center">
            <v-btn icon color="primary" @click="transactionStore.resetFilters" class="mt-2">
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </template>

      <v-data-table-server v-model:items-per-page="transactionStore.itemsPerPage" :headers="headers"
        :items="transactionStore.paginatedTransactions" :items-length="transactionStore.totalTransactions"
        loading-text="Loading... Please wait" :search="transactionStore.search" item-value="id"
        @update:options="transactionStore.updateOptions" mobile-breakpoint="sm">





        <template v-slot:[`item.actions`]="{ item }">
          <v-btn icon @click="editType(item)" v-if="userStore.user">
            <v-icon>mdi-information</v-icon>
          </v-btn>

          <v-btn icon @click="goToLogin" v-else>
            <v-icon>mdi-login</v-icon>
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>

  <!-- Dialog for item details -->
  <v-dialog v-model="dialog" :persistent="false" class="custom-dialog">
    <TransactionDetail :type="selectedTransaction" :action="'details'" v-on:closeDialog="dialog = false" />
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useTransactionStore } from '@/stores/transaction';
import { useUserStore } from '@/stores/user';
import _ from 'lodash';
import TransactionDetail from './TransactionDetail.vue';
import { useRouter } from 'vue-router';

const transactionStore = useTransactionStore();
const userStore = useUserStore();
const router = useRouter();

const apiHost = process.env.VUE_APP_API_HOST;
const environment = process.env.VUE_APP_ENVIRONMENT;


const dialog = ref(false);

const headers = [

  {
    title: 'Actions',
    align: 'start',
    sortable: false,
    key: 'actions',
  },
  {
    title: 'Transaction',
    align: 'start',
    sortable: true,
    key: 'name',
  },
  {
    title: 'Type',
    align: 'start',
    sortable: false,
    key: 'type',
  },
  {
    title: 'Party',
    align: 'start',
    sortable: false,
    key: 'party',
  },
  {
    title: 'Amount',
    align: 'start',
    sortable: false,
    key: 'amount',
  },


];



const debounceSearch = _.debounce(() => {
  transactionStore.fetchPaginatedTransactions();
}, 300);

const editTransaction = (transaction) => {
  transactionStore.selectedTransaction = transaction;
  dialog.value = true;
};

const goToLogin = () => {
  router.push({ name: 'login-form' }); // Adjust the route name as necessary
};

// Computed properties for date constraints
const today = new Date();
today.setHours(0, 0, 0, 0);

const startOfDayInMillis = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

const minStartDate = computed(() => today);

// Watchers to ensure dates are correctly updated
watch(() => transactionStore.dateRange[0], (newStartDate) => {
  if (newStartDate > transactionStore.dateRange[transactionStore.dateRange.length - 1]) {
    transactionStore.dateRange[transactionStore.dateRange.length - 1] = new Date(newStartDate.getTime() + startOfDayInMillis);
  }
});

watch(() => transactionStore.dateRange[transactionStore.dateRange.length - 1], (newEndDate) => {
  if (newEndDate < transactionStore.dateRange[0]) {
    transactionStore.dateRange[0] = new Date(newEndDate.getTime() - startOfDayInMillis);
  }
});





onMounted(async () => {
  transactionStore.fetchTypes();
  transactionStore.fetchPaginatedTransactions();
})
</script>

<style>
.custom-dialog .v-overlay__content {
  pointer-events: none;
}

.custom-dialog .v-card {
  pointer-events: auto;
}
</style>
