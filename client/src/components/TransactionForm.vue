<template>
  <v-card>
    <v-card-title>{{
      isEdit ? "Edit Transaction" : "Create New Transaction"
    }}</v-card-title>
    <v-card-text>
      <!-- Form Inputs for Creating/Updating a Transaction -->
      <v-select
        v-if="transactionStore.types"
        density="compact"
        v-model="localTransaction.type"
        :items="transactionStore.types"
        label="Type"
        :error-messages="responseStore?.response?.errors[0]?.transaction"
      />

      <v-text-field
        density="compact"
        v-model="localTransaction.name"
        :error-messages="responseStore?.response?.errors[0]?.name"
        label="Name"
      />

      <v-row>
        <v-col cols="10">
          <v-autocomplete
            v-if="transactionStore.parties"
            density="compact"
            clearable
            required
            v-model="localTransaction.party_id"
            :items="transactionStore.parties"
            item-value="id"
            item-title="name"
            label="Party"
            :error-messages="
              responseStore?.response?.errors[0]?.party_id
            " /></v-col
        ><v-col cols="2">
          <v-btn color="primary" @click="showPartyCreationDialog = true"
            >New Party</v-btn
          ></v-col
        >
      </v-row>

      <v-text-field
        density="compact"
        v-model="localTransaction.amount"
        label="Amount"
        transaction="number"
        placeholder="Enter amount"
        prefix="$"
        required
        outlined
        clearable
        :error-messages="responseStore?.response?.errors[0]?.amount"
      ></v-text-field>

      <v-date-input
        label="Date"
        density="compact"
        v-model="localTransaction.date"
        format="MMMM D, YYYY"
      />

      <v-select
        v-if="transactionStore.paymentMethods"
        density="compact"
        clearable
        v-model="localTransaction.payment_method"
        :items="transactionStore.paymentMethods"
        label="Payment Method"
        :error-messages="responseStore?.response?.errors[0]?.payment_method"
      />

      <v-text-field
        density="compact"
        v-model="localTransaction.details"
        :error-messages="responseStore?.response?.errors[0]?.details"
        label="Details"
      />

      <v-select
        v-if="transactionStore.tags"
        density="compact"
        clearable
        v-model="localTransaction.tag"
        :items="transactionStore.tags"
        label="Tag"
        :error-messages="responseStore?.response?.errors[0]?.tag"
      />

      <v-select
        v-if="transactionStore.users"
        density="compact"
        v-model="localTransaction.user_id"
        :items="transactionStore.users"
        label="User"
        item-value="id"
        item-title="name"
        :error-messages="responseStore?.response?.errors[0]?.user_id"
      />

      <v-select
        v-if="transactionStore.users"
        density="compact"
        clearable
        v-model="localTransaction.recipient_id"
        :items="transactionStore.users"
        label="Recipient"
        item-value="id"
        item-title="name"
        :error-messages="responseStore?.response?.errors[0]?.recipient_id"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="saveTransaction">{{
        isEdit ? "Update" : "Create"
      }}</v-btn>
      <v-btn text @click="closeModal">Cancel</v-btn>
    </v-card-actions>
  </v-card>

  <!-- Party Creation Modal -->
  <v-dialog
    v-model="showPartyCreationDialog"
    :persistent="false"
    class="custom-dialog"
  >
    <party-form
      :isEdit="false"
      v-on:closeModal="showPartyCreationDialog = false"
      @party-created="handlePartyCreated"
    />
  </v-dialog>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useResponseStore } from "@/stores/response";
import { useTransactionStore } from "@/stores/transaction";
import { useUserStore } from "@/stores/user";
import PartyForm from "./PartyForm.vue";
import { useDate } from "vuetify";

const transactionStore = useTransactionStore();
const responseStore = useResponseStore();
const userStore = useUserStore();
const adapter = useDate();
const { date } = useDate();

const showPartyCreationDialog = ref(false);

// Define props
const props = defineProps({
  showTransactionModal: Boolean,
  isEdit: Boolean,
  transaction: Object,
});

const emit = defineEmits([
  "update-transaction",
  "create-transaction",
  "update:showTransactionModal",
  "close-modal",
]);

const localTransaction = ref({});

const handlePartyCreated = (partyId) => {
  localTransaction.value.party_id = transactionStore.selectedPartyId;
};

// Function to initialize localTransaction
const initializeLocalTransaction = () => {
  if (props.isEdit && props.transaction) {
    localTransaction.value = {
      ...props.transaction,
    };
    // Ensure the `date` is a valid Date object
    if (localTransaction.value.date) {
      localTransaction.value.date = adapter.parseISO(
        localTransaction.value.date
      );
    }
  } else {
    localTransaction.value = {
      name: "",
      type: transactionStore.selectedType,
      amount: null,
      date: ref(date(new Date(), "YYYY-MM-DD")),
      payment_method: null,
      details: null,
      tag: null,
      user_id: userStore.user.id,
    };
  }
};

// Initialize localTransaction on component mount or when the transaction changes
onMounted(() => {
  transactionStore.fetchParties();
  transactionStore.fetchPaymentMethods();
  transactionStore.fetchTypes();
  transactionStore.fetchRecurrenceTypes();
  transactionStore.fetchTags();
  transactionStore.fetchUsers();
  initializeLocalTransaction();
});

watch(
  () => props.transaction,
  () => {
    initializeLocalTransaction();
  },
  { deep: true }
);

const saveTransaction = async () => {
  const responseStore = useResponseStore();
  const formData = new FormData();

  // Append all properties of local transaction except images handling
  for (let [key, value] of Object.entries(localTransaction.value)) {
    if (
      (key == "date" ||
        key == "recurrence_start_date" ||
        key == "recurrence_end_date") &&
      value
    ) {
      value = value.toISOString().split("T")[0];
    }

    // Skip keys with null values or replace them with actual null
    if (value === null || value === undefined) {
      formData.append(key, "");
    } else {
      formData.append(key, value);
    }
  }

  try {
    if (props.isEdit) {
      await transactionStore.updateTransaction(formData);
    } else {
      await transactionStore.createTransaction(formData);
    }
    if (responseStore.response.success) {
      closeModal();
      transactionStore.fetchTransactions();
    } else {
      console.log("Error:", responseStore.response.message);
    }
  } catch (error) {
    console.error("Unexpected Error:", error);
    responseStore.setResponse(false, error.response.data.message, [
      error.response.data.errors,
    ]);
  }
};

// Close modal logic
const closeModal = () => {
  console.log("attempt-close");
  emit("close-modal");
};
</script>

<style scoped>
/* Add any styles specific to this component here */
</style>
