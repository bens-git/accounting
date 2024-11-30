<template>
  <v-card
    v-if="transactionStore.selectedMonth && transactionStore.selectedYear"
  >
    <v-card-title>Populate Month From Drafts</v-card-title>
    <v-card-text>
      <v-alert type="info" elevation="2" prominent>
        <h3>
          Are you sure you want to populate
          {{ transactionStore.selectedMonth }},
          {{ transactionStore.selectedYear }} with the following drafts?
        </h3>
        <ul>
          <li v-for="draft of draftStore.drafts">
            {{ draft.type }} ({{ draft.recurrence_type }}): {{ draft.name }},
            {{ draft.amount }}
          </li>
        </ul>
      </v-alert>
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="populateMonth">Populate</v-btn>
      <v-btn text @click="closeModal">Cancel</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { onMounted } from "vue";
import { useResponseStore } from "@/stores/response";
import { useTransactionStore } from "@/stores/transaction";
import { useDraftStore } from "@/stores/draft";

const draftStore = useDraftStore();
const transactionStore = useTransactionStore();

// Define props
const props = defineProps({
  showPopulationModal: Boolean,
});

const emit = defineEmits(["close-modal"]);

onMounted(() => {
  draftStore.itemsPerPage = -1;
  draftStore.selectedType = null;
  draftStore.search = null;
  draftStore.selectedMonth = transactionStore.selectedMonth;
  draftStore.selectedYear = transactionStore.selectedYear;
  draftStore.fetchDrafts();
});

const populateMonth = async () => {
  const responseStore = useResponseStore();

  await draftStore.populateMonth();

  if (responseStore.response.success) {
    closeModal();
  } else {
    console.log("Error:", responseStore.response.message);
  }
};

// Close modal logic
const closeModal = () => {
  emit("close-modal");
};
</script>
