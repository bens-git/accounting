<template>
  <v-container class="d-flex justify-center">
    <v-card
      title="Transactions"
      flat
      style="min-width: 90vw; max-height: 88vh; min-height: 88vh"
    >
      <template v-slot:text>
        <v-row>
          <!-- Search Field -->
          <v-col cols="12" md="3">
            <v-text-field
              density="compact"
              v-model="transactionStore.search"
              label="Search"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              hide-details
              single-line
              @input="debounceSearch"
            />
          </v-col>

          <!-- Type -->
          <v-col cols="12" md="3">
            <v-select
              density="compact"
              v-model="transactionStore.selectedType"
              :items="transactionStore.types"
              label="Type"
              :clearable="true"
              @update:modelValue="debounceSearch"
            />
          </v-col>

          <!-- Tag -->
          <v-col cols="12" md="2">
            <v-select
              density="compact"
              v-model="transactionStore.selectedTag"
              :items="transactionStore.tags"
              label="Tag"
              :clearable="true"
              @update:modelValue="debounceSearch"
            />
          </v-col>
          <v-col cols="12" md="2">
            <!-- Month Picker -->
            <v-select
              density="compact"
              v-model="transactionStore.selectedMonth"
              :items="transactionStore.months"
              label="Month"
              outlined
              class="me-4"
              @update:modelValue="debounceSearch"
            />
          </v-col>
          <v-col cols="12" md="2">
            <!-- Year Picker -->
            <v-select
              density="compact"
              v-model="transactionStore.selectedYear"
              :items="years"
              label="Year"
              outlined
              @update:modelValue="debounceSearch"
            />
          </v-col> </v-row
        ><v-row>
          <!-- Create Button -->
          <v-col cols="12" md="12">
            <v-btn
              prepend-icon="mdi-plus"
              color="success"
              @click="[(isEdit = false), (showTransactionDialog = true)]"
            >
              New
            </v-btn>

            <!-- Reset Button -->
            <v-btn
              prepend-icon="mdi-refresh"
              color="primary"
              @click="transactionStore.resetFilters"
            >
              Reset
            </v-btn>

            <!-- Populate from Drafts Button -->
            <v-btn
              prepend-icon="mdi-database-import"
              color="primary"
              @click="showPopulationDialog = true"
            >
              Populate From Drafts
            </v-btn>
          </v-col>
        </v-row>
      </template>

      <v-data-table-server
        v-model:items-per-page="transactionStore.itemsPerPage"
        :headers="headers"
        :items="transactionStore.transactions"
        :items-length="transactionStore.totalTransactions"
        loading-text="Loading... Please wait"
        :search="transactionStore.search"
        item-value="id"
        @update:options="transactionStore.updateOptions"
        mobile-breakpoint="sm"
        fixed-header
        :height="'50vh'"
      >
        <template v-slot:[`item.actions`]="{ item }">
          <v-btn
            icon
            @click="
              [
                (transactionStore.selectedTransaction = item),
                (isEdit = true),
                (showTransactionDialog = true),
              ]
            "
            v-if="userStore.user"
          >
            <v-icon>mdi-pencil</v-icon>
          </v-btn>

          <v-btn icon @click="deleteTransaction(item)" v-if="userStore.user">
            <v-icon>mdi-delete</v-icon>
          </v-btn>

          <v-btn icon @click="goToLogin" v-else>
            <v-icon>mdi-login</v-icon>
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>

  <!-- Creation / Modification Modal -->
  <v-dialog
    v-model="showTransactionDialog"
    :persistent="false"
    class="custom-dialog"
  >
    <transaction-form
      :isEdit="isEdit"
      :transaction="transactionStore.selectedTransaction"
      @close-modal="showTransactionDialog = false"
    />
  </v-dialog>

  <!-- Deletion Modal -->
  <v-dialog
    v-model="showDeletionDialog"
    :persistent="false"
    class="custom-dialog"
  >
    <delete-transaction-form
      :isEdit="false"
      @close-modal="showDeletionDialog = false"
    />
  </v-dialog>

  <!-- Population Modal -->
  <v-dialog
    v-model="showPopulationDialog"
    :persistent="false"
    class="custom-dialog"
  >
    <populate-from-drafts-form
      :isEdit="false"
      @close-modal="showPopulationDialog = false"
    />
  </v-dialog>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useTransactionStore } from "@/stores/transaction";
import { useUserStore } from "@/stores/user";
import _ from "lodash";
import DeleteTransactionForm from "./DeleteTransactionForm.vue";
import TransactionForm from "./TransactionForm.vue";
import PopulateFromDraftsForm from "./PopulateFromDraftsForm.vue";
import { useRouter } from "vue-router";

const transactionStore = useTransactionStore();
const userStore = useUserStore();
const router = useRouter();

onMounted(async () => {
  await transactionStore.fetchTypes();
  await transactionStore.fetchTags();
});

const years = generateYears();

function generateYears() {
  const currentYear = new Date().getFullYear();
  const range = 20; // Adjust the range of years as needed
  return Array.from({ length: range }, (_, i) => currentYear - 10 + i);
}

const showTransactionDialog = ref(false);
const showDeletionDialog = ref(false);
const showPopulationDialog = ref(false);

const headers = [
  {
    title: "Actions",
    align: "start",
    sortable: false,
    key: "actions",
  },
  {
    title: "Transaction",
    align: "start",
    sortable: true,
    key: "name",
  },
  {
    title: "Type",
    align: "start",
    sortable: true,
    key: "type",
  },
  {
    title: "Tag",
    align: "start",
    sortable: false,
    key: "tag",
  },
  {
    title: "Party",
    align: "start",
    sortable: false,
    key: "party.name",
  },
  {
    title: "User",
    align: "start",
    sortable: false,
    key: "user.name",
  },
  {
    title: "Recipient",
    align: "start",
    sortable: false,
    key: "recipient.name",
  },
  {
    title: "Amount",
    align: "start",
    sortable: true,
    key: "amount",
  },
  {
    title: "Date",
    align: "start",
    sortable: true,
    key: "date",
  },
];

const debounceSearch = _.debounce(() => {
  transactionStore.fetchTransactions();
}, 300);

const editTransaction = (transaction) => {
  transactionStore.selectedTransaction = transaction;
  dialog.value = true;
};

const deleteTransaction = (transaction) => {
  transactionStore.selectedTransaction = transaction;
  showDeletionDialog.value = true;
};

const goToLogin = () => {
  router.push({ name: "login-form" }); // Adjust the route name as necessary
};

// Computed properties for date constraints
const today = new Date();
today.setHours(0, 0, 0, 0);
</script>

<style>
.custom-dialog .v-overlay__content {
  pointer-events: none;
}

.custom-dialog .v-card {
  pointer-events: auto;
}
</style>
