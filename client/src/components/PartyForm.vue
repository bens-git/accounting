<template>
  <v-card>
    <v-card-title>{{
      isEdit ? "Edit Party" : "Create New Party"
    }}</v-card-title>
    <v-card-text>
      <v-text-field
        v-model="localParty.name"
        :error-messages="responseStore?.response?.errors[0]?.party_name"
        label="Name"
      ></v-text-field>
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="saveParty">{{
        isEdit ? "Update" : "Create"
      }}</v-btn>
      <v-btn text @click="closeModal">Cancel</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { ref, watch } from "vue";
import { useResponseStore } from "@/stores/response";
import { useTransactionStore } from "@/stores/transaction";

const transactionStore = useTransactionStore();
const responseStore = useResponseStore();

// Define props
const props = defineProps({
  showPartyModal: Boolean,
  isEdit: Boolean,
  party: Object,
});

const emit = defineEmits([
  "update-party",
  "create-party",
  "update:showPartyModal",
  "close-modal",
  "party-created",
]);

watch(
  () => props.party,
  (newParty) => {
    localParty.value = { ...newParty };
  },
  { deep: true }
);

const localParty = ref({ ...props.party });

const saveParty = async () => {
  const responseStore = useResponseStore();
  const formData = new FormData();

  // Append all properties of local type except images handling
  for (const [key, value] of Object.entries(localParty.value)) {
    // Skip keys with null values or replace them with actual null
    if (value === null || value === undefined) {
      formData.append(key, "");
    } else {
      formData.append(key, value);
    }
  }

  try {
    if (props.isEdit) {
      await transactionStore.updateParty(formData);
    } else {
      await transactionStore.createParty(formData);
    }
    if (responseStore.response.success) {
      emit("party-created");

      closeModal();
      transactionStore.fetchParties();
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
  emit("close-modal");
};
</script>
