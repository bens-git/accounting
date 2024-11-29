<template>
  <v-card>
    <v-card-title>{{
      isEdit ? "Edit Draft" : "Create New Draft"
    }}</v-card-title>
    <v-card-text>
      <!-- Form Inputs for Creating/Updating a Draft -->
      <v-select
        v-if="draftStore.types"
        density="compact"
        v-model="localDraft.type"
        :items="draftStore.types"
        label="Type"
        :error-messages="responseStore?.response?.errors[0]?.draft"
      />

      <v-text-field
        density="compact"
        v-model="localDraft.name"
        :error-messages="responseStore?.response?.errors[0]?.name"
        label="Name"
      />

      <v-row>
        <v-col cols="10">
          <v-autocomplete
            v-if="draftStore.parties"
            density="compact"
            clearable
            required
            v-model="localDraft.party_id"
            :items="draftStore.parties"
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
        v-model="localDraft.amount"
        label="Amount"
        draft="number"
        placeholder="Enter amount"
        prefix="$"
        required
        outlined
        clearable
        :error-messages="responseStore?.response?.errors[0]?.amount"
      ></v-text-field>

      <v-select
        v-if="draftStore.paymentMethods"
        density="compact"
        clearable
        v-model="localDraft.payment_method"
        :items="draftStore.paymentMethods"
        label="Payment Method"
        :error-messages="responseStore?.response?.errors[0]?.payment_method"
      />

      <v-text-field
        density="compact"
        v-model="localDraft.details"
        :error-messages="responseStore?.response?.errors[0]?.details"
        label="Details"
      />

      <v-select
        v-if="draftStore.tags"
        density="compact"
        clearable
        v-model="localDraft.tag"
        :items="draftStore.tags"
        label="Tag"
        :error-messages="responseStore?.response?.errors[0]?.tag"
      />

      <v-select
        v-if="draftStore.users"
        density="compact"
        v-model="localDraft.user_id"
        :items="draftStore.users"
        label="User"
        item-value="id"
        item-title="name"
        :error-messages="responseStore?.response?.errors[0]?.user_id"
      />

      <v-select
        v-if="draftStore.users && localDraft.type == 'PURCHASE'"
        density="compact"
        clearable
        v-model="localDraft.recipient_id"
        :items="draftStore.users"
        label="Recipient"
        item-value="id"
        item-title="name"
        :error-messages="responseStore?.response?.errors[0]?.recipient_id"
      />

      <v-select
        v-if="draftStore.recurrenceTypes"
        density="compact"
        clearable
        v-model="localDraft.recurrence_type"
        :items="draftStore.recurrenceTypes"
        label="Recurrence Type"
        :error-messages="responseStore?.response?.errors[0]?.recurrence_type"
      />

      <v-date-input
        label="Recurrence Start Date"
        v-if="localDraft.recurrence_type"
        density="compact"
        v-model="localDraft.recurrence_start_date"
      />
      <v-date-input
        label="Recurrence Dne Date"
        v-if="localDraft.recurrence_type"
        density="compact"
        v-model="localDraft.recurrence_end_date"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="saveDraft">{{
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
import { useDraftStore } from "@/stores/draft";
import { useUserStore } from "@/stores/user";
import PartyForm from "./PartyForm.vue";
import { useDate } from "vuetify";

const draftStore = useDraftStore();
const responseStore = useResponseStore();
const userStore = useUserStore();
const adapter = useDate();

const showPartyCreationDialog = ref(false);

// Define props
const props = defineProps({
  showDraftModal: Boolean,
  isEdit: Boolean,
  draft: Object,
});

const emit = defineEmits([
  "update-draft",
  "create-draft",
  "update:showDraftModal",
  "close-modal",
]);

const localDraft = ref({});

const handlePartyCreated = (partyId) => {
  localDraft.value.party_id = draftStore.selectedPartyId;
};

// Function to initialize localDraft
const initializeLocalDraft = () => {
  if (props.isEdit && props.draft) {
    localDraft.value = {
      ...props.draft,
    };
    // Ensure the `date` is a valid Date object
    if (localDraft.value.date) {
      localDraft.value.date = adapter.parseISO(localDraft.value.date);
    }
  } else {
    localDraft.value = {
      name: "",
      type: draftStore.selectedType,
      amount: null,
      date: useDate(),
      payment_method: null,
      details: null,
      tag: null,
      user_id: userStore.user.id,
    };
  }
};

// Initialize localDraft on component mount or when the draft changes
onMounted(() => {
  draftStore.fetchParties();
  draftStore.fetchPaymentMethods();
  draftStore.fetchTypes();
  draftStore.fetchRecurrenceTypes();
  draftStore.fetchTags();
  draftStore.fetchUsers();
  initializeLocalDraft();
});

watch(
  () => props.draft,
  () => {
    initializeLocalDraft();
  },
  { deep: true }
);

const saveDraft = async () => {
  const responseStore = useResponseStore();
  const formData = new FormData();

  console.log(localDraft.value);
  // Append all properties of local draft except images handling
  for (let [key, value] of Object.entries(localDraft.value)) {
    if (
      (key == "recurrence_start_date" || key == "recurrence_end_date") &&
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
      await draftStore.updateDraft(formData);
    } else {
      await draftStore.createDraft(formData);
    }
    if (responseStore.response.success) {
      closeModal();
      draftStore.fetchDrafts();
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
