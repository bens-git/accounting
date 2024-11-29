<template>
  <v-card v-if="draftStore.selectedDraft">
    <v-card-title>Delete Draft</v-card-title>
    <v-card-text>
      <v-alert color="warning"
        >Are you sure you want to delete
        {{ draftStore.selectedDraft.name }}?</v-alert
      >
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="deleteDraft">Delete</v-btn>
      <v-btn text @click="closeModal">Cancel</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { ref, watch } from "vue";
import { useResponseStore } from "@/stores/response";
import { useDraftStore } from "@/stores/draft";

const draftStore = useDraftStore();
const responseStore = useResponseStore();

// Define props
const props = defineProps({
  showDeleteModal: Boolean,
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

const deleteDraft = async () => {
  const responseStore = useResponseStore();

  await draftStore.deleteDraft();

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
